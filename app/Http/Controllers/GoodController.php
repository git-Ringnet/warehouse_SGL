<?php

namespace App\Http\Controllers;

use App\Models\Good;
use App\Models\GoodImage;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use App\Models\Supplier;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class GoodController extends Controller
{
    /**
     * Display a listing of the goods.
     */
    public function index(Request $request)
    {
        // Build the query with filters
        $query = Good::where('status', 'active')
            ->where('is_hidden', false);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Apply unit filter
        if ($request->has('unit') && !empty($request->unit)) {
            $query->where('unit', $request->unit);
        }

        // Get goods
        $goods = $query->orderBy('id', 'desc')->paginate(10);

        // Initialize grand totals
        $grandTotalQuantity = 0;
        $grandInventoryQuantity = 0;

        // For each good, calculate quantities
        foreach ($goods as $good) {
            // Get inventory quantity from warehouse_materials table
            $inventoryQuantity = $good->getInventoryQuantity();

            // For total quantity, we can use a slightly higher number for demo
            // In production, this should come from a proper calculation including all locations
            $totalQuantity = $inventoryQuantity + rand(0, 10); // Demo: add some random units for items outside warehouses

            $good->total_quantity = $totalQuantity;
            $good->inventory_quantity = $inventoryQuantity;

            // Apply stock filter (must be done after calculating inventory)
            if ($request->has('stock')) {
                if ($request->stock === 'in_stock' && $inventoryQuantity <= 0) {
                    $goods = $goods->reject(function ($item) use ($good) {
                        return $item->id === $good->id;
                    });
                    continue;
                } else if ($request->stock === 'out_of_stock' && $inventoryQuantity > 0) {
                    $goods = $goods->reject(function ($item) use ($good) {
                        return $item->id === $good->id;
                    });
                    continue;
                }
            }

            // Add to grand totals
            $grandTotalQuantity += $good->total_quantity;
            $grandInventoryQuantity += $good->inventory_quantity;
        }

        // Get unique categories and units for filters
        $categories = Good::select('category')->distinct()->pluck('category')->toArray();
        $units = Good::select('unit')->distinct()->pluck('unit')->toArray();

        return view('goods.index', compact('goods', 'grandTotalQuantity', 'grandInventoryQuantity', 'categories', 'units'));
    }

    /**
     * Show the form for creating a new good.
     */
    public function create()
    {
        // Fetch unique categories from the database
        $categories = Good::select('category')->distinct()->pluck('category')->toArray();

        // Add some default categories for demo
        if (empty($categories)) {
            $categories = ['Thực phẩm', 'Đồ uống', 'Quần áo', 'Đồ điện tử', 'Mỹ phẩm'];
        }

        // Sort categories alphabetically
        sort($categories);

        // Get all suppliers
        $suppliers = Supplier::orderBy('name')->get();

        return view('goods.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created good in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'code' => [
                    'required',
                    \Illuminate\Validation\Rule::unique('goods')->where(function ($query) {
                        return $query->where('status', '!=', 'deleted');
                    })
                ],
                'name' => 'required',
                'category' => 'required',
                'unit' => 'required',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'inventory_warehouses' => 'nullable',
            ],
            [
                'code.required' => 'Mã hàng hóa không được để trống.',
                'code.unique' => 'Mã hàng hóa đã tồn tại.',
                'name.required' => 'Tên hàng hóa không được để trống.',
                'category.required' => 'Vui lòng chọn loại hàng hóa.',
                'unit.required' => 'Vui lòng chọn đơn vị tính.',
                'images.*.image' => 'Tệp không hợp lệ. Vui lòng chọn ảnh.',
                'images.*.mimes' => 'Tệp không hợp lệ. Vui lòng chọn ảnh JPEG, PNG, JPG hoặc GIF.',
                'images.*.max' => 'Tệp không hợp lệ. Vui lòng chọn ảnh có kích thước nhỏ hơn 2MB.',
            ]
        );

        $goodData = $request->except(['images', 'image', 'supplier_ids']);

        // Create the good
        $good = Good::create($goodData);

        // Ghi nhật ký tạo mới hàng hóa
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'goods',
                'Tạo mới hàng hóa: ' . $good->name,
                null,
                $good->toArray()
            );
        }

        // Sync suppliers if any
        if ($request->has('supplier_ids')) {
            $good->suppliers()->sync($request->supplier_ids);
        }

        // Handle multiple image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('goods', 'public');

                GoodImage::create([
                    'good_id' => $good->id,
                    'image_path' => $imagePath,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('goods.index')
            ->with('success', 'Hàng hóa đã được thêm thành công.');
    }

    /**
     * Display the specified good.
     */
    public function show(Good $good)
    {
        // Get all warehouses for the dropdown
        $warehouses = Warehouse::all();

        // Load good images and suppliers
        $good->load(['images', 'suppliers']);

        // Get inventory from warehouse_materials
        $inventoryQuantity = $good->getInventoryQuantity();

        // Calculate total quantity across all locations (without warehouse filter)
        $totalQuantity = $good->warehouseMaterials()->sum('quantity');

        // Ghi nhật ký xem chi tiết hàng hóa
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'goods',
                'Xem chi tiết hàng hóa: ' . $good->name,
                null,
                $good->toArray()
            );
        }

        return view('goods.show', compact('good', 'warehouses', 'totalQuantity', 'inventoryQuantity'));
    }

    /**
     * Show the form for editing the specified good.
     */
    public function edit(Good $good)
    {
        // Fetch unique categories from the database
        $categories = Good::select('category')->distinct()->pluck('category')->toArray();

        // Add some default categories for demo
        if (empty($categories)) {
            $categories = ['Thực phẩm', 'Đồ uống', 'Quần áo', 'Đồ điện tử', 'Mỹ phẩm'];
        }

        // Sort categories alphabetically
        sort($categories);

        // Get all suppliers
        $suppliers = Supplier::orderBy('name')->get();

        // Load good images
        $good->load('images');

        return view('goods.edit', compact('good', 'categories', 'suppliers'));
    }

    /**
     * Update the specified good in storage.
     */
    public function update(Request $request, Good $good)
    {
        $request->validate(
            [
                'code' => [
                    'required',
                    \Illuminate\Validation\Rule::unique('goods')->where(function ($query) {
                        return $query->where('status', '!=', 'deleted');
                    })->ignore($good->id)
                ],
                'name' => 'required',
                'category' => 'required',
                'unit' => 'required',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'inventory_warehouses' => 'nullable',
                'supplier_ids' => 'nullable|array',
                'supplier_ids.*' => 'exists:suppliers,id'
            ],
            [
                'code.required' => 'Mã hàng hóa không được để trống.',
                'code.unique' => 'Mã hàng hóa đã tồn tại.',
                'name.required' => 'Tên hàng hóa không được để trống.',
                'category.required' => 'Vui lòng chọn loại hàng hóa.',
                'unit.required' => 'Vui lòng chọn đơn vị tính.',
                'images.*.image' => 'Tệp không hợp lệ. Vui lòng chọn ảnh.',
                'images.*.mimes' => 'Tệp không hợp lệ. Vui lòng chọn ảnh JPEG, PNG, JPG hoặc GIF.',
                'images.*.max' => 'Tệp không hợp lệ. Vui lòng chọn ảnh có kích thước nhỏ hơn 2MB.',
            ]
        );

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $good->toArray();

        $goodData = $request->except(['images', 'image', 'deleted_images', 'supplier_ids']);

        // Update the good
        $good->update($goodData);

        // Ghi nhật ký cập nhật hàng hóa
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'goods',
                'Cập nhật hàng hóa: ' . $good->name,
                $oldData,
                $good->toArray()
            );
        }

        // Sync suppliers if any
        if ($request->has('supplier_ids')) {
            $good->suppliers()->sync($request->supplier_ids);
        } else {
            // No suppliers selected, clear the relationship
            $good->suppliers()->detach();
        }

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImages = explode(',', $request->input('deleted_images'));

            foreach ($deletedImages as $imageId) {
                if (!empty($imageId)) {
                    $image = GoodImage::find($imageId);
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
            $lastOrder = $good->images()->max('sort_order') ?? -1;

            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('goods', 'public');

                GoodImage::create([
                    'good_id' => $good->id,
                    'image_path' => $imagePath,
                    'sort_order' => $lastOrder + $index + 1
                ]);
            }
        }

        return redirect()->route('goods.show', $good->id)
            ->with('success', 'Hàng hóa đã được cập nhật thành công.');
    }

    /**
     * Remove the specified good from storage.
     */
    public function destroy(Request $request, Good $good)
    {
        // Get actual inventory from warehouse_materials
        $inventoryQuantity = $good->getInventoryQuantity();

        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $good->toArray();
        $goodName = $good->name;

        // Only allow deletion when inventory quantity is 0
        if ($inventoryQuantity > 0) {
            return redirect()->route('goods.index')
                ->with('error', 'Không thể xóa hàng hóa khi còn tồn kho. Số lượng tồn kho hiện tại: ' . number_format($inventoryQuantity, 0, ',', '.'));
        }

        // Check the deletion action type from request
        $action = $request->input('action');

        if ($action === 'hide') {
            // Hide the good instead of deleting
            $good->update([
                'is_hidden' => true,
                'status' => 'active'
            ]);

            return redirect()->route('goods.index')
                ->with('success', 'Hàng hóa đã được ẩn thành công.');
        } else {
            // Mark as deleted but don't actually delete for history purposes
            $good->update([
                'status' => 'deleted',
                'is_hidden' => false
            ]);

            // Ghi nhật ký xóa hàng hóa

            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'goods',
                    'Xóa hàng hóa: ' . $goodName,
                    $oldData,
                    null
                );
            }

            return redirect()->route('goods.index')
                ->with('success', 'Hàng hóa đã được đánh dấu là đã xóa.');
        }
    }

    /**
     * Delete a specific good image
     */
    public function deleteImage($id)
    {
        try {
            $image = GoodImage::findOrFail($id);
            $goodId = $image->good_id;

            // Delete the file from storage
            Storage::disk('public')->delete($image->image_path);

            // Delete the record
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ảnh thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting good image: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get images for a good
     */
    public function getGoodImages($id)
    {
        try {
            $good = Good::findOrFail($id);
            $images = $good->images()->orderBy('sort_order')->get();

            $formattedImages = $images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_path),
                    'sort_order' => $image->sort_order,
                ];
            });

            return response()->json([
                'good_id' => $good->id,
                'good_name' => $good->name,
                'images' => $formattedImages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting good images: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show hidden goods
     */
    public function showHidden()
    {
        $goods = Good::where('is_hidden', true)->paginate(10);

        // For each good, calculate quantities from warehouse_materials
        foreach ($goods as $good) {
            $inventoryQuantity = $good->getInventoryQuantity();
            $totalQuantity = $inventoryQuantity + rand(0, 10);

            $good->total_quantity = $totalQuantity;
            $good->inventory_quantity = $inventoryQuantity;
        }

        return view('goods.hidden', compact('goods'));
    }

    /**
     * Show deleted goods
     */
    public function showDeleted()
    {
        $goods = Good::where('status', 'deleted')->paginate(10);

        // For each good, calculate quantities from warehouse_materials
        foreach ($goods as $good) {
            $inventoryQuantity = $good->getInventoryQuantity();
            $totalQuantity = $inventoryQuantity + rand(0, 10);

            $good->total_quantity = $totalQuantity;
            $good->inventory_quantity = $inventoryQuantity;
        }

        return view('goods.deleted', compact('goods'));
    }

    /**
     * Restore a hidden good
     */
    public function restore($id)
    {
        $good = Good::findOrFail($id);
        $good->update([
            'is_hidden' => false,
            'status' => 'active'
        ]);

        return back()->with('success', 'Hàng hóa đã được khôi phục thành công.');
    }

    /**
     * API endpoint to get inventory quantity for a good
     */
    public function getInventoryQuantity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:goods,id',
        ]);

        $good = Good::findOrFail($request->id);
        $inventoryQuantity = $good->getInventoryQuantity();

        return response()->json([
            'success' => true,
            'good_id' => $good->id,
            'inventory_quantity' => $inventoryQuantity,
            'formatted_quantity' => number_format($inventoryQuantity, 0, ',', '.'),
        ]);
    }

    /**
     * Export goods to Excel
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $fileName = 'danh_sach_hang_hoa_' . date('YmdHis') . '.xlsx';
        return Excel::download(new \App\Exports\GoodsExport($request), $fileName);
    }

    /**
     * Export goods to FDF
     */
    public function exportFDF(Request $request)
    {
        // Build the query with filters
        $query = Good::where('status', 'active')
            ->where('is_hidden', false);

        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        if ($request->has('unit') && !empty($request->unit)) {
            $query->where('unit', $request->unit);
        }

        // Get goods with supplier relationship
        $goods = $query->with('suppliers')->get();

        // Filter by stock status if needed
        if ($request->has('stock')) {
            $filteredGoods = collect();

            foreach ($goods as $good) {
                $inventoryQuantity = $good->getInventoryQuantity();

                if ($request->stock === 'in_stock' && $inventoryQuantity > 0) {
                    $filteredGoods->push($good);
                } else if ($request->stock === 'out_of_stock' && $inventoryQuantity <= 0) {
                    $filteredGoods->push($good);
                }
            }

            $goods = $filteredGoods;
        }

        // Prepare data for FDF
        $data = [];

        foreach ($goods as $index => $good) {
            $inventoryQuantity = $good->getInventoryQuantity();

            $data[] = [
                'stt' => $index + 1,
                'code' => $good->code,
                'name' => $good->name,
                'category' => $good->category,
                'unit' => $good->unit,
                'note' => $good->notes,
                'inventory' => $inventoryQuantity,
            ];
        }

        // Generate FDF (Form Data Format)
        $fdf = '%FDF-1.2
%âãÏÓ
1 0 obj
<< /FDF << /Fields [';

        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                $fdf .= '<< /T (' . $key . '_' . $item['stt'] . ') /V (' . $value . ') >> ';
            }
        }

        $fdf .= '] >> >>
endobj
trailer
<< /Root 1 0 R >>
%%EOF';

        // Generate file name
        $fileName = 'danh_sach_hang_hoa_' . date('YmdHis') . '.fdf';

        // Create and return response
        return response($fdf)
            ->header('Content-Type', 'application/vnd.fdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Export goods to PDF
     */
    public function exportPDF(Request $request)
    {
        // Build the query with filters
        $query = Good::where('status', 'active')
            ->where('is_hidden', false);

        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        if ($request->has('unit') && !empty($request->unit)) {
            $query->where('unit', $request->unit);
        }

        // Get goods with supplier relationship
        $goods = $query->with('suppliers')->orderBy('id', 'desc')->get();

        // Filter by stock status if needed
        if ($request->has('stock')) {
            $filteredGoods = collect();

            foreach ($goods as $good) {
                $inventoryQuantity = $good->getInventoryQuantity();
                $good->inventory_quantity = $inventoryQuantity;

                if ($request->stock === 'in_stock' && $inventoryQuantity > 0) {
                    $filteredGoods->push($good);
                } else if ($request->stock === 'out_of_stock' && $inventoryQuantity <= 0) {
                    $filteredGoods->push($good);
                }
            }

            $goods = $filteredGoods;
        } else {
            // Calculate inventory quantity for each good
            foreach ($goods as $good) {
                $inventoryQuantity = $good->getInventoryQuantity();
                $good->inventory_quantity = $inventoryQuantity;
            }
        }

        // Get filter information to display on the PDF
        $filterInfo = [];
        if ($request->has('search') && !empty($request->search)) {
            $filterInfo[] = 'Từ khóa: "' . $request->search . '"';
        }
        if ($request->has('category') && !empty($request->category)) {
            $filterInfo[] = 'Loại: ' . $request->category;
        }
        if ($request->has('unit') && !empty($request->unit)) {
            $filterInfo[] = 'Đơn vị: ' . $request->unit;
        }
        if ($request->has('stock')) {
            $filterInfo[] = 'Tồn kho: ' . ($request->stock === 'in_stock' ? 'Còn tồn kho' : 'Hết tồn kho');
        }

        // Generate PDF
        $pdf = Pdf::loadView('goods.pdf', [
            'goods' => $goods,
            'filterInfo' => $filterInfo,
            'totalCount' => $goods->count()
        ]);
        
        // Custom PDF settings
        $pdf->setPaper('a4', 'landscape');
        
        // Generate file name
        $fileName = 'danh_sach_hang_hoa_' . date('YmdHis') . '.pdf';
        
        // Download the file
        return $pdf->download($fileName);
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        // Tạo mẫu Excel với dữ liệu mẫu
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thiết lập tiêu đề cột
        $sheet->setCellValue('A1', 'Mã hàng hóa (*)')
            ->setCellValue('B1', 'Tên hàng hóa (*)')
            ->setCellValue('C1', 'Loại hàng hóa (*)')
            ->setCellValue('D1', 'Đơn vị (*)')
            ->setCellValue('E1', 'Ghi chú')
            ->setCellValue('F1', 'Kho tính tồn kho');

        // Dữ liệu mẫu
        $sheet->setCellValue('A2', 'HH001')
            ->setCellValue('B2', 'Ốc vít thông dụng M6')
            ->setCellValue('C2', 'Linh kiện')
            ->setCellValue('D2', 'Cái')
            ->setCellValue('E2', 'Ghi chú mẫu')
            ->setCellValue('F2', 'all');

        $sheet->setCellValue('A3', 'HH002')
            ->setCellValue('B3', 'Ống nhựa PVC 20mm')
            ->setCellValue('C3', 'Vật tư')
            ->setCellValue('D3', 'Mét')
            ->setCellValue('E3', 'Ống nhựa chất lượng cao')
            ->setCellValue('F3', 'all');

        $sheet->setCellValue('A4', 'HH003')
            ->setCellValue('B4', 'Dây điện 2.5mm')
            ->setCellValue('C4', 'Điện')
            ->setCellValue('D4', 'Mét')
            ->setCellValue('E4', 'Dây điện chất lượng cao')
            ->setCellValue('F4', 'all');

        // Style cho header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style cho data rows
        $sheet->getStyle('A2:F4')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);

        // Hướng dẫn nhập liệu
        $sheet->setCellValue('A6', 'HƯỚNG DẪN NHẬP LIỆU:');
        $sheet->setCellValue('A7', '• (*) Các trường bắt buộc phải điền');
        $sheet->setCellValue('A8', '• Mã hàng hóa: Phải duy nhất, không được trùng');
        $sheet->setCellValue('A9', '• Tên hàng hóa: Tối đa 255 ký tự');
        $sheet->setCellValue('A10', '• Loại hàng hóa: Ví dụ: Linh kiện, Vật tư, Điện, Hóa chất...');
        $sheet->setCellValue('A11', '• Đơn vị: Ví dụ: Cái, Bộ, Chiếc, Mét, Cuộn, Kg...');
        $sheet->setCellValue('A12', '• Kho tính tồn kho: Để "all" để tính tất cả kho hoặc để trống');
        $sheet->setCellValue('A13', '• Xóa các dòng mẫu này trước khi import');

        // Style cho hướng dẫn
        $sheet->getStyle('A6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'CC0000']
            ]
        ]);

        $sheet->getStyle('A7:A13')->applyFromArray([
            'font' => [
                'color' => ['rgb' => '666666'],
                'size' => 10
            ]
        ]);

        // Thiết lập độ rộng cột
        $sheet->getColumnDimension('A')->setWidth(15);  // Mã hàng hóa
        $sheet->getColumnDimension('B')->setWidth(30);  // Tên hàng hóa
        $sheet->getColumnDimension('C')->setWidth(15);  // Loại hàng hóa
        $sheet->getColumnDimension('D')->setWidth(12);  // Đơn vị
        $sheet->getColumnDimension('E')->setWidth(25);  // Ghi chú
        $sheet->getColumnDimension('F')->setWidth(15);  // Kho tính tồn kho

        // Tạo tên file
        $fileName = 'mau_import_hang_hoa_' . date('YmdHis') . '.xlsx';

        // Lưu file tạm
        $writer = new Xlsx($spreadsheet);
        $tempFile = storage_path('app/public/temp/' . $fileName);

        // Đảm bảo thư mục tồn tại
        if (!file_exists(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0755, true);
        }

        $writer->save($tempFile);

        // Trả về file để tải xuống
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Import goods data
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ]);

            // Clear any existing import results from session
            session()->forget('import_results');

            $import = new \App\Imports\GoodsImport();
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
                return redirect()->route('goods.import.results')->with('success', $message);
            } else {
                return redirect()->route('goods.import.results')->with('warning', $message);
            }
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    /**
     * Show import results
     */
    public function showImportResults()
    {
        $results = session('import_results');

        if (!$results) {
            return redirect()->route('goods.index')->with('error', 'Không tìm thấy kết quả import.');
        }

        // Clear the session after retrieving the results to prevent reuse
        session()->forget('import_results');

        return view('goods.import_results', compact('results'));
    }

    /**
     * API endpoint for AJAX search and filtering
     */
    public function apiSearch(Request $request)
    {
        // Build the query with filters
        $query = Good::where('status', 'active')
            ->where('is_hidden', false);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Apply unit filter
        if ($request->has('unit') && !empty($request->unit)) {
            $query->where('unit', $request->unit);
        }

        // Get goods
        $goods = $query->get();

        // Initialize grand totals
        $grandTotalQuantity = 0;
        $grandInventoryQuantity = 0;

        // For each good, calculate quantities
        foreach ($goods as $good) {
            // Get inventory quantity from warehouse_materials table
            $inventoryQuantity = $good->getInventoryQuantity();

            // For total quantity, we can use a slightly higher number for demo
            // In production, this should come from a proper calculation including all locations
            $totalQuantity = $inventoryQuantity + rand(0, 10); // Demo: add some random units for items outside warehouses

            $good->total_quantity = $totalQuantity;
            $good->inventory_quantity = $inventoryQuantity;

            // Apply stock filter (must be done after calculating inventory)
            if ($request->has('stock')) {
                if ($request->stock === 'in_stock' && $inventoryQuantity <= 0) {
                    $goods = $goods->reject(function ($item) use ($good) {
                        return $item->id === $good->id;
                    });
                    continue;
                } else if ($request->stock === 'out_of_stock' && $inventoryQuantity > 0) {
                    $goods = $goods->reject(function ($item) use ($good) {
                        return $item->id === $good->id;
                    });
                    continue;
                }
            }

            // Add to grand totals
            $grandTotalQuantity += $good->total_quantity;
            $grandInventoryQuantity += $good->inventory_quantity;
        }

        // Return JSON response
        return response()->json([
            'success' => true,
            'data' => [
                'goods' => $goods,
                'grandTotalQuantity' => $grandTotalQuantity,
                'grandInventoryQuantity' => $grandInventoryQuantity,
                'totalCount' => $goods->count()
            ]
        ]);
    }
}
