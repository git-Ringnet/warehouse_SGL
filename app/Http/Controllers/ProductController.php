<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\PDF;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $stockQuantity = $request->get('stock_quantity');

        $query = Product::where('status', 'active')
            ->where('is_hidden', false);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo số lượng tồn kho nếu có - áp dụng trước khi paginate
        if (!empty($stockQuantity) && $stockQuantity >= 0) {
            // Lấy tất cả products để tính inventory quantity
            $allProducts = $query->get();
            
            // Tính inventory quantity cho mỗi product
            foreach ($allProducts as $product) {
                $product->inventory_quantity = $product->getInventoryQuantity();
            }
            
            // Lọc theo stock quantity
            $filteredProductIds = $allProducts->filter(function ($product) use ($stockQuantity) {
                // Nếu stockQuantity = 0, chỉ lấy những cái có inventory chính xác bằng 0
                if ($stockQuantity === 0) {
                    return $product->inventory_quantity == 0;
                }
                // Nếu stockQuantity > 0, lấy những cái có inventory <= stockQuantity
                return $product->inventory_quantity <= $stockQuantity;
            })->pluck('id');
            

            
            // Query lại với IDs đã lọc
            $query = Product::where('status', 'active')
                ->where('is_hidden', false)
                ->whereIn('id', $filteredProductIds);
                
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }
        }

        $products = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Add inventory quantity to each product
        foreach ($products as $product) {
            $product->inventory_quantity = $product->getInventoryQuantity();
        }
        
        // Nếu đang lọc theo stock_quantity = 0, kiểm tra lại để đảm bảo không có product nào có inventory > 0
        if (!empty($stockQuantity) && $stockQuantity == 0) {
            $productsWithInventory = $products->filter(function($p) { return $p->inventory_quantity > 0; });
            if ($productsWithInventory->count() > 0) {
                // Log để debug
                Log::warning('Found products with inventory > 0 when filtering for stock_quantity = 0', [
                    'products' => $productsWithInventory->map(function($p) { 
                        return ['id' => $p->id, 'code' => $p->code, 'inventory' => $p->inventory_quantity]; 
                    })->toArray()
                ]);
                
                // Nếu có product có inventory > 0, loại bỏ chúng khỏi collection
                $products = $products->filter(function($p) { return $p->inventory_quantity == 0; });
            }
        }

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Get all active materials for the form
        $materials = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'unit']);

        return view('products.create', compact('materials'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Kiểm tra mã trùng lặp (cả sản phẩm active/hidden nhưng không xóa)
                    $exists = Product::where('code', $value)
                        ->where(function($query) {
                            $query->where('status', 'active');
                        })
                        ->exists();
                    
                    if ($exists) {
                        $fail('Mã thành phẩm đã tồn tại.');
                    }
                },
            ],
            'name' => 'required',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'inventory_warehouses' => 'nullable',
            'materials' => 'nullable|array',
            'materials.*.id' => 'nullable|exists:materials,id',
            'materials.*.quantity' => 'nullable|numeric|min:0.01',
            'materials.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle inventory_warehouses - default to 'all' if empty
            $inventoryWarehouses = $request->inventory_warehouses;
            if (empty($inventoryWarehouses)) {
                $inventoryWarehouses = ['all'];
            }

            // Create the product
            $product = Product::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'inventory_warehouses' => $inventoryWarehouses,
                'status' => 'active',
                'is_hidden' => false
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('products', 'public');

                    $product->images()->create([
                        'image_path' => $imagePath,
                        'alt_text' => $product->name
                    ]);
                }
            }

            // Handle materials relationship
            if ($request->has('materials') && is_array($request->materials)) {
                $materialsData = [];
                foreach ($request->materials as $material) {
                    if (!empty($material['id']) && !empty($material['quantity'])) {
                        $materialsData[$material['id']] = [
                            'quantity' => $material['quantity'],
                            'notes' => $material['notes'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }

                if (!empty($materialsData)) {
                    $product->materials()->attach($materialsData);
                }
            }

            DB::commit();

            // Ghi nhật ký tạo mới thành phẩm
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'products',
                    'Tạo mới thành phẩm: ' . $product->name,
                    null,
                    $product->toArray()
                );
            }

            return redirect()->route('products.index')
                ->with('success', 'Thành phẩm đã được thêm thành công.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating product: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo thành phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Get inventory quantity for this product
        $inventoryQuantity = $product->getInventoryQuantity();

        // Load product with its relationships (avoiding problematic materials.suppliers for now)
        $product->load(['materials.suppliers', 'images']);

        // Ghi nhật ký xem chi tiết thành phẩm
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'products',
                'Xem chi tiết thành phẩm: ' . $product->name,
                null,
                ['id' => $product->id, 'name' => $product->name, 'code' => $product->code]
            );
        }

        return view('products.show', compact('product', 'inventoryQuantity'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Get all active materials for the form
        $materials = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'unit']);

        // Load product with its relationships
        $product->load(['materials.suppliers', 'images']);

        return view('products.edit', compact('product', 'materials'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'code' => [
                'required',
                function ($attribute, $value, $fail) use ($product) {
                    // Kiểm tra mã trùng lặp (cả sản phẩm active/hidden nhưng không xóa)
                    $exists = Product::where('code', $value)
                        ->where('id', '!=', $product->id)
                        ->where(function($query) {
                            $query->where('status', 'active');
                        })
                        ->exists();
                    
                    if ($exists) {
                        $fail('Mã thành phẩm đã tồn tại.');
                    }
                },
            ],
            'name' => 'required',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'nullable|string',
            'inventory_warehouses' => 'nullable',
            'materials' => 'nullable|array',
            'materials.*.id' => 'nullable|exists:materials,id',
            'materials.*.quantity' => 'nullable|numeric|min:0.01',
            'materials.*.notes' => 'nullable|string',
        ]);

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $product->toArray();

        // Kiểm tra xem có đang cố gắng thay đổi vật tư không khi còn tồn kho
        if ($request->has('materials') && $product->hasInventory()) {
            // Kiểm tra xem danh sách vật tư mới có khác với danh sách cũ không
            $currentMaterials = $product->materials()->pluck('material_id')->toArray();
            $newMaterials = collect($request->materials)->pluck('id')->filter()->toArray();
            
            // Nếu số lượng vật tư khác nhau hoặc có vật tư mới khác vật tư cũ
            if (count($currentMaterials) != count($newMaterials) || 
                array_diff($currentMaterials, $newMaterials) || 
                array_diff($newMaterials, $currentMaterials)) {
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không thể chỉnh sửa công thức Vật tư sử dụng do thành phẩm vẫn còn tồn kho.');
            }
            
            // Kiểm tra số lượng và ghi chú của từng vật tư đã thay đổi không
            foreach ($request->materials as $material) {
                if (!empty($material['id'])) {
                    $existingMaterial = $product->materials()
                        ->where('material_id', $material['id'])
                        ->first();
                    
                    if ($existingMaterial && 
                        ($existingMaterial->pivot->quantity != $material['quantity'] || 
                         $existingMaterial->pivot->notes != ($material['notes'] ?? null))) {
                        
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Không thể chỉnh sửa công thức Vật tư sử dụng do thành phẩm vẫn còn tồn kho.');
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Handle inventory_warehouses - default to 'all' if empty
            $inventoryWarehouses = $request->inventory_warehouses;
            if (empty($inventoryWarehouses)) {
                $inventoryWarehouses = ['all'];
            }

            // Update basic product information
            $product->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'inventory_warehouses' => $inventoryWarehouses
            ]);

            // Handle deleted images
            if ($request->filled('deleted_images')) {
                $deletedImageIds = explode(',', $request->deleted_images);
                foreach ($deletedImageIds as $imageId) {
                    $image = $product->images()->where('id', $imageId)->first();
                    if ($image) {
                        // Delete file from storage
                        if (Storage::disk('public')->exists($image->image_path)) {
                            Storage::disk('public')->delete($image->image_path);
                        }
                        // Delete record from database
                        $image->delete();
                    }
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('products', 'public');

                    $product->images()->create([
                        'image_path' => $imagePath,
                        'alt_text' => $product->name
                    ]);
                }
            }

            // Handle materials relationship
            if ($request->has('materials')) {
                // First, detach all existing materials
                $product->materials()->detach();

                // Then attach new materials
                if (is_array($request->materials)) {
                    $materialsData = [];
                    foreach ($request->materials as $material) {
                        if (!empty($material['id']) && !empty($material['quantity'])) {
                            $materialsData[$material['id']] = [
                                'quantity' => $material['quantity'],
                                'notes' => $material['notes'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }

                    if (!empty($materialsData)) {
                        $product->materials()->attach($materialsData);
                    }
                }
            }

            DB::commit();

            // Ghi nhật ký cập nhật thành phẩm
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'products',
                    'Cập nhật thành phẩm: ' . $product->name,
                    $oldData,
                    $product->toArray()
                );
            }

            return redirect()->route('products.show', $product->id)
                ->with('success', 'Thành phẩm đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating product: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật thành phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Handle hide or delete product based on action.
     */
    public function destroy(Request $request, Product $product)
    {
        $action = $request->input('action', 'delete');

        // Lưu dữ liệu cũ trước khi ẩn/xóa
        $oldData = $product->toArray();
        $productName = $product->name;

        if ($action === 'hide') {
            // Hide the product
            $product->update([
                'is_hidden' => true
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Thành phẩm đã được ẩn thành công.');
        } else {
            // Mark as deleted
            $product->update([
                'status' => 'deleted'
            ]);

            // Ghi nhật ký xóa thành phẩm

            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'products',
                    'Xóa thành phẩm: ' . $productName,
                    $oldData,
                    null
                );
            }

            return redirect()->route('products.index')
                ->with('success', 'Thành phẩm đã được đánh dấu đã xóa.');
        }
    }

    /**
     * Display hidden products.
     */
    public function showHidden(Request $request)
    {
        $search = $request->get('search');

        $query = Product::where('status', 'active')
            ->where('is_hidden', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Add inventory quantity to each product
        foreach ($products as $product) {
            $product->inventory_quantity = $product->getInventoryQuantity();
        }

        return view('products.hidden', compact('products'));
    }

    /**
     * Display deleted products.
     */
    public function showDeleted(Request $request)
    {
        $search = $request->get('search');

        $query = Product::where('status', 'deleted');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Add inventory quantity to each product
        foreach ($products as $product) {
            $product->inventory_quantity = $product->getInventoryQuantity();
        }

        return view('products.deleted', compact('products'));
    }

    /**
     * Restore a hidden product.
     */
    public function restoreHidden(Product $product)
    {
        $product->update([
            'is_hidden' => false
        ]);

        return redirect()->route('products.hidden')
            ->with('success', 'Thành phẩm đã được khôi phục.');
    }

    /**
     * Restore a deleted product.
     */
    public function restoreDeleted(Product $product)
    {
        $product->update([
            'status' => 'active'
        ]);

        return redirect()->route('products.deleted')
            ->with('success', 'Thành phẩm đã được khôi phục.');
    }

    /**
     * Get components that are typically used for a product
     * This returns any materials that have been used in previous assemblies for this product
     */
    public function getComponents($id)
    {
        $product = Product::findOrFail($id);

        // Query for materials associated with this product from assembly_materials table
        // through the assemblies table
        $components = DB::table('materials')
            ->join('assembly_materials', 'materials.id', '=', 'assembly_materials.material_id')
            ->join('assemblies', 'assembly_materials.assembly_id', '=', 'assemblies.id')
            ->where('assemblies.product_id', $product->id)
            ->select('materials.*', 'assembly_materials.quantity as pivot_quantity')
            ->distinct()
            ->get();

        // If no components found, return empty array
        if ($components->isEmpty()) {
            return response()->json([]);
        }

        // Add stock quantity to each component
        foreach ($components as $component) {
            // Get total stock quantity across all warehouses
            $stockQuantity = DB::table('warehouse_materials')
                ->where('material_id', $component->id)
                ->where('item_type', 'material')
                ->sum('quantity');

            $component->stock_quantity = $stockQuantity;

            // Add pivot data for consistency with relationships
            $component->pivot = (object)[
                'quantity' => $component->pivot_quantity
            ];
            unset($component->pivot_quantity);
        }

        return response()->json($components);
    }

    /**
     * API endpoint to get inventory quantity for a product
     */
    public function getInventoryQuantity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->id);
        $inventoryQuantity = $product->getInventoryQuantity();

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'inventory_quantity' => $inventoryQuantity,
            'formatted_quantity' => number_format($inventoryQuantity, 0, ',', '.'),
        ]);
    }

    /**
     * API endpoint để lấy hình ảnh sản phẩm
     */
    public function getProductImages($id)
    {
        try {
            $product = Product::findOrFail($id);
            $images = $product->images;
            
            return response()->json([
                'success' => true,
                'images' => $images
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy hình ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products via API for AJAX requests
     */


    /**
     * Export products list to PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            // Start with base query for active products that are not hidden
            $query = Product::where('status', 'active')
                ->where('is_hidden', false);

            // Apply filters if provided
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            $products = $query->get();

            // Calculate quantities for each product
            foreach ($products as $product) {
                $product->inventory_quantity = $product->getInventoryQuantity();
            }

            // Apply stock filter after calculating inventory quantities
            if ($request->filled('stock_filter')) {
                $stockFilter = $request->stock_filter;
                $products = $products->filter(function ($product) use ($stockFilter) {
                    switch ($stockFilter) {
                        case 'in_stock':
                            return $product->inventory_quantity > 0;
                        case 'out_of_stock':
                            return $product->inventory_quantity == 0;
                        default:
                            return true;
                    }
                });
            }

            $pdf = FacadePdf::loadView('products.pdf', compact('products'));

            return $pdf->download('danh-sach-thanh-pham-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Export PDF error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export products list to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get current filters from request
            $filters = [
                'search' => $request->get('search'),
                'stock' => $request->get('stock_filter')
            ];

            return Excel::download(new ProductsExport($filters), 'danh-sach-thanh-pham-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Export Excel error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export products list to FDF
     */
    public function exportFDF(Request $request)
    {
        try {
            // Start with base query for active products that are not hidden
            $query = Product::where('status', 'active')
                ->where('is_hidden', false);

            // Apply filters if provided
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            $products = $query->get();

            // Calculate quantities for each product
            foreach ($products as $product) {
                $product->inventory_quantity = $product->getInventoryQuantity();
            }

            // Apply stock filter after calculating quantities
            if ($request->filled('stock_filter')) {
                if ($request->stock_filter === 'in_stock') {
                    $products = $products->filter(function ($product) {
                        return $product->inventory_quantity > 0;
                    });
                } elseif ($request->stock_filter === 'out_of_stock') {
                    $products = $products->filter(function ($product) {
                        return $product->inventory_quantity == 0;
                    });
                }
            }

            // Create FDF content
            $fdfContent = "%FDF-1.2\n";
            $fdfContent .= "1 0 obj\n";
            $fdfContent .= "<<\n";
            $fdfContent .= "/FDF\n";
            $fdfContent .= "<<\n";
            $fdfContent .= "/Fields [\n";

            foreach ($products as $index => $product) {
                $fdfContent .= "<<\n";
                $fdfContent .= "/T (product_" . ($index + 1) . "_code)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($product->code) . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (product_" . ($index + 1) . "_name)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($product->name) . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (product_" . ($index + 1) . "_description)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($product->description ?? '') . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (product_" . ($index + 1) . "_inventory_quantity)\n";
                $fdfContent .= "/V (" . number_format($product->inventory_quantity, 0, ',', '.') . ")\n";
                $fdfContent .= ">>\n";
            }

            $fdfContent .= "]\n";
            $fdfContent .= ">>\n";
            $fdfContent .= ">>\n";
            $fdfContent .= "endobj\n";
            $fdfContent .= "trailer\n";
            $fdfContent .= "<<\n";
            $fdfContent .= "/Root 1 0 R\n";
            $fdfContent .= ">>\n";
            $fdfContent .= "%%EOF\n";

            return response($fdfContent)
                ->header('Content-Type', 'application/vnd.fdf')
                ->header('Content-Disposition', 'attachment; filename="danh-sach-thanh-pham-' . date('Y-m-d') . '.fdf"');
        } catch (\Exception $e) {
            Log::error('Export FDF error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất FDF: ' . $e->getMessage());
        }
    }

    /**
     * Escape string for FDF format
     */
    private function escapeFDFString($string)
    {
        return str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $string);
    }

    /**
     * Download Excel template for products import
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(new ProductsTemplateExport, 'mau-nhap-thanh-pham-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Download template error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải mẫu: ' . $e->getMessage());
        }
    }

    /**
     * Import products from Excel file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // Max 10MB
            ]);

            // Clear any existing import results from session
            session()->forget('import_results');

            $import = new ProductsImport();
            Excel::import($import, $request->file('import_file'));

            $results = $import->getImportResults();

            // Log results for debugging
            Log::info('Import results:', $results);

            // Prepare success message
            $message = "Import hoàn tất! ";
            $message .= "Thành công: {$results['success_count']}, ";
            $message .= "Lỗi: {$results['error_count']}, ";
            $message .= "Trùng lặp: {$results['duplicate_count']}";

            // Store detailed results in session for the results page
            session(['import_results' => $results]);

            // Kiểm tra nếu có trùng lặp và có ít nhất 1 thành công
            if ($results['duplicate_count'] > 0 && $results['success_count'] > 0) {
                $duplicateMessage = "Chỉ tạo thành công {$results['success_count']} thành phẩm do {$results['duplicate_count']} thành phẩm còn lại có mã bị trùng lặp";
                return redirect()->route('products.import.results')->with('warning', $duplicateMessage);
            } elseif ($results['success_count'] > 0) {
                return redirect()->route('products.import.results')->with('success', $message);
            } else {
                return redirect()->route('products.import.results')->with('warning', $message);
            }
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    /**
     * Show import results
     */
    public function importResults()
    {
        $results = session('import_results');

        if (!$results) {
            return redirect()->route('products.index')->with('error', 'Không tìm thấy kết quả import.');
        }

        // Clear the session after retrieving the results to prevent reuse
        session()->forget('import_results');

        return view('products.import-results', compact('results'));
    }
}
