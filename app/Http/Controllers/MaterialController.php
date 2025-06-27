<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialImage;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaterialsExport;
use App\Exports\MaterialsTemplateExport;
use App\Imports\MaterialsImport;
use App\Models\Supplier;

class MaterialController extends Controller
{
    /**
     * Display a listing of the materials.
     */
    public function index(Request $request)
    {
        // Clear any remaining import results from session when visiting index
        if (session()->has('import_results')) {
            session()->forget('import_results');
        }

        // Start with base query for active materials that are not hidden
        $query = Material::where('status', 'active')
            ->where('is_hidden', false);

        // Apply filters if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        $materials = $query->get();
        
        // Initialize grand totals
        $grandTotalQuantity = 0;
        $grandInventoryQuantity = 0;
        
        // For each material, calculate total quantity and warehouse quantity
        foreach ($materials as $material) {
            // Total quantity across all locations (warehouses, projects, rentals, repairs, etc.)
            $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material')
                ->sum('quantity');
            
            // Total quantity only in warehouses based on inventory_warehouses setting
            $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material');
                
            // Check if inventory_warehouses is an array and contains specific warehouses
            if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
            }
            
            $material->inventory_quantity = $warehouseQuery->sum('quantity');
                
            // Add to grand totals
            $grandTotalQuantity += $material->total_quantity;
            $grandInventoryQuantity += $material->inventory_quantity;
        }
        
        // Apply stock filter after calculating quantities
        if ($request->filled('stock')) {
            if ($request->stock === 'in_stock') {
                $materials = $materials->filter(function ($material) {
                    return $material->inventory_quantity > 0;
                });
            } elseif ($request->stock === 'out_of_stock') {
                $materials = $materials->filter(function ($material) {
                    return $material->inventory_quantity == 0;
                });
            }
        }

        // Get unique categories and units for dropdowns
        $categories = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        $units = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->distinct()
            ->pluck('unit')
            ->filter()
            ->sort()
            ->values();

        return view('materials.index', compact('materials', 'grandTotalQuantity', 'grandInventoryQuantity', 'categories', 'units'));
    }

    /**
     * Show the form for creating a new material.
     */
    public function create()
    {
        // Fetch unique categories from the database
        $categories = Material::select('category')->distinct()->pluck('category')->toArray();
        
        // Sort categories alphabetically
        sort($categories);
        
        // Get all suppliers
        $suppliers = Supplier::orderBy('name')->get();

        return view('materials.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created material in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:materials,code',
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'inventory_warehouses' => 'nullable',

        ]);

        $materialData = $request->except(['images', 'image', 'supplier_ids']);

        // Handle inventory_warehouses - default to 'all' if empty
        if (empty($materialData['inventory_warehouses'])) {
            $materialData['inventory_warehouses'] = ['all'];
        }

        // Create the material
        $material = Material::create($materialData);

        // Handle suppliers relationship
        if ($request->has('supplier_ids') && !empty($request->supplier_ids)) {
            $material->suppliers()->sync($request->supplier_ids);
        }

        // Handle multiple image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('materials', 'public');
                
                MaterialImage::create([
                    'material_id' => $material->id,
                    'image_path' => $imagePath,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được thêm thành công.');
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material)
    {
        // Get all warehouses for the dropdown
        $warehouses = Warehouse::all();
        
        // Load material images and suppliers
        $material->load(['images', 'suppliers']);
        
        // Calculate total quantity across all locations
        $totalQuantity = WarehouseMaterial::where('material_id', $material->id)
            ->where('item_type', 'material')
            ->sum('quantity');
        
        // Calculate total inventory based on configuration
        $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
            ->where('item_type', 'material');
            
        if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
            $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
        }
        
        $inventoryQuantity = $warehouseQuery->sum('quantity');
        
        return view('materials.show', compact('material', 'warehouses', 'totalQuantity', 'inventoryQuantity'));
    }

    /**
     * Show the form for editing the specified material.
     */
    public function edit(Material $material)
    {
        // Fetch unique categories from the database
        $categories = Material::select('category')->distinct()->pluck('category')->toArray();
        
        // Sort categories alphabetically
        sort($categories);

        // Get all suppliers
        $suppliers = Supplier::orderBy('name')->get();
        
        // Load material images and suppliers
        $material->load(['images', 'suppliers']);
        
        return view('materials.edit', compact('material', 'categories', 'suppliers'));
    }

    /**
     * Update the specified material in storage.
     */
    public function update(Request $request, Material $material)
    {
        $request->validate([
            'code' => 'required|unique:materials,code,' . $material->id,
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'inventory_warehouses' => 'nullable',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id'
        ]);

        $materialData = $request->except(['images', 'image', 'deleted_images', 'supplier_ids']);

        // Handle inventory_warehouses - default to 'all' if empty
        if (empty($materialData['inventory_warehouses'])) {
            $materialData['inventory_warehouses'] = ['all'];
        }

        // Update the material
        $material->update($materialData);

        // Handle suppliers relationship
        if ($request->has('supplier_ids')) {
            $material->suppliers()->sync($request->supplier_ids ?? []);
        }

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImages = explode(',', $request->input('deleted_images'));
            
            foreach ($deletedImages as $imageId) {
                if (!empty($imageId)) {
                    $image = MaterialImage::find($imageId);
                    if ($image) {
                        // Delete the file from storage
                        Storage::disk('public')->delete($image->image_path);
                        // Delete the record
                        $image->delete();
                    }
                }
            }
        }

        // Handle multiple image uploads if present
        if ($request->hasFile('images')) {
            $lastOrder = $material->images()->max('sort_order') ?? -1;
            
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('materials', 'public');
                
                MaterialImage::create([
                    'material_id' => $material->id,
                    'image_path' => $imagePath,
                    'sort_order' => $lastOrder + $index + 1
                ]);
            }
        }

        return redirect()->route('materials.show', $material->id)
            ->with('success', 'Vật tư đã được cập nhật thành công.');
    }

    /**
     * Remove the specified material from storage.
     */
    public function destroy(Request $request, Material $material)
    {
        // Check if material has inventory quantity > 0
        $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
            ->where('item_type', 'material');

        // Check inventory based on material's configuration
        if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
            $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
        }

        $inventoryQuantity = $warehouseQuery->sum('quantity');

        // Only allow deletion when inventory quantity is 0
        if ($inventoryQuantity > 0) {
            return redirect()->route('materials.index')
                ->with('error', 'Không thể xóa vật tư khi còn tồn kho. Số lượng tồn kho hiện tại: ' . number_format($inventoryQuantity, 0, ',', '.'));
        }

        // Check the deletion action type from request
        $action = $request->input('action');

        if ($action === 'hide') {
            // Hide the material instead of deleting
            $material->update([
                'is_hidden' => true,
                'status' => 'active'
            ]);

            return redirect()->route('materials.index')
                ->with('success', 'Vật tư đã được ẩn thành công.');
        } else {
            // Mark as deleted but don't actually delete for history purposes
            $material->update([
                'status' => 'deleted',
                'is_hidden' => false
            ]);

        return redirect()->route('materials.index')
                ->with('success', 'Vật tư đã được đánh dấu là đã xóa.');
        }
    }

    /**
     * Delete a specific material image
     */
    public function deleteImage($id)
    {
        try {
            $image = MaterialImage::findOrFail($id);
            $materialId = $image->material_id;
            
            // Delete the file from storage
            Storage::disk('public')->delete($image->image_path);
            
            // Delete the record
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ảnh thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting material image: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search materials API endpoint
     */
    public function searchMaterials(Request $request)
    {
        try {
            $searchTerm = $request->input('term');
            
            if (empty($searchTerm)) {
                return response()->json([]);
            }
            
            // Make search case-insensitive and more comprehensive
            $materials = Material::where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(category) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(unit) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(notes) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->limit(10)
                ->get(['id', 'code', 'name', 'category', 'unit']);
            
            return response()->json($materials);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Material search error: ' . $e->getMessage());
            
            // Return error with more details for debugging
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get inventory quantity for a specific material across all or specific warehouse
     */
    public function getInventoryQuantity(Request $request)
    {
        try {
            $materialId = $request->input('material_id');
            $warehouseId = $request->input('warehouse_id');
            
            if (!$materialId) {
                return response()->json(['error' => 'Material ID is required'], 400);
            }
            
            $material = Material::find($materialId);
            if (!$material) {
                return response()->json(['error' => 'Material not found'], 404);
            }
            
            $query = WarehouseMaterial::where('material_id', $materialId)
                                      ->where('item_type', 'material');
            
            // If warehouse ID is provided and not 'all', filter by warehouse
            if ($warehouseId && $warehouseId !== 'all') {
                $query->where('warehouse_id', $warehouseId);
            } 
            // If no warehouse ID is provided, use the material's configured inventory_warehouses setting
            else if (!$warehouseId && is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $query->whereIn('warehouse_id', $material->inventory_warehouses);
            }
            
            $totalQuantity = $query->sum('quantity');
            
            return response()->json([
                'quantity' => $totalQuantity
            ]);
        } catch (\Exception $e) {
            Log::error('Get inventory quantity error: ' . $e->getMessage());
            
            return response()->json([
                'error' => true, 
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get material images API endpoint
     */
    public function getMaterialImages($id)
    {
        try {
            $material = Material::findOrFail($id);
            $images = $material->images()->orderBy('sort_order')->get();
            
            $formattedImages = $images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_path),
                    'sort_order' => $image->sort_order
                ];
            });
            
            return response()->json([
                'material_id' => $material->id,
                'material_name' => $material->name,
                'images' => $formattedImages
            ]);
        } catch (\Exception $e) {
            Log::error('Get material images error: ' . $e->getMessage());
            
            return response()->json([
                'error' => true, 
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export materials list to PDF
     */
    public function exportPDF()
    {
        try {
            $materials = Material::all();
            
            // Calculate quantities for each material
            foreach ($materials as $material) {
                // Total quantity across all locations
                $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material')
                    ->sum('quantity');
                
                // Total inventory based on configuration
                $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material');
                    
                if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                    $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
                }
                
                $material->inventory_quantity = $warehouseQuery->sum('quantity');
            }
            
            $pdf = PDF::loadView('materials.pdf', compact('materials'));
            
            return $pdf->download('danh-sach-vat-tu-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Export PDF error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Export materials list to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get current filters from request
            $filters = [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
                'unit' => $request->get('unit'),
                'stock' => $request->get('stock')
            ];

            return Excel::download(new MaterialsExport($filters), 'danh-sach-vat-tu-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Export Excel error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export materials list to FDF
     */
    public function exportFDF(Request $request)
    {
        try {
            // Start with base query for active materials that are not hidden
            $query = Material::where('status', 'active')
                ->where('is_hidden', false);

            // Apply filters if provided
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('unit')) {
                $query->where('unit', $request->unit);
            }

            $materials = $query->get();

            // Calculate quantities for each material
            foreach ($materials as $material) {
                $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material')
                    ->sum('quantity');

                $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material');

                if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                    $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
                }

                $material->inventory_quantity = $warehouseQuery->sum('quantity');
            }

            // Apply stock filter after calculating quantities
            if ($request->filled('stock')) {
                if ($request->stock === 'in_stock') {
                    $materials = $materials->filter(function ($material) {
                        return $material->inventory_quantity > 0;
                    });
                } elseif ($request->stock === 'out_of_stock') {
                    $materials = $materials->filter(function ($material) {
                        return $material->inventory_quantity == 0;
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

            foreach ($materials as $index => $material) {
                $fdfContent .= "<<\n";
                $fdfContent .= "/T (material_" . ($index + 1) . "_code)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($material->code) . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (material_" . ($index + 1) . "_name)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($material->name) . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (material_" . ($index + 1) . "_category)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($material->category) . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (material_" . ($index + 1) . "_unit)\n";
                $fdfContent .= "/V (" . $this->escapeFDFString($material->unit) . ")\n";
                $fdfContent .= ">>\n";

                $fdfContent .= "<<\n";
                $fdfContent .= "/T (material_" . ($index + 1) . "_inventory_quantity)\n";
                $fdfContent .= "/V (" . number_format($material->inventory_quantity, 0, ',', '.') . ")\n";
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
                ->header('Content-Disposition', 'attachment; filename="danh-sach-vat-tu-' . date('Y-m-d') . '.fdf"');
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
     * Download Excel template for materials import
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(new MaterialsTemplateExport, 'mau-nhap-vat-tu-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Download template error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải mẫu: ' . $e->getMessage());
        }
    }

    /**
     * Import materials from Excel file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // Max 10MB
            ]);

            // Clear any existing import results from session
            session()->forget('import_results');

            $import = new MaterialsImport();
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

            if ($results['success_count'] > 0) {
                return redirect()->route('materials.import.results')->with('success', $message);
            } else {
                return redirect()->route('materials.import.results')->with('warning', $message);
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
            return redirect()->route('materials.index')->with('error', 'Không tìm thấy kết quả import.');
        }

        // Clear the session after retrieving the results to prevent reuse
        session()->forget('import_results');

        return view('materials.import-results', compact('results'));
    }

    /**
     * Show hidden materials
     */
    public function showHidden()
    {
        $materials = Material::where('is_hidden', true)
            ->get();

        // Calculate quantities for each material
        foreach ($materials as $material) {
            // Total quantity across all locations
            $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material')
                ->sum('quantity');

            // Total quantity only in warehouses based on inventory_warehouses setting
            $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material');

            // Check if inventory_warehouses is an array and contains specific warehouses
            if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
            }

            $material->inventory_quantity = $warehouseQuery->sum('quantity');
        }

        return view('materials.hidden', compact('materials'));
    }

    /**
     * Show deleted materials
     */
    public function showDeleted()
    {
        $materials = Material::where('status', 'deleted')
            ->get();

        // Calculate quantities for each material
        foreach ($materials as $material) {
            // Total quantity across all locations
            $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material')
                ->sum('quantity');

            // Total quantity only in warehouses based on inventory_warehouses setting
            $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material');

            // Check if inventory_warehouses is an array and contains specific warehouses
            if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
            }

            $material->inventory_quantity = $warehouseQuery->sum('quantity');
        }

        return view('materials.deleted', compact('materials'));
    }

    /**
     * Restore a hidden material
     */
    public function restore($id)
    {
        $material = Material::findOrFail($id);
        $material->update([
            'is_hidden' => false,
            'status' => 'active'
        ]);

        return back()->with('success', 'Vật tư đã được khôi phục thành công.');
    }

    /**
     * Search materials via API for AJAX requests
     */
    public function searchMaterialsApi(Request $request)
    {
        // Start with base query for active materials that are not hidden
        $query = Material::where('status', 'active')
            ->where('is_hidden', false);

        // Apply filters if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        $materials = $query->get();

        // For each material, calculate quantities
        foreach ($materials as $material) {
            // Total quantity across all locations
            $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material')
                ->sum('quantity');

            // Total quantity only in warehouses based on inventory_warehouses setting
            $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material');

            if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
            }

            $material->inventory_quantity = $warehouseQuery->sum('quantity');
        }

        // Apply stock filter after calculating quantities
        if ($request->filled('stock')) {
            if ($request->stock === 'in_stock') {
                $materials = $materials->filter(function ($material) {
                    return $material->inventory_quantity > 0;
                });
            } elseif ($request->stock === 'out_of_stock') {
                $materials = $materials->filter(function ($material) {
                    return $material->inventory_quantity == 0;
                });
            }
        }

        return response()->json([
            'materials' => $materials->values(),
            'count' => $materials->count()
        ]);
    }

    /**
     * API: Lấy lịch sử xuất nhập vật tư (dùng cho modal)
     */
    public function historyAjax(Request $request, $id)
    {
        // Lấy bộ lọc từ request
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Query lịch sử nhập kho
        $importsQuery = \App\Models\InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->leftJoin('warehouses', 'inventory_import_materials.warehouse_id', '=', 'warehouses.id')
            ->where('inventory_import_materials.material_id', $id)
            ->select([
                'inventory_imports.import_date as date',
                'inventory_import_materials.quantity',
                'inventory_import_materials.warehouse_id',
                'warehouses.name as warehouse_name',
                DB::raw("'Hệ thống' as user_name"),
                DB::raw("'Nhập' as type"),
                DB::raw("CASE WHEN inventory_import_materials.quantity > 0 THEN CONCAT('+', inventory_import_materials.quantity) ELSE inventory_import_materials.quantity END as formatted_quantity")
            ]);

        // Áp dụng bộ lọc thời gian cho nhập kho
        if ($fromDate) {
            $importsQuery->where('inventory_imports.import_date', '>=', $fromDate);
        }
        if ($toDate) {
            $importsQuery->where('inventory_imports.import_date', '<=', $toDate);
        }

        $imports = $importsQuery->get();

        // Query lịch sử xuất kho
        $exportsQuery = \App\Models\DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->leftJoin('warehouses', 'dispatch_items.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('users', 'dispatches.created_by', '=', 'users.id')
            ->where('dispatch_items.item_id', $id)
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->select([
                'dispatches.dispatch_date as date',
                'dispatch_items.quantity',
                'dispatch_items.warehouse_id',
                'warehouses.name as warehouse_name',
                'users.name as user_name',
                DB::raw("'Xuất' as type"),
                DB::raw("CASE WHEN dispatch_items.quantity > 0 THEN CONCAT('-', dispatch_items.quantity) ELSE dispatch_items.quantity END as formatted_quantity")
            ]);

        // Áp dụng bộ lọc thời gian cho xuất kho
        if ($fromDate) {
            $exportsQuery->where('dispatches.dispatch_date', '>=', $fromDate);
        }
        if ($toDate) {
            $exportsQuery->where('dispatches.dispatch_date', '<=', $toDate);
        }

        $exports = $exportsQuery->get();

        // Gộp và sắp xếp theo ngày (mới nhất trước)
        $history = $imports->concat($exports)->sortByDesc('date')->values();

        // Format lại dữ liệu
        $formattedHistory = $history->map(function ($item) {
            return [
                'date' => \Carbon\Carbon::parse($item->date)->format('H:i:s d/m/Y'),
                'type' => $item->type,
                'quantity' => $item->formatted_quantity,
                'warehouse_name' => $item->warehouse_name ?: 'N/A',
                'user_name' => $item->user_name ?: 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedHistory,
            'total' => $formattedHistory->count()
        ]);
    }
}
