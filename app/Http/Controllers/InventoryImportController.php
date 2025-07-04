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
use Illuminate\Support\Facades\Validator;

class InventoryImportController extends Controller
{
    /**
     * Hiển thị danh sách phiếu nhập kho.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        $query = InventoryImport::with(['supplier', 'warehouse', 'materials.material']);

        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'import_code':
                        $query->where('import_code', 'like', "%{$search}%");
                        break;
                    case 'order_code':
                        $query->where('order_code', 'like', "%{$search}%");
                        break;
                    case 'supplier':
                        $query->whereHas('supplier', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('import_code', 'like', "%{$search}%")
                        ->orWhere('order_code', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($subq) use ($search) {
                            $subq->where('name', 'like', "%{$search}%");
                        });
                });
            }
        }

        $inventoryImports = $query->latest()->paginate(10);

        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $inventoryImports->appends([
            'search' => $search,
            'filter' => $filter
        ]);

        return view('inventory-imports.index', compact('inventoryImports', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo phiếu nhập kho mới.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $materials = Material::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $goods = Good::where('status', 'active')->get();

        return view('inventory-imports.create', compact('suppliers', 'warehouses', 'materials', 'products', 'goods'));
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
            'import_date' => 'required|date',
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
            'import_date.date' => 'Ngày nhập kho không hợp lệ',
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
        $validator->after(function ($validator) use ($request) {
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

                            // Khi tạo mới, không cần kiểm tra serials của phiếu nhập hiện tại
                            foreach ($serialArray as $serialIndex => $serialNumber) {
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
            // Tạo phiếu nhập kho
            $inventoryImport = InventoryImport::create([
                'supplier_id' => $request->supplier_id,
                'import_code' => $request->import_code,
                'import_date' => $request->import_date,
                'order_code' => $request->order_code,
                'notes' => $request->notes,
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

                //Lưu nhật ký thay đổi
                if ($itemType == 'material') {
                    $materialLS = Material::find($itemId);
                } else if ($itemType == 'product') {
                    $materialLS = Product::find($itemId);
                } else if ($itemType == 'good') {
                    $materialLS = Good::find($itemId);
                }

                // Lấy thông tin nhà cung cấp để đưa vào description
                $supplier = \App\Models\Supplier::find($inventoryImport->supplier_id);
                $supplierName = $supplier ? $supplier->name : 'Không xác định';

                ChangeLogHelper::nhapKho(
                    $materialLS->code,
                    $materialLS->name,
                    $material['quantity'],
                    $inventoryImport->import_code,
                    $supplierName,
                    $material['notes']
                );

                // Cập nhật số lượng vật tư/thành phẩm/hàng hóa trong kho
                $warehouseMaterial = WarehouseMaterial::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'material_id' => $itemId,
                    'item_type' => $itemType
                ]);

                $currentQty = $warehouseMaterial->quantity ?? 0;
                $warehouseMaterial->quantity = $currentQty + $material['quantity'];

                // Lưu serial numbers vào bảng serials (nếu có)
                if (!empty($serialNumbers)) {
                    foreach ($serialNumbers as $serialNumber) {
                        Serial::create([
                            'serial_number' => $serialNumber,
                            'product_id' => $itemId,
                            'type' => $itemType,
                            'status' => 'active',
                            'notes' => $material['notes'] ?? null,
                            'warehouse_id' => $warehouseId,
                        ]);
                    }
                }

                $warehouseMaterial->save();
            }

            DB::commit();
            return redirect()->route('inventory-imports.index')
                ->with('success', 'Phiếu nhập kho đã được thêm thành công.');
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
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $materials = Material::all();
        $products = Product::all();
        $goods = Good::all();

        return view('inventory-imports.edit', compact('inventoryImport', 'suppliers', 'warehouses', 'materials', 'products', 'goods'));
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
            'import_date' => 'required|date',
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
            'import_date.date' => 'Ngày nhập kho không hợp lệ',
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
                                            $serialsArray = json_decode($importMaterial->serial_numbers);
                                            if (is_array($serialsArray)) {
                                                $currentImportSerials = array_merge($currentImportSerials, $serialsArray);
                                            }
                                        } else if (is_array($importMaterial->serial_numbers)) {
                                            $currentImportSerials = array_merge($currentImportSerials, $importMaterial->serial_numbers);
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

            // Cập nhật phiếu nhập kho - không còn cần warehouse_id
            $inventoryImport->update([
                'supplier_id' => $request->supplier_id,
                'import_code' => $request->import_code,
                'import_date' => $request->import_date,
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
                            '$.import_date', '" . $request->import_date . "'
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

                // Cập nhật số lượng vật tư trong kho mới
                $warehouseMaterial = WarehouseMaterial::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'material_id' => $itemId,
                    'item_type' => $itemType
                ]);

                $currentQty = $warehouseMaterial->quantity ?? 0;
                $warehouseMaterial->quantity = $currentQty + $material['quantity'];
                $warehouseMaterial->save();

                // Lưu serial numbers vào bảng serials (nếu có)
                if (!empty($serialNumbers)) {
                    foreach ($serialNumbers as $serialNumber) {
                        Serial::create([
                            'serial_number' => $serialNumber,
                            'product_id' => $itemId,
                            'type' => $itemType,
                            'status' => 'active',
                            'notes' => $material['notes'] ?? null,
                            'warehouse_id' => $warehouseId,
                        ]);
                    }
                }
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
