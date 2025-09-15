<?php

namespace App\Http\Controllers;

use App\Helpers\ChangeLogHelper;
use App\Models\InventoryImport;
use App\Models\InventoryImportMaterial;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use App\Models\Product;
use App\Models\Good;
use App\Models\Serial;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InventoryImportController extends Controller
{
    /**
     * Tạo mã phiếu nhập tự động
     */
    private function generateImportCode()
    {
        $prefix = 'NK';
        $date = date('ymd');
        
        do {
            // Tạo số random 4 chữ số
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newCode = $prefix . $date . $randomNumber;
            
            // Kiểm tra xem mã đã tồn tại chưa
            $exists = InventoryImport::where('import_code', $newCode)->exists();
        } while ($exists); // Nếu đã tồn tại thì tạo mã mới
        
        return $newCode;
    }

    /**
     * Tạo mã phiếu nhập mới qua API.
     */
    public function generateCode()
    {
        try {
            $newCode = $this->generateImportCode();
            return response()->json([
                'success' => true,
                'code' => $newCode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo mã mới: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị danh sách phiếu nhập kho.
     */
    public function index(Request $request)
    {
        $query = InventoryImport::with(['supplier', 'warehouse', 'materials.material']);

        // Xử lý tìm kiếm
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'import_code':
                    if ($request->filled('search')) {
                        $search = strtolower($request->search);
                        $query->whereRaw('LOWER(import_code) LIKE ?', ['%' . $search . '%']);
                    }
                    break;

                case 'order_code':
                    if ($request->filled('search')) {
                        $search = strtolower($request->search);
                        $query->whereRaw('LOWER(order_code) LIKE ?', ['%' . $search . '%']);
                    }
                    break;

                case 'supplier':
                    if ($request->filled('supplier_id')) {
                        $query->where('supplier_id', $request->supplier_id);
                    }
                    break;

                case 'notes':
                    if ($request->filled('search')) {
                        $search = strtolower($request->search);
                        $query->whereRaw('LOWER(notes) LIKE ?', ['%' . $search . '%']);
                    }
                    break;

                case 'date':
                    if ($request->filled('start_date')) {
                        $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
                        $query->whereDate('import_date', '>=', $startDate);
                    }
                    if ($request->filled('end_date')) {
                        $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');
                        $query->whereDate('import_date', '<=', $endDate);
                    }
                    break;

                case 'status':
                    if ($request->filled('status')) {
                        $query->where('status', $request->status);
                    }
                    break;

                default:
                    // Tìm kiếm tổng quát nếu không chọn bộ lọc cụ thể
                    if ($request->filled('search')) {
                        $search = strtolower($request->search);
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(import_code) LIKE ?', ['%' . $search . '%'])
                                ->orWhereRaw('LOWER(order_code) LIKE ?', ['%' . $search . '%'])
                                ->orWhereRaw('LOWER(notes) LIKE ?', ['%' . $search . '%'])
                                ->orWhereHas('supplier', function ($subq) use ($search) {
                                    $subq->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%']);
                                });
                        });
                    }
            }
        } else {
            // Tìm kiếm tổng quát nếu không chọn bộ lọc
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(import_code) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(order_code) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(notes) LIKE ?', ['%' . $search . '%'])
                        ->orWhereHas('supplier', function ($subq) use ($search) {
                            $subq->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%']);
                        });
                });
            }
        }

        $inventoryImports = $query->latest()->paginate(10);
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        // Giữ lại tham số tìm kiếm khi phân trang
        $inventoryImports->appends($request->all());

        return view('inventory-imports.index', compact('inventoryImports', 'suppliers'));
    }

    /**
     * Hiển thị form tạo phiếu nhập kho mới.
     */
    public function create()
    {
        // Lấy tất cả nhà cung cấp vì không có cột status, sắp xếp theo alphabet
        $suppliers = Supplier::orderBy('name')->get();
        
        // Chỉ lấy các kho đang active
        $warehouses = Warehouse::where('status', 'active')->get();
        
        // Lấy vật tư active và không bị ẩn, sắp xếp theo alphabet
        $materials = Material::where('status', 'active')
            ->where('is_hidden', 0)
            ->orderBy('name')
            ->get()
            ->map(function($material) {
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'unit' => $material->unit,
                    'category' => $material->category,
                    'status' => $material->status,
                    'is_hidden' => $material->is_hidden,
                    'type' => 'material'
                ];
            })
            ->values()  // Reset array keys
            ->all();    // Convert to array
            
        // Lấy hàng hóa active và không bị ẩn, sắp xếp theo alphabet    
        $goods = Good::where('status', 'active')
            ->where('is_hidden', 0)
            ->orderBy('name')
            ->get()
            ->map(function($good) {
                return [
                    'id' => $good->id,
                    'code' => $good->code,
                    'name' => $good->name,
                    'unit' => $good->unit,
                    'category' => $good->category,
                    'status' => $good->status,
                    'is_hidden' => $good->is_hidden,
                    'type' => 'good'
                ];
            })
            ->values()  // Reset array keys
            ->all();    // Convert to array

        // Log để debug
        Log::info('Materials:', ['data' => $materials]);
        Log::info('Goods:', ['data' => $goods]);
        
        // Tạo mã phiếu nhập tự động
        $generated_import_code = $this->generateImportCode();

        return view('inventory-imports.create', [
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'materials' => $materials,
            'goods' => $goods,
            'generated_import_code' => $generated_import_code
        ]);
    }

    /**
     * Lưu phiếu nhập kho mới vào database.
     */
    public function store(Request $request)
    {
        // Validation cơ bản
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'import_code' => 'required|string|max:255|unique:inventory_imports',
            'import_date' => 'required|date_format:d/m/Y',
            'order_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.item_type' => 'required|in:material,good',
            'materials.*.material_id' => 'required|integer',
            'materials.*.warehouse_id' => 'required|exists:warehouses,id',
            'materials.*.quantity' => 'required|integer|min:1',
            'materials.*.serial_numbers' => 'nullable|string',
            'materials.*.notes' => 'nullable|string',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'import_code.required' => 'Mã phiếu nhập không được để trống',
            'import_code.unique' => 'Mã phiếu nhập đã tồn tại',
            'import_date.required' => 'Ngày nhập kho không được để trống',
            'import_date.date_format' => 'Ngày nhập kho phải có định dạng dd/mm/yyyy',
            'materials.required' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.min' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.*.item_type.required' => 'Loại sản phẩm không được để trống',
            'materials.*.item_type.in' => 'Chỉ được chọn Vật tư hoặc Hàng hóa',
            'materials.*.material_id.required' => 'Vật tư không được để trống',
            'materials.*.warehouse_id.required' => 'Kho nhập không được để trống',
            'materials.*.warehouse_id.exists' => 'Kho nhập không tồn tại',
            'materials.*.quantity.required' => 'Số lượng không được để trống',
            'materials.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'materials.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1',
        ]);

        // Validation custom cho số lượng serial và kiểm tra trùng
        $validator->after(function ($validator) use ($request) {
            if ($request->has('materials')) {
                // Mảng lưu trữ tất cả số serial đã nhập để kiểm tra trùng
                $allSerials = [];
                
                foreach ($request->materials as $index => $material) {
                    // Chỉ kiểm tra khi có nhập danh sách số seri
                    if (!empty($material['serial_numbers'])) {
                        $serialArray = preg_split('/[,;\n\r]+/', $material['serial_numbers']);
                        $serialArray = array_map('trim', $serialArray);
                        $serialArray = array_filter($serialArray);
                        
                        // Kiểm tra trùng lặp trong cùng một vật tư
                        $duplicates = array_diff_assoc($serialArray, array_unique($serialArray));
                        if (!empty($duplicates)) {
                            $validator->errors()->add(
                                "materials.{$index}.serial_numbers",
                                "Các số serial sau bị trùng lặp: " . implode(', ', array_unique($duplicates))
                            );
                        }
                        
                        // Kiểm tra trùng lặp với các vật tư khác trong cùng phiếu nhập
                        foreach ($serialArray as $serial) {
                            if (in_array($serial, $allSerials)) {
                                $validator->errors()->add(
                                    "materials.{$index}.serial_numbers",
                                    "Số serial '{$serial}' đã được sử dụng cho vật tư khác trong phiếu nhập này"
                                );
                            } else {
                                $allSerials[] = $serial;
                            }
                        }
                        
                        $serialCount = count($serialArray);
                        $quantity = (int) $material['quantity'];

                        if ($serialCount != $quantity) {
                            $validator->errors()->add(
                                "materials.{$index}.serial_numbers",
                                "Số lượng số seri ({$serialCount}) phải bằng với số lượng vật tư nhập ({$quantity})"
                            );
                        }

                        // Kiểm tra serial number đã tồn tại chưa
                        if (!empty($material['item_type']) && !empty($material['material_id'])) {
                            $itemType = $material['item_type'];
                            $itemId = (int) $material['material_id'];

                            foreach ($serialArray as $serialNumber) {
                                $existingSerial = Serial::where([
                                    'product_id' => $itemId,
                                    'type' => $itemType,
                                    'serial_number' => $serialNumber
                                ])->first();

                                if ($existingSerial) {
                                    $validator->errors()->add(
                                        "materials.{$index}.serial_numbers",
                                        "Số seri '{$serialNumber}' đã tồn tại trong hệ thống cho sản phẩm này"
                                    );
                                }
                            }
                        }
                    }

                    // Kiểm tra material_id có tồn tại và đang active
                    if (!empty($material['item_type']) && !empty($material['material_id'])) {
                        $itemExists = false;
                        $itemId = (int) $material['material_id'];

                        switch ($material['item_type']) {
                            case 'material':
                                $itemExists = Material::where('id', $itemId)
                                    ->where('status', 'active')
                                    ->exists();
                                break;
                            case 'good':
                                $itemExists = Good::where('id', $itemId)
                                    ->where('status', 'active')
                                    ->exists();
                                break;
                        }

                        if (!$itemExists) {
                            $validator->errors()->add(
                                "materials.{$index}.material_id",
                                "Sản phẩm đã chọn không tồn tại hoặc đã bị vô hiệu hóa"
                            );
                        }
                    }
                    
                    // Kiểm tra kho nhập có đang active không
                    if (!empty($material['warehouse_id'])) {
                        $warehouseActive = Warehouse::where('id', $material['warehouse_id'])
                            ->where('status', 'active')
                            ->exists();
                            
                        if (!$warehouseActive) {
                            $validator->errors()->add(
                                "materials.{$index}.warehouse_id",
                                "Kho nhập đã chọn không tồn tại hoặc đã bị vô hiệu hóa"
                            );
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Chuyển đổi định dạng ngày từ d/m/Y sang Y-m-d
            $importDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->import_date)->format('Y-m-d');
            
            // Tạo phiếu nhập kho với trạng thái pending
            $inventoryImport = InventoryImport::create([
                'supplier_id' => $request->supplier_id,
                'import_code' => $request->import_code,
                'import_date' => $importDate,
                'order_code' => $request->order_code,
                'notes' => $request->notes,
                'status' => 'pending' // Mặc định là chờ xử lý
            ]);

            // Ghi nhật ký tạo mới phiếu nhập kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'inventory_imports',
                    'Tạo mới phiếu nhập kho: ' . $inventoryImport->import_code,
                    null,
                    $inventoryImport->toArray()
                );
            }

            // Thêm các vật tư vào phiếu nhập kho
            foreach ($request->materials as $material) {
                // Xử lý danh sách số serial (nếu có)
                $serialNumbers = null;
                if (!empty($material['serial_numbers'])) {
                    // Phân tách các số serial bằng dấu phẩy, xuống dòng hoặc dấu chấm phẩy
                    $serialArray = preg_split('/[,;\n\r]+/', $material['serial_numbers']);
                    $serialArray = array_map('trim', $serialArray); // Loại bỏ khoảng trắng thừa
                    $serialArray = array_filter($serialArray); // Loại bỏ các giá trị trống
                    if (count($serialArray) > 0) {
                        $serialNumbers = $serialArray;
                    }
                }

                $warehouseId = $material['warehouse_id'];
                $itemType = $material['item_type'];
                $itemId = $material['material_id'];

                InventoryImportMaterial::create([
                    'inventory_import_id' => $inventoryImport->id,
                    'material_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $material['quantity'],
                    'serial_numbers' => $serialNumbers,
                    'notes' => $material['notes'] ?? null,
                    'item_type' => $itemType,
                ]);

            }

            DB::commit();
            return redirect()->route('inventory-imports.show', $inventoryImport->id)
                ->with('success', 'Phiếu nhập kho đã được tạo thành công và đang chờ duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị chi tiết phiếu nhập kho.
     */
    public function show(string $id)
    {
        $inventoryImport = InventoryImport::with(['supplier', 'warehouse', 'materials.material'])->findOrFail($id);

        // Ghi nhật ký xem chi tiết phiếu nhập kho
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'inventory_imports',
                'Xem chi tiết phiếu nhập kho: ' . $inventoryImport->import_code,
                null,
                $inventoryImport->toArray()
            );
        }
        
        return view('inventory-imports.show', compact('inventoryImport'));  
    }

    /**
     * Hiển thị form chỉnh sửa phiếu nhập kho.
     */
    public function edit(string $id)
    {
        $inventoryImport = InventoryImport::with(['supplier', 'warehouse', 'materials.material'])->findOrFail($id);
        $suppliers = Supplier::orderBy('name')->get();
        
        // Chỉ lấy kho active (không bị ẩn/xóa) - giống như WarehouseTransferController
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
            
        // Lấy vật tư active và không bị ẩn, sắp xếp theo alphabet
        $materials = Material::where('status', 'active')
            ->where('is_hidden', 0)
            ->orderBy('name')
            ->get()
            ->map(function($material) {
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'unit' => $material->unit,
                    'category' => $material->category,
                    'status' => $material->status,
                    'is_hidden' => $material->is_hidden,
                    'type' => 'material'
                ];
            })
            ->values()
            ->all();
            
        // Lấy hàng hóa active và không bị ẩn, sắp xếp theo alphabet    
        $goods = Good::where('status', 'active')
            ->where('is_hidden', 0)
            ->orderBy('name')
            ->get()
            ->map(function($good) {
                return [
                    'id' => $good->id,
                    'code' => $good->code,
                    'name' => $good->name,
                    'unit' => $good->unit,
                    'category' => $good->category,
                    'status' => $good->status,
                    'is_hidden' => $good->is_hidden,
                    'type' => 'good'
                ];
            })
            ->values()
            ->all();

        return view('inventory-imports.edit', compact('inventoryImport', 'suppliers', 'warehouses', 'materials', 'goods'));
    }

    /**
     * Cập nhật phiếu nhập kho trong database.
     */
    public function update(Request $request, string $id)
    {
        // Validation cơ bản
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'import_code' => 'required|string|max:255|unique:inventory_imports,import_code,' . $id,
            'import_date' => 'required|date_format:d/m/Y',
            'order_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.item_type' => 'required|in:material,product,good',
            'materials.*.material_id' => 'required|integer',
            'materials.*.warehouse_id' => 'required|exists:warehouses,id',
            'materials.*.quantity' => 'required|integer|min:1',
            'materials.*.serial_numbers' => 'nullable|string',
            'materials.*.notes' => 'nullable|string',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'import_code.required' => 'Mã phiếu nhập không được để trống',
            'import_code.unique' => 'Mã phiếu nhập đã tồn tại',
            'import_date.required' => 'Ngày nhập kho không được để trống',
            'import_date.date_format' => 'Ngày nhập kho phải có định dạng dd/mm/yyyy',
            'materials.required' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.min' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.*.item_type.required' => 'Loại sản phẩm không được để trống',
            'materials.*.item_type.in' => 'Loại sản phẩm không hợp lệ',
            'materials.*.material_id.required' => 'Vật tư không được để trống',
            'materials.*.warehouse_id.required' => 'Kho nhập không được để trống',
            'materials.*.warehouse_id.exists' => 'Kho nhập không tồn tại',
            'materials.*.quantity.required' => 'Số lượng không được để trống',
            'materials.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'materials.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1',
        ]);

        // Validation custom cho số lượng serial
        $validator->after(function ($validator) use ($request, $id) {
            if ($request->has('materials')) {
                foreach ($request->materials as $index => $material) {
                    // Chỉ kiểm tra khi có nhập danh sách số seri
                    if (!empty($material['serial_numbers'])) {
                        $serialArray = preg_split('/[,;\n\r]+/', $material['serial_numbers']);
                        $serialArray = array_map('trim', $serialArray);
                        $serialArray = array_filter($serialArray);
                        $serialCount = count($serialArray);
                        $quantity = (int) $material['quantity'];

                        if ($serialCount != $quantity) {
                            $validator->errors()->add(
                                "materials.{$index}.serial_numbers",
                                "Số lượng số seri ({$serialCount}) phải bằng với số lượng vật tư nhập ({$quantity})"
                            );
                        }

                        // Kiểm tra serial number đã tồn tại chưa (ngoại trừ serials thuộc về chính phiếu nhập này)
                        if (!empty($material['item_type']) && !empty($material['material_id'])) {
                            $itemType = $material['item_type'];
                            $itemId = (int) $material['material_id'];

                            // Nếu đang trong chế độ chỉnh sửa, lấy danh sách serial của phiếu nhập hiện tại
                            $currentImportSerials = [];
                            if (isset($id)) {
                                $currentImportMaterials = InventoryImportMaterial::where('inventory_import_id', $id)->get();
                                foreach ($currentImportMaterials as $importMaterial) {
                                    if (!empty($importMaterial->serial_numbers)) {
                                        if (is_string($importMaterial->serial_numbers)) {
                                            $serialsArray = json_decode($importMaterial->serial_numbers, true);
                                            if (is_array($serialsArray)) {
                                                foreach ($serialsArray as $sn) {
                                                    $currentImportSerials[] = $sn;
                                                }
                                            }
                                        } else if (is_array($importMaterial->serial_numbers)) {
                                            foreach ($importMaterial->serial_numbers as $sn) {
                                                $currentImportSerials[] = $sn;
                                            }
                                        }
                                    }
                                }
                            }

                            foreach ($serialArray as $serialIndex => $serialNumber) {
                                // Nếu serial thuộc phiếu nhập hiện tại, bỏ qua kiểm tra trùng lặp
                                if (in_array($serialNumber, $currentImportSerials)) {
                                    continue;
                                }

                                // Kiểm tra serial có tồn tại trong hệ thống không
                                // Cần kiểm tra chính xác type và product_id để không báo lỗi khi serial trùng với loại sản phẩm khác
                                $existingSerial = Serial::where([
                                    'product_id' => $itemId,
                                    'type' => $itemType,
                                    'serial_number' => $serialNumber
                                ])->first();

                                if ($existingSerial) {
                                    $validator->errors()->add(
                                        "materials.{$index}.serial_numbers",
                                        "Số seri '{$serialNumber}' đã tồn tại trong hệ thống cho sản phẩm này"
                                    );
                                }
                            }
                        }
                    }

                    // Kiểm tra material_id có tồn tại trong loại sản phẩm tương ứng
                    if (!empty($material['item_type']) && !empty($material['material_id'])) {
                        $itemExists = false;
                        $itemId = (int) $material['material_id'];

                        switch ($material['item_type']) {
                            case 'material':
                                $itemExists = Material::where('id', $itemId)->exists();
                                break;
                            case 'product':
                                $itemExists = Product::where('id', $itemId)->exists();
                                break;
                            case 'good':
                                $itemExists = Good::where('id', $itemId)->exists();
                                break;
                        }

                        if (!$itemExists) {
                            $validator->errors()->add(
                                "materials.{$index}.material_id",
                                "Sản phẩm đã chọn không tồn tại trong hệ thống"
                            );
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Lấy phiếu nhập kho hiện tại và các vật tư liên quan
            $inventoryImport = InventoryImport::with('materials')->findOrFail($id);
            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $inventoryImport->toArray();
            $oldMaterials = $inventoryImport->materials->toArray();

            // Trước khi cập nhật, cần giảm số lượng vật tư trong kho cũ và xóa serials cũ
            foreach ($oldMaterials as $oldMaterial) {
                $materialId = $oldMaterial['material_id'];
                $quantity = $oldMaterial['quantity'];
                $warehouseId = $oldMaterial['warehouse_id'];
                $itemType = $oldMaterial['item_type'] ?? 'material';
                $serialNumbers = $oldMaterial['serial_numbers'] ?? null;

                // Xóa serial numbers từ bảng serials (nếu có)
                if (!empty($serialNumbers) && is_array($serialNumbers)) {
                    foreach ($serialNumbers as $serial) {
                        Serial::where([
                            'product_id' => $materialId,
                            'type' => $itemType,
                            'serial_number' => $serial
                        ])->delete();
                    }
                }

                // Giảm số lượng trong kho
                $warehouseMaterial = WarehouseMaterial::where([
                    'warehouse_id' => $warehouseId,
                    'material_id' => $materialId,
                    'item_type' => $itemType
                ])->first();

                if ($warehouseMaterial) {
                    if ($warehouseMaterial->quantity <= $quantity) {
                        $warehouseMaterial->delete();
                    } else {
                        $warehouseMaterial->quantity -= $quantity;
                        $warehouseMaterial->save();
                    }
                }
            }

            // Lưu thông tin cũ để so sánh
            $oldSupplierId = $inventoryImport->supplier_id;
            $oldImportCode = $inventoryImport->import_code;

            // Chuyển đổi định dạng ngày từ d/m/Y sang Y-m-d
            $importDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->import_date)->format('Y-m-d');
            
            // Cập nhật phiếu nhập kho - không còn cần warehouse_id
            $inventoryImport->update([
                'supplier_id' => $request->supplier_id,
                'import_code' => $request->import_code,
                'import_date' => $importDate,
                'order_code' => $request->order_code,
                'notes' => $request->notes,
            ]);

            // Cập nhật nhật ký thay đổi nếu có thay đổi nhà cung cấp hoặc mã phiếu
            if ($oldSupplierId != $request->supplier_id || $oldImportCode != $request->import_code) {
                // Lấy thông tin nhà cung cấp mới
                $newSupplier = \App\Models\Supplier::find($request->supplier_id);
                $newSupplierName = $newSupplier ? $newSupplier->name : 'Không xác định';

                // Cập nhật tất cả các nhật ký thay đổi có document_code trùng với mã phiếu cũ
                \App\Models\ChangeLog::where('document_code', $oldImportCode)
                    ->where('change_type', 'nhap_kho')
                    ->update([
                        'document_code' => $request->import_code,
                        'description' => $newSupplierName,
                        'detailed_info' => DB::raw("JSON_SET(
                            COALESCE(detailed_info, '{}'),
                            '$.supplier_id', " . $request->supplier_id . ",
                            '$.supplier_name', '" . addslashes($newSupplierName) . "',
                            '$.order_code', '" . addslashes($request->order_code ?? '') . "',
                            '$.import_date', '" . $importDate . "'
                        )")
                    ]);
            }

            // Xóa tất cả các vật tư cũ của phiếu nhập kho
            $inventoryImport->materials()->delete();

            // Thêm các vật tư mới vào phiếu nhập kho
            foreach ($request->materials as $material) {
                // Xử lý danh sách số serial (nếu có)
                $serialNumbers = null;
                if (!empty($material['serial_numbers'])) {
                    // Phân tách các số serial bằng dấu phẩy, xuống dòng hoặc dấu chấm phẩy
                    $serialArray = preg_split('/[,;\n\r]+/', $material['serial_numbers']);
                    $serialArray = array_map('trim', $serialArray); // Loại bỏ khoảng trắng thừa
                    $serialArray = array_filter($serialArray); // Loại bỏ các giá trị trống
                    if (count($serialArray) > 0) {
                        $serialNumbers = $serialArray;
                    }
                }

                $warehouseId = $material['warehouse_id'];
                $itemType = $material['item_type'] ?? 'material';
                $itemId = $material['material_id'];

                InventoryImportMaterial::create([
                    'inventory_import_id' => $inventoryImport->id,
                    'material_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $material['quantity'],
                    'serial_numbers' => $serialNumbers,
                    'notes' => $material['notes'] ?? null,
                    'item_type' => $itemType,
                ]);

                // KHÔNG cập nhật tồn kho khi chỉnh sửa phiếu nhập kho
                // Chỉ cập nhật tồn kho khi duyệt phiếu (method approve)
            }

            DB::commit();
            
            // Ghi nhật ký cập nhật phiếu nhập kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'inventory_imports',
                    'Cập nhật phiếu nhập kho: ' . $inventoryImport->import_code,
                    $oldData,
                    $inventoryImport->toArray()
                );
            }

            return redirect()->route('inventory-imports.show', $id)
                ->with('success', 'Phiếu nhập kho đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa phiếu nhập kho khỏi database.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $inventoryImport = InventoryImport::with('materials')->findOrFail($id);
            // Lưu dữ liệu cũ trước khi xóa
            $oldData = $inventoryImport->toArray();

            $warehouseId = $inventoryImport->warehouse_id;  

            // Giảm số lượng vật tư trong kho và xóa serials
            foreach ($inventoryImport->materials as $material) {
                $materialId = $material->material_id;
                $quantity = $material->quantity;
                $serialNumbers = $material->serial_numbers;
                $itemType = $material->item_type ?? 'material';

                // Xóa serial numbers từ bảng serials (nếu có)
                if (!empty($serialNumbers) && is_array($serialNumbers)) {
                    foreach ($serialNumbers as $serial) {
                        Serial::where([
                            'product_id' => $materialId,
                            'type' => $itemType,
                            'serial_number' => $serial
                        ])->delete();
                    }
                }

                // Giảm số lượng trong kho
                $warehouseMaterial = WarehouseMaterial::where([
                    'warehouse_id' => $material->warehouse_id,
                    'material_id' => $materialId,
                    'item_type' => $itemType
                ])->first();

                if ($warehouseMaterial) {
                    if ($warehouseMaterial->quantity <= $quantity) {
                        $warehouseMaterial->delete();
                    } else {
                        $warehouseMaterial->quantity -= $quantity;
                        $warehouseMaterial->save();
                    }
                }
            }

            $inventoryImport->materials()->delete();
            $inventoryImport->delete();

            DB::commit();

            // Ghi nhật ký xóa phiếu nhập kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'inventory_imports',        
                    'Xóa phiếu nhập kho: ' . $inventoryImport->import_code,
                    $oldData,
                    null
                );
            }

            return redirect()->route('inventory-imports.index')
                ->with('success', 'Phiếu nhập kho đã được xóa thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Duyệt phiếu nhập kho
     */
    public function approve(string $id)
    {
        DB::beginTransaction();
        try {
            $inventoryImport = InventoryImport::with('materials')->findOrFail($id);
            
            // Kiểm tra xem phiếu đã được duyệt chưa
            if ($inventoryImport->status === 'approved') {
                return back()->with('error', 'Phiếu nhập kho này đã được duyệt trước đó.');
            }
            
            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $inventoryImport->toArray();
            
            // Cập nhật trạng thái phiếu
            $inventoryImport->status = 'approved';
            $inventoryImport->save();

            // Cập nhật số lượng tồn kho và serial numbers
            foreach ($inventoryImport->materials as $material) {
                // Cập nhật số lượng vật tư/thành phẩm/hàng hóa trong kho
                $warehouseMaterial = WarehouseMaterial::firstOrNew([
                    'warehouse_id' => $material->warehouse_id,
                    'material_id' => $material->material_id,
                    'item_type' => $material->item_type
                ]);

                $currentQty = $warehouseMaterial->quantity ?? 0;
                $warehouseMaterial->quantity = $currentQty + $material->quantity;

                // Luôn lưu cập nhật số lượng, kể cả khi không có serial
                $warehouseMaterial->save();

                // Cập nhật serial_number vào warehouse_materials nếu có serial
                if (!empty($material->serial_numbers)) {
                    $serials = is_array($material->serial_numbers)
                        ? $material->serial_numbers
                        : (json_decode($material->serial_numbers, true) ?: []);
                    $currentSerials = [];
                    if (!empty($warehouseMaterial->serial_number)) {
                        $decoded = json_decode($warehouseMaterial->serial_number, true);
                        $currentSerials = is_array($decoded) ? $decoded : [];
                    }
                    // Gộp serial cũ và mới, loại bỏ trùng lặp (tránh dùng array_merge để khỏi cảnh báo kiểu)
                    foreach ($serials as $sn) {
                        $currentSerials[] = $sn;
                    }
                    $mergedSerials = array_values(array_unique($currentSerials));
                    $warehouseMaterial->serial_number = json_encode($mergedSerials);
                    $warehouseMaterial->save(); // Save lại cho serials
                }

                // Lưu serial numbers vào bảng serials (nếu có)
                if (!empty($material->serial_numbers)) {
                    foreach ($material->serial_numbers as $serialNumber) {
                        Serial::create([
                            'serial_number' => $serialNumber,
                            'product_id' => $material->material_id,
                            'type' => $material->item_type,
                            'status' => 'active',
                            'notes' => $material->notes ?? null,
                            'warehouse_id' => $material->warehouse_id,
                        ]);
                    }
                }

                // Lưu nhật ký thay đổi khi phiếu được duyệt
                $itemType = $material->item_type;
                $itemId = $material->material_id;

                if ($itemType == 'material') {
                    $materialLS = Material::find($itemId);
                } else if ($itemType == 'good') {
                    $materialLS = Good::find($itemId);
                }

                if ($materialLS) {
                    // Lấy thông tin kho nhập để đưa vào description
                    $warehouse = \App\Models\Warehouse::find($material->warehouse_id);
                    $warehouseName = $warehouse ? $warehouse->name : 'Không xác định';

                    ChangeLogHelper::nhapKho(
                        $materialLS->code,
                        $materialLS->name,
                        $material->quantity,
                        $inventoryImport->import_code,
                        $warehouseName,
                        $material->notes
                    );
                }
            }
            
            DB::commit();
            
            // Ghi nhật ký duyệt phiếu nhập kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'inventory_imports',
                    'Duyệt phiếu nhập kho: ' . $inventoryImport->import_code,
                    $oldData,
                    $inventoryImport->toArray()
                );
            }
            
            return redirect()->route('inventory-imports.show', $id)
                ->with('success', 'Phiếu nhập kho đã được duyệt thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Trả về thông tin chi tiết của vật tư qua API.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMaterialInfo($id)
    {
        try {
            $material = Material::with('supplier')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $material
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vật tư'
            ], 404);
        }
    }
}
