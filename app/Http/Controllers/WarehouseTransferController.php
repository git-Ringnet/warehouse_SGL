<?php

namespace App\Http\Controllers;

use App\Helpers\ChangeLogHelper;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\UserLog;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferMaterial;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WarehouseTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WarehouseTransfer::query()->with(['source_warehouse', 'destination_warehouse', 'material', 'employee', 'materials']);

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $filter = $request->filter ?? '';

            switch ($filter) {
                case 'serial':
                    $query->where('serial', 'like', "%{$search}%");
                    break;
                case 'material':
                    $query->where(function ($q) use ($search) {
                        // Tìm trong bảng materials
                        $q->whereHas('material', function ($subQ) use ($search) {
                            $subQ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        // Hoặc tìm trong bảng products
                        ->orWhereExists(function ($subQ) use ($search) {
                            $subQ->select(DB::raw(1))
                                ->from('products')
                                ->whereRaw('products.id = warehouse_transfers.material_id')
                                ->where(function ($productQ) use ($search) {
                                    $productQ->where('name', 'like', "%{$search}%")
                                        ->orWhere('code', 'like', "%{$search}%");
                                });
                        })
                        // Hoặc tìm trong bảng goods
                        ->orWhereExists(function ($subQ) use ($search) {
                            $subQ->select(DB::raw(1))
                                ->from('goods')
                                ->whereRaw('goods.id = warehouse_transfers.material_id')
                                ->where(function ($goodQ) use ($search) {
                                    $goodQ->where('name', 'like', "%{$search}%")
                                        ->orWhere('code', 'like', "%{$search}%");
                                });
                        });
                    });
                    break;
                case 'source':
                    $query->whereHas('source_warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                    break;
                case 'destination':
                    $query->whereHas('destination_warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                    break;
                case 'status':
                    $status = match (strtolower($search)) {
                        'chờ xác nhận', 'cho xac nhan' => 'pending',
                        'đang chuyển', 'dang chuyen' => 'in_progress',
                        'hoàn thành', 'hoan thanh' => 'completed',
                        'đã hủy', 'da huy' => 'canceled',
                        default => $search
                    };
                    $query->where('status', 'like', "%{$status}%");
                    break;
                default:
                    $query->where(function ($q) use ($search) {
                        $q->where('transfer_code', 'like', "%{$search}%")
                            ->orWhere('serial', 'like', "%{$search}%")
                            // Tìm trong bảng materials
                            ->orWhereHas('material', function ($subQ) use ($search) {
                                $subQ->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            })
                            // Hoặc tìm trong bảng products
                            ->orWhereExists(function ($subQ) use ($search) {
                                $subQ->select(DB::raw(1))
                                    ->from('products')
                                    ->whereRaw('products.id = warehouse_transfers.material_id')
                                    ->where(function ($productQ) use ($search) {
                                        $productQ->where('name', 'like', "%{$search}%")
                                            ->orWhere('code', 'like', "%{$search}%");
                                    });
                            })
                            // Hoặc tìm trong bảng goods
                            ->orWhereExists(function ($subQ) use ($search) {
                                $subQ->select(DB::raw(1))
                                    ->from('goods')
                                    ->whereRaw('goods.id = warehouse_transfers.material_id')
                                    ->where(function ($goodQ) use ($search) {
                                        $goodQ->where('name', 'like', "%{$search}%")
                                            ->orWhere('code', 'like', "%{$search}%");
                                    });
                            });
                    });
            }
        }

        $warehouseTransfers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('warehouse-transfers.index', compact('warehouseTransfers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Chỉ lấy kho active (không bị ẩn/xóa)
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
            
        $employees = Employee::orderBy('name')->get();
        $materials = Material::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $products = Product::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $goods = Good::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();

        // Generate mã phiếu chuyển kho mặc định
        $lastTransfer = WarehouseTransfer::orderBy('id', 'desc')->first();
        $today = now();
        $prefix = 'CT' . $today->format('ymd');
        
        if ($lastTransfer) {
            $lastCode = $lastTransfer->transfer_code;
            if (preg_match('/^CT\d{6}(\d{4})$/', $lastCode, $matches)) {
                $sequence = intval($matches[1]) + 1;
            } else {
                $sequence = 1;
            }
        } else {
            $sequence = 1;
        }
        
        $generated_transfer_code = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return view('warehouse-transfers.create', compact(
            'warehouses',
            'employees',
            'materials',
            'products',
            'goods',
            'generated_transfer_code'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'transfer_code' => 'required|string|max:50|unique:warehouse_transfers',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'employee_id' => 'nullable|exists:employees,id', // Bỏ required cho employee_id
            'materials_json' => 'required|json',
        ], [
            'transfer_code.required' => 'Mã phiếu chuyển là bắt buộc',
            'transfer_code.unique' => 'Mã phiếu chuyển đã tồn tại',
            'source_warehouse_id.required' => 'Kho nguồn là bắt buộc',
            'destination_warehouse_id.required' => 'Kho đích là bắt buộc',
            'destination_warehouse_id.different' => 'Kho đích không được trùng với kho nguồn',
            'transfer_date.required' => 'Ngày chuyển kho là bắt buộc',
        ]);

        try {
            DB::beginTransaction();

            // Phân tích chuỗi JSON vật tư
            $materialsData = json_decode($request->materials_json, true);
            if (empty($materialsData)) {
                return back()->with('error', 'Danh sách vật tư không hợp lệ')->withInput();
            }
            
            // Kiểm tra tính hợp lệ của material_id cho từng item
            foreach ($materialsData as $material) {
                $materialId = $material['id'];
                $itemType = $material['type'] ?? 'material';
                
                // Kiểm tra xem material_id có tồn tại trong bảng tương ứng không
                $exists = false;
                switch ($itemType) {
                    case 'material':
                        $exists = Material::where('id', $materialId)->exists();
                        break;
                    case 'product':
                        $exists = Product::where('id', $materialId)->exists();
                        break;
                    case 'good':
                        $exists = Good::where('id', $materialId)->exists();
                        break;
                }
                
                if (!$exists) {
                    return back()->with('error', "Không tìm thấy {$itemType} với ID: {$materialId}")->withInput();
                }
            }
            
            // Kiểm tra serial trùng giữa các item
            $allSerials = [];
            $serialDuplicates = [];
            foreach ($materialsData as $idx => $material) {
                $serials = $material['serial_numbers'] ?? [];
                if (!is_array($serials)) {
                    $serials = preg_split('/[,;\n\r]+/', $serials);
                    $serials = array_map('trim', $serials);
                    $serials = array_filter($serials);
                }
                foreach ($serials as $serial) {
                    if (in_array($serial, $allSerials)) {
                        $serialDuplicates[] = $serial;
                    } else {
                        $allSerials[] = $serial;
                    }
                }
            }
            if (!empty($serialDuplicates)) {
                return back()->with('error', 'Serial bị trùng giữa các vật tư: ' . implode(', ', array_unique($serialDuplicates)))->withInput();
            }

            // Kiểm tra tồn kho trước khi tạo phiếu
            $sourceWarehouseId = $request->source_warehouse_id;
            $inventoryErrors = [];
            
            foreach ($materialsData as $material) {
                $materialId = $material['id'];
                $itemType = $material['type'] ?? 'material';
                $requestedQuantity = (int)$material['quantity'];
                
                // Lấy tồn kho hiện tại
                $stock = WarehouseMaterial::where('warehouse_id', $sourceWarehouseId)
                    ->where('material_id', $materialId)
                    ->where('item_type', $itemType)
                    ->sum('quantity');
                
                // Lấy tên vật tư/sản phẩm
                $itemName = $material['name'] ?? "Mã: {$materialId}";
                
                // Kiểm tra nếu số lượng yêu cầu vượt quá tồn kho
                if ($requestedQuantity > $stock) {
                    $inventoryErrors[] = "Số lượng yêu cầu ({$requestedQuantity}) của {$itemName} vượt quá tồn kho ({$stock})";
                }
            }
            
            // Nếu có lỗi tồn kho, trả về thông báo lỗi
            if (!empty($inventoryErrors)) {
                return back()->with('error', implode('<br>', $inventoryErrors))->withInput();
            }

            // Lấy vật tư chính (vật tư đầu tiên)
            $primaryMaterial = $materialsData[0];
            $materialId = $primaryMaterial['id'];
            $itemType = $primaryMaterial['type'] ?? 'material';

            // Tạo phiếu chuyển kho với status mặc định là pending
            $warehouseTransfer = WarehouseTransfer::create([
                'transfer_code' => $request->transfer_code,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'material_id' => $materialId,
                'quantity' => $request->quantity ?? 1,
                'transfer_date' => $request->transfer_date,
                'employee_id' => $request->employee_id, // Có thể null
                'status' => 'pending', // Mặc định là pending
                'notes' => $request->notes,
            ]);

            // Kiểm tra nếu trạng thái là 'completed'
            $isCompleted = $request->status === 'completed';

            // Lưu chi tiết vật tư
            foreach ($materialsData as $material) {
                $serialNumbers = null;
                if (!empty($material['serial_numbers'])) {
                    // Tin tưởng frontend đã gửi đúng mảng serial, chỉ cần chuẩn hóa lại một lần nữa cho chắc chắn
                    if (is_array($material['serial_numbers'])) {
                        $serialNumbers = array_map('trim', $material['serial_numbers']);
                    } else {
                        $serialNumbers = array_filter(array_map('trim', preg_split('/[,;\n\r]+/', $material['serial_numbers'])));
                    }
                    $serialNumbers = !empty($serialNumbers) ? $serialNumbers : null;
                }

                WarehouseTransferMaterial::create([
                    'warehouse_transfer_id' => $warehouseTransfer->id,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'],
                    'type' => $material['type'] ?? 'material',
                    'serial_numbers' => $serialNumbers,
                    'notes' => $material['notes'] ?? null,
                ]);

                // Nếu trạng thái là 'completed', cập nhật tồn kho
                if ($isCompleted) {
                    $materialId = $material['id'];
                    $itemType = $material['type'] ?? 'material';
                    $quantity = $material['quantity'];

                    // Lấy thông tin vật tư để tạo nhật ký
                    $itemModel = null;
                    if ($itemType == 'material') {
                        $itemModel = Material::find($materialId);
                    } elseif ($itemType == 'product') {
                        $itemModel = Product::find($materialId);
                    } elseif ($itemType == 'good') {
                        $itemModel = Good::find($materialId);
                    }

                    // Giảm số lượng tồn kho ở kho nguồn
                    $this->updateWarehouseStock(
                        $request->source_warehouse_id,
                        $materialId,
                        $itemType,
                        -$quantity,
                        "Giảm tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                    );

                    // Tăng số lượng tồn kho ở kho đích
                    $this->updateWarehouseStock(
                        $request->destination_warehouse_id,
                        $materialId,
                        $itemType,
                        $quantity,
                        "Tăng tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                    );

                    // Tạo nhật ký chuyển kho
                    if ($itemModel) {
                        $sourceWarehouse = Warehouse::find($request->source_warehouse_id);
                        $destinationWarehouse = Warehouse::find($request->destination_warehouse_id);
                        
                        ChangeLogHelper::chuyenKho(
                            $itemModel->code,
                            $itemModel->name,
                            $quantity,
                            $warehouseTransfer->transfer_code,
                            "Chuyển từ " . ($sourceWarehouse ? $sourceWarehouse->name : 'Kho không xác định') . " sang " . ($destinationWarehouse ? $destinationWarehouse->name : 'Kho không xác định'),
                            [
                                'source_warehouse_id' => $request->source_warehouse_id,
                                'source_warehouse_name' => $sourceWarehouse ? $sourceWarehouse->name : 'Kho không xác định',
                                'destination_warehouse_id' => $request->destination_warehouse_id,
                                'destination_warehouse_name' => $destinationWarehouse ? $destinationWarehouse->name : 'Kho không xác định',
                            ],
                            $warehouseTransfer->notes
                        );
                    }
                }
            }

            DB::commit();

            // Ghi nhật ký tạo mới phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'warehouse_transfers',
                    'Tạo mới phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                    null,
                    $warehouseTransfer->toArray()
                );
            }

            return redirect()->route('warehouse-transfers.index')->with('success', 'Phiếu chuyển kho đã được tạo thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi tạo phiếu chuyển kho')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehouseTransfer $warehouseTransfer)
    {
        $warehouseTransfer->load(['source_warehouse', 'destination_warehouse', 'material', 'employee', 'materials.material']);
        $selectedMaterials = $warehouseTransfer->materials->map(function ($item) {
            $itemName = 'Không xác định';
            $itemCode = '';
            
            // Thử lấy thông tin từ bảng tương ứng
            if ($item->type == 'material' && $item->material) {
                $itemName = $item->material->name;
                $itemCode = $item->material->code;
            } elseif ($item->type == 'product') {
                $product = Product::find($item->material_id);
                if ($product) {
                    $itemName = $product->name;
                    $itemCode = $product->code;
                }
            } elseif ($item->type == 'good') {
                $good = Good::find($item->material_id);
                if ($good) {
                    $itemName = $good->name;
                    $itemCode = $good->code;
                }
            }
            
            return [
                'id' => $item->material_id,
                'name' => $itemCode ? ($itemCode . ' - ' . $itemName) : $itemName,
                'type' => $item->type ?? 'material',
                'quantity' => $item->quantity,
                'serial_numbers' => $item->serial_numbers,
                'notes' => $item->notes
            ];
        })->toArray();

        // Ghi nhật ký xem chi tiết phiếu chuyển kho
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'warehouse_transfers',
                'Xem chi tiết phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                null,
                $warehouseTransfer->toArray()
            );
        }

        return view('warehouse-transfers.show', compact('warehouseTransfer', 'selectedMaterials'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WarehouseTransfer $warehouseTransfer)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $materials = Material::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $products = Product::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $goods = Good::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();

        $warehouseTransfer->load(['materials.material']);
        $selectedMaterials = $warehouseTransfer->materials->map(function ($item) {
            $itemName = 'Không xác định';
            $itemCode = '';
            
            // Thử lấy thông tin từ bảng tương ứng
            if ($item->type == 'material' && $item->material) {
                $itemName = $item->material->name;
                $itemCode = $item->material->code;
            } elseif ($item->type == 'product') {
                $product = Product::find($item->material_id);
                if ($product) {
                    $itemName = $product->name;
                    $itemCode = $product->code;
                }
            } elseif ($item->type == 'good') {
                $good = Good::find($item->material_id);
                if ($good) {
                    $itemName = $good->name;
                    $itemCode = $good->code;
                }
            }
            
            return [
                'id' => $item->material_id,
                'name' => $itemCode ? ($itemCode . ' - ' . $itemName) : $itemName,
                'type' => $item->type ?? 'material',
                'quantity' => $item->quantity,
                'serial_numbers' => $item->serial_numbers,
                'notes' => $item->notes
            ];
        })->toArray();

        return view('warehouse-transfers.edit', compact(
            'warehouseTransfer',
            'warehouses',
            'employees',
            'materials',
            'products',
            'goods',
            'selectedMaterials'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WarehouseTransfer $warehouseTransfer)
    {
        $request->validate([
            'transfer_code' => 'required|string|max:50|unique:warehouse_transfers,transfer_code,' . $warehouseTransfer->id,
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'transfer_date' => 'required|date',
            'employee_id' => 'nullable|exists:employees,id',
            'materials_json' => 'required|json',
        ], [
            'transfer_code.required' => 'Mã phiếu chuyển là bắt buộc',
            'transfer_code.unique' => 'Mã phiếu chuyển đã tồn tại',
            'source_warehouse_id.required' => 'Kho nguồn là bắt buộc',
            'destination_warehouse_id.required' => 'Kho đích là bắt buộc',
            'destination_warehouse_id.different' => 'Kho đích không được trùng với kho nguồn',
            'quantity.required' => 'Số lượng là bắt buộc',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'transfer_date.required' => 'Ngày chuyển kho là bắt buộc',
        ]);

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $warehouseTransfer->toArray();

        try {
            DB::beginTransaction();

            // Phân tích chuỗi JSON vật tư
            $materialsData = json_decode($request->materials_json, true);
            if (empty($materialsData)) {
                return back()->with('error', 'Danh sách vật tư không hợp lệ')->withInput();
            }

            // Kiểm tra tính hợp lệ của material_id cho từng item
            foreach ($materialsData as $material) {
                $materialId = $material['id'];
                $itemType = $material['type'] ?? 'material';
                
                // Kiểm tra xem material_id có tồn tại trong bảng tương ứng không
                $exists = false;
                switch ($itemType) {
                    case 'material':
                        $exists = Material::where('id', $materialId)->exists();
                        break;
                    case 'product':
                        $exists = Product::where('id', $materialId)->exists();
                        break;
                    case 'good':
                        $exists = Good::where('id', $materialId)->exists();
                        break;
                }
                
                if (!$exists) {
                    return back()->with('error', "Không tìm thấy {$itemType} với ID: {$materialId}")->withInput();
                }
            }

            // Kiểm tra serial trùng giữa các item
            $allSerials = [];
            $serialDuplicates = [];
            foreach ($materialsData as $idx => $material) {
                $serials = $material['serial_numbers'] ?? [];
                if (!is_array($serials)) {
                    $serials = preg_split('/[,;\n\r]+/', $serials);
                    $serials = array_map('trim', $serials);
                    $serials = array_filter($serials);
                }
                foreach ($serials as $serial) {
                    if (in_array($serial, $allSerials)) {
                        $serialDuplicates[] = $serial;
                    } else {
                        $allSerials[] = $serial;
                    }
                }
            }
            if (!empty($serialDuplicates)) {
                return back()->with('error', 'Serial bị trùng giữa các vật tư: ' . implode(', ', array_unique($serialDuplicates)))->withInput();
            }

            // Lấy vật tư chính (vật tư đầu tiên)
            $primaryMaterial = $materialsData[0];
            $materialId = $primaryMaterial['id'];
            $itemType = $primaryMaterial['type'] ?? 'material';

            // Cập nhật phiếu chuyển kho
            $warehouseTransfer->update([
                'transfer_code' => $request->transfer_code,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'material_id' => $materialId,
                'quantity' => $request->quantity,
                'transfer_date' => $request->transfer_date,
                'employee_id' => $request->employee_id,
                'status' => 'pending', // Luôn giữ trạng thái pending khi update
                'notes' => $request->notes,
            ]);

            // Ghi nhật ký cập nhật phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'warehouse_transfers',
                    'Cập nhật phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                    $oldData,
                    $warehouseTransfer->toArray()
                );
            }

            // Xóa chi tiết vật tư hiện có
            $warehouseTransfer->materials()->delete();

            // Tạo lại chi tiết vật tư
            foreach ($materialsData as $materialData) {
                $warehouseTransfer->materials()->create([
                    'material_id' => $materialData['id'],
                    'type' => $materialData['type'] ?? 'material',
                    'quantity' => $materialData['quantity'],
                    'serial_numbers' => $materialData['serial_numbers'] ?? null,
                    'notes' => $materialData['notes'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()
                ->route('warehouse-transfers.show', $warehouseTransfer)
                ->with('success', 'Cập nhật phiếu chuyển kho thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật phiếu chuyển kho')->withInput();
        }
    }

    /**
     * Cập nhật số lượng tồn kho
     * 
     * @param int $warehouseId ID của kho
     * @param int $materialId ID của vật tư/thành phẩm/hàng hóa
     * @param string $itemType Loại sản phẩm (material, product, good)
     * @param int $quantityChange Thay đổi số lượng (dương là tăng, âm là giảm)
     * @param string $note Ghi chú về việc thay đổi tồn kho
     * @return bool Kết quả cập nhật
     */
    private function updateWarehouseStock($warehouseId, $materialId, $itemType, $quantityChange, $note = '')
    {
        try {
            // Tìm bản ghi tồn kho
            $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $materialId)
                ->where('item_type', $itemType)
                ->first();

            if ($warehouseMaterial) {
                // Cập nhật số lượng tồn kho hiện có
                $newQuantity = $warehouseMaterial->quantity + $quantityChange;

                if ($newQuantity <= 0) {
                    // Nếu số lượng mới <= 0, xóa bản ghi tồn kho
                    $warehouseMaterial->delete();
                    Log::info("Đã xóa bản ghi tồn kho: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, ghi chú={$note}");
                } else {
                    // Cập nhật số lượng mới
                    $warehouseMaterial->quantity = $newQuantity;
                    $warehouseMaterial->save();
                    Log::info("Cập nhật tồn kho: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, thay đổi={$quantityChange}, mới={$warehouseMaterial->quantity}, ghi chú={$note}");
                }
            } else {
                // Tạo mới bản ghi tồn kho nếu chưa tồn tại
                if ($quantityChange > 0) {
                    WarehouseMaterial::create([
                        'warehouse_id' => $warehouseId,
                        'material_id' => $materialId,
                        'item_type' => $itemType,
                        'quantity' => $quantityChange,
                    ]);

                    Log::info("Tạo mới tồn kho: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, số lượng={$quantityChange}, ghi chú={$note}");
                } else {
                    Log::warning("Không thể giảm tồn kho không tồn tại: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, thay đổi={$quantityChange}");
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật tồn kho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseTransfer $warehouseTransfer)
    {
        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $warehouseTransfer->toArray();
        $warehouseTransferCode = $warehouseTransfer->transfer_code;

        try {
            $warehouseTransfer->delete();

            // Ghi nhật ký xóa phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'warehouse_transfers',  
                    'Xóa phiếu chuyển kho: ' . $warehouseTransferCode,
                    $oldData,
                    null
                );
            }

            return redirect()->route('warehouse-transfers.index')->with('success', 'Phiếu chuyển kho đã được xóa thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa phiếu chuyển kho');
        }
    }

    /**
     * Kiểm tra số lượng tồn kho của sản phẩm trong kho nguồn
     */
    public function checkInventory(Request $request)
    {
        try {
            $materialId = $request->input('material_id');
            $warehouseId = $request->input('warehouse_id');
            $itemType = $request->input('item_type', 'material');

            Log::info("API checkInventory được gọi với: materialId={$materialId}, warehouseId={$warehouseId}, itemType={$itemType}");

            if (!$materialId || !$warehouseId) {
                Log::warning("Thiếu thông tin sản phẩm hoặc kho: materialId={$materialId}, warehouseId={$warehouseId}");
                return response()->json(['error' => 'Thiếu thông tin sản phẩm hoặc kho'], 400);
            }

            // Kiểm tra xem bảng warehouse_materials có cột item_type không
            $hasItemTypeColumn = Schema::hasColumn('warehouse_materials', 'item_type');
            Log::info("Bảng warehouse_materials có cột item_type: " . ($hasItemTypeColumn ? 'Có' : 'Không'));

            // Truy vấn trực tiếp từ DB để debug
            $rawResults = DB::select("SELECT * FROM warehouse_materials WHERE warehouse_id = ? AND material_id = ?", [$warehouseId, $materialId]);
            Log::info("Raw query results: " . json_encode($rawResults));

            // Khởi tạo query
            $query = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $materialId);

            Log::info("Query ban đầu: warehouse_id={$warehouseId}, material_id={$materialId}");

            // Chỉ áp dụng điều kiện item_type nếu cột tồn tại
            if ($hasItemTypeColumn) {
                // Nếu loại là material, có thể trong DB là null hoặc material
                if ($itemType == 'material') {
                    $query->where(function ($q) {
                        $q->where('item_type', 'material')
                            ->orWhereNull('item_type');
                    });
                    Log::info("Áp dụng điều kiện: item_type='material' HOẶC item_type IS NULL");
                } else {
                    $query->where('item_type', $itemType);
                    Log::info("Áp dụng điều kiện: item_type='{$itemType}'");
                }
            }

            // Lấy SQL query để debug
            $querySql = $query->toSql();
            $queryBindings = $query->getBindings();
            Log::info("SQL Query: {$querySql}, Bindings: " . json_encode($queryBindings));

            // Lấy tổng số lượng
            $quantity = $query->sum('quantity');

            // Lấy danh sách các bản ghi tìm thấy để debug
            $records = $query->get();
            Log::info("Tìm thấy " . count($records) . " bản ghi tồn kho");
            foreach ($records as $index => $record) {
                Log::info("Bản ghi #{$index}: id={$record->id}, warehouse_id={$record->warehouse_id}, material_id={$record->material_id}, item_type={$record->item_type}, quantity={$record->quantity}");
            }

            // Lấy thông tin kho
            $warehouse = Warehouse::find($warehouseId);
            $warehouseName = $warehouse ? $warehouse->name : 'Không xác định';

            // Log để debug
            Log::info("Kết quả kiểm tra tồn kho: materialId={$materialId}, warehouseId={$warehouseId}, itemType={$itemType}, quantity={$quantity}, warehouseName={$warehouseName}");

            return response()->json([
                'quantity' => $quantity,
                'warehouse_name' => $warehouseName,
                'has_stock' => $quantity > 0,
                'records_count' => count($records)
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra tồn kho: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi kiểm tra tồn kho: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách serial tồn kho
     * 
     * Hàm này sẽ:
     * 1. Lấy danh sách serial đã nhập vào kho từ phiếu nhập kho
     * 2. Lấy danh sách serial đã chuyển đi từ phiếu chuyển kho
     * 3. Tính toán danh sách serial còn tồn kho (đã nhập - đã chuyển)
     * 4. Tính toán số lượng thiết bị không có serial (tổng tồn kho - số lượng có serial)
     * 
     * Logic xử lý serial:
     * - Nếu số lượng Serial chọn < số lượng chuyển, các thiết bị còn lại sẽ được hiểu là Serial trống.
     * - Nếu số lượng Serial chọn > số lượng chuyển, hệ thống hiển thị thông báo lỗi.
     * - Nếu số lượng thiết bị không có Serial tồn kho không đủ, hệ thống hiển thị thông báo lỗi.
     */
    public function getAvailableSerials(Request $request)
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $materialId = $request->input('material_id');
            $itemType = $request->input('item_type', 'material');

            Log::info('Đang lấy serial với params:', [
                'warehouse_id' => $warehouseId,
                'material_id' => $materialId,
                'item_type' => $itemType
            ]);

            // Lấy danh sách serial đã nhập từ phiếu nhập kho - BỎ ĐIỀU KIỆN item_type
            // để lấy tất cả serial bất kể loại sản phẩm
            $importQuery = DB::table('inventory_import_materials')
                ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                ->where('inventory_imports.warehouse_id', $warehouseId)
                ->where('inventory_import_materials.material_id', $materialId)
                ->whereNotNull('inventory_import_materials.serial_numbers')
                ->select('inventory_import_materials.serial_numbers', 'inventory_import_materials.item_type');
                
            Log::info('Import Query:', ['sql' => $importQuery->toSql(), 'bindings' => $importQuery->getBindings()]);
            
            $importedItems = $importQuery->get();
            Log::info('Raw Import Items:', ['count' => count($importedItems), 'data' => $importedItems]);
            
            $importedSerials = [];
            foreach ($importedItems as $item) {
                if (!empty($item->serial_numbers)) {
                    try {
                        Log::info('Processing serial data:', [
                            'item_type' => $item->item_type,
                            'raw_data' => $item->serial_numbers
                        ]);
                        
                        // Thử nhiều cách decode khác nhau
                        $serials = null;
                        
                        // Cách 1: Decode trực tiếp
                        $serials = json_decode($item->serial_numbers, true);
                        
                        // Cách 2: Nếu cách 1 không được, thử xử lý chuỗi
                        if (is_null($serials) || !is_array($serials)) {
                            // Loại bỏ dấu ngoặc vuông nếu có
                            $cleaned = trim($item->serial_numbers, '[]');
                            // Tách chuỗi thành mảng
                            $serials = preg_split('/[,"\s]+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);
                            $serials = array_map(function($s) { return trim($s, '"\''); }, $serials);
                        }
                        
                        // Thêm vào danh sách
                        if (is_array($serials)) {
                            Log::info('Decoded serials:', ['serials' => $serials]);
                            $importedSerials = array_merge($importedSerials, $serials);
                        }
                    } catch (\Exception $e) {
                        Log::error('Lỗi khi decode serial: ' . $e->getMessage(), ['serial_data' => $item->serial_numbers]);
                    }
                }
            }
            
            // Loại bỏ trùng lặp
            $importedSerials = array_unique($importedSerials);
            
            Log::info('Imported serials:', ['count' => count($importedSerials), 'serials' => $importedSerials]);

            // Lấy danh sách serial đã chuyển đi từ phiếu chuyển kho - BỎ ĐIỀU KIỆN type
            // để lấy tất cả serial đã chuyển đi bất kể loại sản phẩm
            $transferQuery = DB::table('warehouse_transfer_materials')
                ->join('warehouse_transfers', 'warehouse_transfers.id', '=', 'warehouse_transfer_materials.warehouse_transfer_id')
                ->where('warehouse_transfers.source_warehouse_id', $warehouseId)
                ->where('warehouse_transfer_materials.material_id', $materialId)
                ->whereNotNull('warehouse_transfer_materials.serial_numbers')
                ->select('warehouse_transfer_materials.serial_numbers', 'warehouse_transfer_materials.type');
                
            Log::info('Transfer Query:', ['sql' => $transferQuery->toSql(), 'bindings' => $transferQuery->getBindings()]);
            
            $transferredItems = $transferQuery->get();
            Log::info('Raw Transferred Items:', ['count' => count($transferredItems), 'data' => $transferredItems]);
            
            $transferredSerials = [];
            foreach ($transferredItems as $item) {
                if (!empty($item->serial_numbers)) {
                    try {
                        $serials = json_decode($item->serial_numbers, true);
                        if (is_array($serials)) {
                            $transferredSerials = array_merge($transferredSerials, $serials);
                        }
                    } catch (\Exception $e) {
                        Log::error('Lỗi khi decode serial: ' . $e->getMessage(), ['serial_data' => $item->serial_numbers]);
                    }
                }
            }
            
            // Loại bỏ trùng lặp
            $transferredSerials = array_unique($transferredSerials);
            
            Log::info('Transferred serials:', ['count' => count($transferredSerials), 'serials' => $transferredSerials]);

            // Lấy số lượng tồn kho
            $totalStock = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $materialId)
                ->where('item_type', $itemType)
                ->sum('quantity');
                
            Log::info('Total stock:', ['quantity' => $totalStock]);

            // Lấy danh sách serial còn tồn kho (đã nhập - đã chuyển)
            $availableSerials = array_values(array_diff($importedSerials, $transferredSerials));
            
            // Số lượng tồn kho không có serial = tổng tồn kho - số lượng có serial
            $nonSerialStock = $totalStock - count($availableSerials);
            $nonSerialStock = max(0, $nonSerialStock);

            Log::info('Kết quả cuối cùng:', [
                'available_serials' => $availableSerials,
                'non_serial_stock' => $nonSerialStock
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'available_serials' => $availableSerials,
                    'non_serial_stock' => $nonSerialStock
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách serial: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra dữ liệu serial trong database
     */
    public function checkSerialData(Request $request)
    {
        try {
            $warehouseId = $request->input('warehouse_id', 1);
            $materialId = $request->input('material_id', 1);
            
            // Truy vấn trực tiếp vào bảng inventory_import_materials
            $importData = DB::table('inventory_import_materials')
                ->select('id', 'inventory_import_id', 'material_id', 'item_type', 'serial_numbers')
                ->where('material_id', $materialId)
                ->whereNotNull('serial_numbers')
                ->whereRaw("serial_numbers != '[]'")
                ->whereRaw("serial_numbers != ''")
                ->get();
                
            // Truy vấn trực tiếp vào bảng inventory_imports
            $importIds = $importData->pluck('inventory_import_id')->toArray();
            $imports = [];
            if (!empty($importIds)) {
                $imports = DB::table('inventory_imports')
                    ->select('id', 'warehouse_id', 'import_code')
                    ->whereIn('id', $importIds)
                    ->get();
            }
            
            // Kết quả chi tiết
            $results = [];
            foreach ($importData as $item) {
                $import = $imports->firstWhere('id', $item->inventory_import_id);
                $warehouse = $import ? $import->warehouse_id : null;
                
                $results[] = [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'item_type' => $item->item_type,
                    'warehouse_id' => $warehouse,
                    'serial_numbers_raw' => $item->serial_numbers,
                    'serial_numbers_decoded' => json_decode($item->serial_numbers, true)
                ];
            }
            
            return response()->json([
                'success' => true,
                'count' => count($results),
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate mã phiếu chuyển kho tự động
     */
    public function generateCode()
    {
        try {
            // Format: CT + YYMMDD + số random 4 chữ số
            $today = now();
            $prefix = 'CT' . $today->format('ymd');
            
            // Random số 4 chữ số và kiểm tra trùng
            do {
                $randomNumber = mt_rand(1, 9999); // Random từ 1-9999
                $newCode = $prefix . str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
            } while (WarehouseTransfer::where('transfer_code', $newCode)->exists());
            
            return response()->json([
                'success' => true,
                'code' => $newCode
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo mã phiếu chuyển kho: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duyệt phiếu chuyển kho
     */
    public function approve(WarehouseTransfer $warehouseTransfer)
    {
        try {
            DB::beginTransaction();

            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $warehouseTransfer->toArray();

            // Kiểm tra tồn kho trước khi duyệt
            $inventoryErrors = [];
            foreach ($warehouseTransfer->materials as $material) {
                // Lấy thông tin tồn kho hiện tại
                $currentStock = WarehouseMaterial::where('warehouse_id', $warehouseTransfer->source_warehouse_id)
                    ->where('material_id', $material->material_id)
                    ->where('item_type', $material->type ?? 'material')
                    ->sum('quantity');
                
                // Lấy tên item
                $itemName = '';
                if ($material->type == 'material' && $material->material) {
                    $itemName = $material->material->code . ' - ' . $material->material->name;
                } elseif ($material->type == 'product' && $material->product) {
                    $itemName = $material->product->code . ' - ' . $material->product->name;
                } elseif ($material->type == 'good' && $material->good) {
                    $itemName = $material->good->code . ' - ' . $material->good->name;
                } else {
                    $itemName = "Mã: {$material->material_id}";
                }
                
                // Nếu số lượng chuyển lớn hơn tồn kho hiện tại
                if ($material->quantity > $currentStock) {
                    $inventoryErrors[] = "Số lượng chuyển ({$material->quantity}) của {$itemName} vượt quá tồn kho hiện tại ({$currentStock})";
                }
            }
            
            // Nếu có lỗi tồn kho, không cho phép duyệt
            if (!empty($inventoryErrors)) {
                DB::rollBack();
                return back()->with('error', 'Không thể duyệt phiếu chuyển kho:<br>' . implode('<br>', $inventoryErrors));
            }

            // Cập nhật trạng thái thành 'completed'
            $warehouseTransfer->update([
                'status' => 'completed'
            ]);

            // Cập nhật tồn kho cho từng vật tư
            foreach ($warehouseTransfer->materials as $material) {
                // Giảm số lượng tồn kho ở kho nguồn
                $this->updateWarehouseStock(
                    $warehouseTransfer->source_warehouse_id,
                    $material->material_id,
                    $material->type ?? 'material',
                    -$material->quantity,
                    "Giảm tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                );

                // Tăng số lượng tồn kho ở kho đích
                $this->updateWarehouseStock(
                    $warehouseTransfer->destination_warehouse_id,
                    $material->material_id,
                    $material->type ?? 'material',
                    $material->quantity,
                    "Tăng tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                );

                // Lấy thông tin vật tư để tạo nhật ký
                $itemModel = null;
                if ($material->type == 'material') {
                    $itemModel = Material::find($material->material_id);
                } elseif ($material->type == 'product') {
                    $itemModel = Product::find($material->material_id);
                } elseif ($material->type == 'good') {
                    $itemModel = Good::find($material->material_id);
                }

                // Tạo nhật ký chuyển kho
                if ($itemModel) {
                    ChangeLogHelper::chuyenKho(
                        $itemModel->code,
                        $itemModel->name,
                        $material->quantity,
                        $warehouseTransfer->transfer_code,
                        "Chuyển từ {$warehouseTransfer->source_warehouse->name} sang {$warehouseTransfer->destination_warehouse->name}",
                        [
                            'source_warehouse_id' => $warehouseTransfer->source_warehouse_id,
                            'source_warehouse_name' => $warehouseTransfer->source_warehouse->name,
                            'destination_warehouse_id' => $warehouseTransfer->destination_warehouse_id,
                            'destination_warehouse_name' => $warehouseTransfer->destination_warehouse->name,
                        ],
                        $warehouseTransfer->notes
                    );
                }
            }

            // Ghi nhật ký duyệt phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'warehouse_transfers',
                    'Duyệt phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                    $oldData,
                    $warehouseTransfer->toArray()
                );
            }

            DB::commit();
            return redirect()->route('warehouse-transfers.index')->with('success', 'Phiếu chuyển kho đã được duyệt thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi duyệt phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi duyệt phiếu chuyển kho');
        }
    }
}
