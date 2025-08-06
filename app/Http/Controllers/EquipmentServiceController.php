<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\DispatchReturn;
use App\Models\DispatchReplacement;
use App\Models\Warranty;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Helpers\ChangeLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EquipmentServiceController extends Controller
{
    /**
     * Xử lý yêu cầu thu hồi thiết bị dự phòng/bảo hành
     */
    public function returnEquipment(Request $request)
    {
        // Validate request
        $validatedData = $request->validate([
            'equipment_id' => 'required|exists:dispatch_items,id',
            'equipment_serial' => 'required|string', // Thêm validation cho serial
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'rental_id' => 'nullable|exists:rentals,id',
            'project_id' => 'nullable|exists:projects,id',
        ], [
            'equipment_id.required' => 'Thiết bị không được để trống',
            'equipment_id.exists' => 'Thiết bị không tồn tại',
            'equipment_serial.required' => 'Serial thiết bị không được để trống',
            'warehouse_id.required' => 'Kho không được để trống',
            'warehouse_id.exists' => 'Kho không tồn tại',
            'reason.required' => 'Lý do thu hồi không được để trống',
            'rental_id.exists' => 'Phiếu thuê không tồn tại',
            'project_id.exists' => 'Dự án không tồn tại',
        ]);

        DB::beginTransaction();
        try {
            // Lấy thông tin thiết bị
            $dispatchItem = DispatchItem::with('dispatch')->findOrFail($validatedData['equipment_id']);
            $warehouse = Warehouse::findOrFail($validatedData['warehouse_id']);
            $serialToReturn = $validatedData['equipment_serial'];

            // Kiểm tra thiết bị phải thuộc loại dự phòng/bảo hành
            if ($dispatchItem->category !== 'backup') {
                return redirect()->back()
                    ->with('error', 'Chỉ có thể thu hồi thiết bị dự phòng/bảo hành.');
            }

            // Kiểm tra serial có tồn tại trong item không
            $serialNumbers = is_array($dispatchItem->serial_numbers) ? $dispatchItem->serial_numbers : [];
            if (!in_array($serialToReturn, $serialNumbers)) {
                return redirect()->back()
                    ->with('error', 'Serial thiết bị không tồn tại trong item này.');
            }

            // Kiểm tra serial chưa được thu hồi trước đó
            $isAlreadyReturned = DispatchReturn::where('dispatch_item_id', $dispatchItem->id)
                ->where('serial_number', $serialToReturn)
                ->exists();
            if ($isAlreadyReturned) {
                return redirect()->back()
                    ->with('error', 'Serial này đã được thu hồi trước đó.');
            }

            // Tạo phiếu thu hồi
            $dispatchReturn = DispatchReturn::create([
                'return_code' => DispatchReturn::generateReturnCode(),
                'dispatch_item_id' => $dispatchItem->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => Auth::id() ?? 1,
                'return_date' => Carbon::now(),
                'reason_type' => 'return',
                'reason' => $validatedData['reason'],
                'condition' => 'good', // Mặc định hoạt động tốt
                'status' => 'completed',
                'serial_number' => $serialToReturn, // Lưu serial được thu hồi
            ]);

            // Cập nhật số lượng trong kho (chỉ cập nhật 1 serial)
            $item = null;
            switch ($dispatchItem->item_type) {
                case 'material':
                    $item = Material::findOrFail($dispatchItem->item_id);
                    $this->updateWarehouseQuantity('material', $item->id, $warehouse->id, 1); // Chỉ cập nhật 1
                    break;
                case 'product':
                    $item = Product::findOrFail($dispatchItem->item_id);
                    $this->updateWarehouseQuantity('product', $item->id, $warehouse->id, 1); // Chỉ cập nhật 1
                    break;
                case 'good':
                    $item = Good::findOrFail($dispatchItem->item_id);
                    $this->updateWarehouseQuantity('good', $item->id, $warehouse->id, 1); // Chỉ cập nhật 1
                    break;
            }

            // Cập nhật serial_numbers trong dispatch_item (loại bỏ serial đã thu hồi)
            $updatedSerials = array_values(array_diff($serialNumbers, [$serialToReturn]));
            $dispatchItem->serial_numbers = $updatedSerials;
            
            // Cập nhật quantity để đồng bộ với số lượng serial còn lại
            $dispatchItem->quantity = count($updatedSerials);
            
            // Cập nhật ghi chú trong dispatch_item
            $dispatchItem->notes = ($dispatchItem->notes ? $dispatchItem->notes . "\n" : "") . 
                "Serial {$serialToReturn} đã thu hồi ngày " . Carbon::now()->format('d/m/Y H:i') . 
                ". Lý do: " . $validatedData['reason'] . 
                ". Kho thu hồi: " . $warehouse->name;
            $dispatchItem->save();

            // Lưu nhật ký thay đổi vật tư và thiết bị
            try {
                $description = 'Thu hồi thiết bị dự phòng/bảo hành';
                $detailedInfo = [
                    'dispatch_return_id' => $dispatchReturn->id,
                    'dispatch_item_id' => $dispatchItem->id,
                    'dispatch_id' => $dispatchItem->dispatch->id,
                    'dispatch_code' => $dispatchItem->dispatch->dispatch_code,
                    'dispatch_type' => $dispatchItem->dispatch->dispatch_type,
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                    'warehouse_code' => $warehouse->code,
                    'reason' => $validatedData['reason'],
                    'return_date' => $dispatchReturn->return_date->toDateTimeString(),
                    'returned_by' => Auth::id(),
                    'serial_number' => $serialToReturn, // Thêm serial number vào nhật ký
                ];

                // Xác định loại item để hiển thị chính xác
                $itemTypeLabel = '';
                switch ($dispatchItem->item_type) {
                    case 'material':
                        $itemTypeLabel = 'vật tư';
                        break;
                    case 'product':
                        $itemTypeLabel = 'thành phẩm';
                        break;
                    case 'good':
                        $itemTypeLabel = 'hàng hóa';
                        break;
                    default:
                        $itemTypeLabel = 'hàng hóa';
                        break;
                }

                if ($dispatchItem->dispatch->dispatch_type === 'project') {
                    $project = \App\Models\Project::find($dispatchItem->dispatch->project_id);
                    $description = "Thu hồi {$itemTypeLabel} từ dự án: " . ($project ? $project->project_name : 'Không xác định');
                    $detailedInfo['project_id'] = $dispatchItem->dispatch->project_id;
                    $detailedInfo['project_name'] = $project ? $project->project_name : null;
                    $detailedInfo['project_code'] = $project ? $project->project_code : null;
                } elseif ($dispatchItem->dispatch->dispatch_type === 'rental') {
                    $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$dispatchItem->dispatch->dispatch_note}%")
                        ->orWhere('rental_code', 'LIKE', "%{$dispatchItem->dispatch->project_receiver}%")
                        ->first();
                    $description = "Thu hồi {$itemTypeLabel} từ phiếu cho thuê: " . ($rental ? $rental->rental_name : 'Không xác định');
                    $detailedInfo['rental_id'] = $rental ? $rental->id : null;
                    $detailedInfo['rental_name'] = $rental ? $rental->rental_name : null;
                    $detailedInfo['rental_code'] = $rental ? $rental->rental_code : null;
                }

                // Lấy thông tin serial numbers nếu có
                if ($dispatchItem->serial_numbers && is_array($dispatchItem->serial_numbers)) {
                    $detailedInfo['serial_numbers'] = $dispatchItem->serial_numbers;
                }

                ChangeLogHelper::thuHoi(
                    $item->code,
                    $item->name,
                    $dispatchItem->quantity,
                    $dispatchReturn->return_code,
                    $description,
                    $detailedInfo,
                    "Thu hồi {$itemTypeLabel} - Lý do: " . $validatedData['reason']
                );

                Log::info('Thu hồi thiết bị dự phòng - Đã lưu nhật ký', [
                    'return_code' => $dispatchReturn->return_code,
                    'item_code' => $item->code,
                    'dispatch_type' => $dispatchItem->dispatch->dispatch_type
                ]);

            } catch (\Exception $logException) {
                Log::error('Lỗi khi lưu nhật ký thu hồi thiết bị dự phòng', [
                    'return_code' => $dispatchReturn->return_code,
                    'error' => $logException->getMessage(),
                    'trace' => $logException->getTraceAsString()
                ]);
                // Không throw exception để không ảnh hưởng đến quá trình thu hồi chính
            }

            DB::commit();

            // Redirect về trang hiện tại với thông báo thành công
            return redirect()->back()
                        ->with('success', 'Thiết bị dự phòng đã được thu hồi thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning equipment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi thu hồi thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý yêu cầu bảo hành/thay thế thiết bị
     */
    public function replaceEquipment(Request $request)
    {
        // Validate request
        $validatedData = $request->validate([
            'equipment_id' => 'required|exists:dispatch_items,id',
            'equipment_serial' => 'required|string',
            'replacement_device_id' => 'required|string', // Thay đổi từ exists sang string
            'replacement_serial' => 'required|string',
            'reason' => 'required|string',
            'rental_id' => 'nullable|exists:rentals,id',
        ], [
            'equipment_id.required' => 'Thiết bị cần thay thế không được để trống',
            'equipment_id.exists' => 'Thiết bị cần thay thế không tồn tại',
            'equipment_serial.required' => 'Serial thiết bị cần thay thế không được để trống',
            'replacement_device_id.required' => 'Thiết bị thay thế không được để trống',
            'replacement_serial.required' => 'Serial thiết bị thay thế không được để trống',
            'reason.required' => 'Lý do thay thế không được để trống',
            'rental_id.exists' => 'Phiếu thuê không tồn tại',
        ]);

        DB::beginTransaction();
        try {
            // Xử lý format mới: replacement_device_id có thể là "itemId:serialNumber"
            $replacementDeviceId = $validatedData['replacement_device_id'];
            if (strpos($replacementDeviceId, ':') !== false) {
                $replacementDeviceId = explode(':', $replacementDeviceId)[0];
            }
            
            // Validate replacement_device_id sau khi parse
            if (!DispatchItem::find($replacementDeviceId)) {
                return redirect()->back()->with('error', 'Thiết bị thay thế không tồn tại.');
            }

            // Lấy thông tin thiết bị
            $originalItem = DispatchItem::with('dispatch')->findOrFail($validatedData['equipment_id']);
            $replacementItem = DispatchItem::with('dispatch')->findOrFail($replacementDeviceId);

            // Kiểm tra thiết bị thay thế phải có cùng mã thiết bị
            $originalCode = $this->getItemCode($originalItem);
            $replacementCode = $this->getItemCode($replacementItem);
            if ($originalCode !== $replacementCode) {
                return redirect()->back()
                    ->with('error', 'Thiết bị thay thế phải có cùng mã thiết bị với thiết bị cần thay thế.');
            }

            // Kiểm tra serial tồn tại trong từng item
            $originalSerials = is_array($originalItem->serial_numbers) ? $originalItem->serial_numbers : [];
            $replacementSerials = is_array($replacementItem->serial_numbers) ? $replacementItem->serial_numbers : [];
            
            if (!in_array($validatedData['equipment_serial'], $originalSerials)) {
                return redirect()->back()->with('error', 'Serial thiết bị hợp đồng không hợp lệ.');
            }
            if (!in_array($validatedData['replacement_serial'], $replacementSerials)) {
                return redirect()->back()->with('error', 'Serial thiết bị dự phòng không hợp lệ.');
            }
            
            // Chỉ xóa serial được chọn khỏi mỗi item
            $originalSerials = array_values(array_diff($originalSerials, [$validatedData['equipment_serial']]));
            $replacementSerials = array_values(array_diff($replacementSerials, [$validatedData['replacement_serial']]));

            // Thêm serial mới vào từng item (swap chỉ 1 serial)
            $originalSerials[] = $validatedData['replacement_serial']; // Thiết bị hợp đồng nhận serial dự phòng
            $replacementSerials[] = $validatedData['equipment_serial']; // Thiết bị dự phòng nhận serial hợp đồng

            // Cập nhật serial_numbers cho từng item
            $originalItem->serial_numbers = $originalSerials;
            $replacementItem->serial_numbers = $replacementSerials;

            // Không đổi category, chỉ cập nhật ghi chú cho serial được swap
            $originalItem->notes = ($originalItem->notes ? $originalItem->notes . "\n" : "") .
                "Serial {$validatedData['equipment_serial']} đã được thay thế bằng serial {$validatedData['replacement_serial']} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];
            $replacementItem->notes = ($replacementItem->notes ? $replacementItem->notes . "\n" : "") .
                "Serial {$validatedData['replacement_serial']} đã thay thế cho serial {$validatedData['equipment_serial']} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];

            $originalItem->save();
            $replacementItem->save();

            // Tạo phiếu thay thế
            $replacement = DispatchReplacement::create([
                'replacement_code' => DispatchReplacement::generateReplacementCode(),
                'original_dispatch_item_id' => $originalItem->id,
                'replacement_dispatch_item_id' => $replacementItem->id,
                'original_serial' => $validatedData['equipment_serial'],
                'replacement_serial' => $validatedData['replacement_serial'],
                'user_id' => Auth::id() ?? 1,
                'replacement_date' => Carbon::now(),
                'reason' => $validatedData['reason'],
                'status' => 'completed',
            ]);

            // Debug log để kiểm tra
            Log::info('Replacement created:', [
                'replacement_id' => $replacement->id,
                'original_item_id' => $originalItem->id,
                'original_serial' => $validatedData['equipment_serial'],
                'replacement_item_id' => $replacementItem->id,
                'replacement_serial' => $validatedData['replacement_serial'],
                'created_at' => $replacement->created_at
            ]);

            // Tạo phiếu bảo hành nếu chưa có
            // Kiểm tra bảo hành theo logic chia sẻ dự án/phiếu cho thuê
            if ($originalItem->dispatch->dispatch_type === 'rental') {
                // Cho phiếu cho thuê: kiểm tra bảo hành chia sẻ trong cùng rental
                $warranty = Warranty::whereHas('dispatch', function ($query) use ($originalItem) {
                    $query->where('project_id', $originalItem->dispatch->project_id)
                        ->where('dispatch_type', 'rental');
                })->first();
            } elseif ($originalItem->dispatch->project_id) {
                // Cho dự án thường: kiểm tra bảo hành chia sẻ trong cùng project
                $warranty = Warranty::whereHas('dispatch', function ($query) use ($originalItem) {
                    $query->where('project_id', $originalItem->dispatch->project_id)
                        ->where('dispatch_type', '!=', 'rental');
                })->first();
            } else {
                // Fallback: kiểm tra theo dispatch_item_id cho trường hợp không có project
                $warranty = Warranty::where('dispatch_item_id', $originalItem->id)->first();
            }
            
            if (!$warranty) {
                $warranty = $this->createWarranty($originalItem, $replacementItem, $validatedData['reason']);
            } else {
                // Cập nhật ghi chú trong phiếu bảo hành đã có
                $warranty->notes = ($warranty->notes ? $warranty->notes . "\n" : "") . 
                    "Thiết bị được thay thế ngày " . Carbon::now()->format('d/m/Y H:i') . 
                    ". Lý do: " . $validatedData['reason'] . 
                    ". Thiết bị thay thế: " . $this->getItemCode($replacementItem);
                $warranty->save();
            }

            // Lưu nhật ký thay đổi cho việc thu hồi vật tư khi thay thế
            try {
                // Lấy thông tin vật tư gốc
                $originalItemInfo = $this->getItemInfo($originalItem);
                $replacementItemInfo = $this->getItemInfo($replacementItem);
                
                // Xác định loại item để hiển thị chính xác
                $itemTypeLabel = '';
                switch ($originalItem->item_type) {
                    case 'material':
                        $itemTypeLabel = 'vật tư';
                        break;
                    case 'product':
                        $itemTypeLabel = 'thành phẩm';
                        break;
                    case 'good':
                        $itemTypeLabel = 'hàng hóa';
                        break;
                    default:
                        $itemTypeLabel = 'vật tư';
                        break;
                }
                
                // Xác định loại dự án/phiếu cho thuê
                $projectType = '';
                $projectName = '';
                $detailedInfo = [
                    'replacement_code' => $replacement->replacement_code,
                    'reason' => $validatedData['reason'],
                    'original_item_code' => $originalItemInfo['code'],
                    'original_item_name' => $originalItemInfo['name'],
                    'replacement_item_code' => $replacementItemInfo['code'],
                    'replacement_item_name' => $replacementItemInfo['name'],
                    'original_serial' => $validatedData['equipment_serial'],
                    'replacement_serial' => $validatedData['replacement_serial'],
                ];
                
                if ($originalItem->dispatch->dispatch_type === 'rental') {
                    $projectType = 'phiếu cho thuê';
                    $projectName = $originalItem->dispatch->dispatch_note ?? 'Không xác định';
                } else {
                    $projectType = 'dự án';
                    $projectName = $originalItem->dispatch->project_name ?? 'Không xác định';
                }

                ChangeLogHelper::thuHoi(
                    $originalItemInfo['code'],
                    $originalItemInfo['name'],
                    $originalItem->quantity,
                    $replacement->replacement_code,
                    "Thay thế {$itemTypeLabel} trong {$projectType}",
                    $detailedInfo,
                    "Thay thế {$itemTypeLabel} {$originalItemInfo['code']} - {$originalItemInfo['name']} (Serial: {$validatedData['equipment_serial']}) bằng {$itemTypeLabel} {$replacementItemInfo['code']} - {$replacementItemInfo['name']} (Serial: {$validatedData['replacement_serial']}) trong {$projectType} {$projectName}"
                );
            } catch (\Exception $e) {
                Log::error('Error logging equipment replacement: ' . $e->getMessage());
            }

            DB::commit();

            // Clear cache và session để đảm bảo dữ liệu được refresh
            Cache::flush();
            session()->forget(['backup_items_cache', 'contract_items_cache']);
            
            // Force refresh database connection
            DB::disconnect();
            DB::reconnect();

            // Redirect với timestamp để tránh cache
            $redirectUrl = '';
            if ($originalItem->dispatch->dispatch_type === 'rental') {
                // Tìm rental ID từ dispatch
                $rental = null;
                if ($originalItem->dispatch->dispatch_note) {
                    $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$originalItem->dispatch->dispatch_note}%")->first();
                }
                if (!$rental && $originalItem->dispatch->project_receiver) {
                    $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$originalItem->dispatch->project_receiver}%")->first();
                }
                if ($rental) {
                    $redirectUrl = route('rentals.show', $rental->id) . '?t=' . time() . '&refresh=1';
                } else {
                    $redirectUrl = route('rentals.index') . '?t=' . time() . '&refresh=1';
                }
            } else {
                $redirectUrl = route('projects.show', $originalItem->dispatch->project_id) . '?t=' . time() . '&refresh=1';
            }

            return redirect($redirectUrl)
                ->with('success', 'Thiết bị đã được thay thế thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error replacing equipment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi thay thế thiết bị: ' . $e->getMessage());
        }
    }

    /**
     * Lấy lịch sử thay đổi của thiết bị
     */
    public function getEquipmentHistory($id)
    {
        try {
            // Lấy thông tin thiết bị
            $dispatchItem = DispatchItem::with(['dispatch', 'warranties', 'material', 'product', 'good'])->findOrFail($id);
            
            // Lấy thông tin dự án/project và nhân viên phụ trách
            $project = null;
            $rental = null;
            $responsibleEmployee = null;
            
            // Kiểm tra xem có rental_id trong query parameter không
            $rentalId = request()->query('rental_id');
            
            if ($rentalId) {
                // Nếu có rental_id, sử dụng trực tiếp
                $rental = \App\Models\Rental::find($rentalId);
                if ($rental && $rental->employee_id) {
                    $responsibleEmployee = \App\Models\Employee::find($rental->employee_id);
                }
            } else {
                // Fallback: Tìm rental theo logic cũ
                if ($dispatchItem->dispatch) {
                    if ($dispatchItem->dispatch->project) {
                        // Trường hợp project
                        $project = $dispatchItem->dispatch->project;
                        $responsibleEmployee = $project->employee; // Nhân viên phụ trách dự án
                    } elseif ($dispatchItem->dispatch->dispatch_type === 'rental') {
                        // Trường hợp rental - thử nhiều cách để tìm rental
                        $rental = null;
                        
                        // Cách 1: Tìm theo dispatch_note
                        if ($dispatchItem->dispatch->dispatch_note) {
                            $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$dispatchItem->dispatch->dispatch_note}%")->first();
                        }
                        
                        // Cách 2: Tìm theo project_receiver nếu cách 1 không tìm thấy
                        if (!$rental && $dispatchItem->dispatch->project_receiver) {
                            $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$dispatchItem->dispatch->project_receiver}%")->first();
                        }
                        
                        // Cách 3: Tìm tất cả rentals và so sánh
                        if (!$rental) {
                            $allRentals = \App\Models\Rental::all();
                            foreach ($allRentals as $r) {
                                if (strpos($dispatchItem->dispatch->dispatch_note ?? '', $r->rental_code) !== false ||
                                    strpos($dispatchItem->dispatch->project_receiver ?? '', $r->rental_code) !== false) {
                                    $rental = $r;
                                    break;
                                }
                            }
                        }
                        
                        if ($rental && $rental->employee_id) {
                            $responsibleEmployee = \App\Models\Employee::find($rental->employee_id);
                        }
                    }
                }
            }
            
            // Lấy thông tin thay thế
            $replacements = DispatchReplacement::with([
                'user', 
                'replacementDispatchItem.material', 
                'replacementDispatchItem.product', 
                'replacementDispatchItem.good',
                'replacementDispatchItem.dispatch',
                'originalDispatchItem.material',
                'originalDispatchItem.product',
                'originalDispatchItem.good',
                'originalDispatchItem.dispatch'
            ])
                ->where('original_dispatch_item_id', $dispatchItem->id)
                ->orWhere('replacement_dispatch_item_id', $dispatchItem->id)
                ->get()
                ->map(function ($replacement) use ($responsibleEmployee) {
                    // Lấy thông tin nhân viên thực hiện
                    $employeeName = 'Không xác định';
                    
                    // Ưu tiên: Nhân viên phụ trách dự án/rental
                    if ($responsibleEmployee) {
                        $employeeName = $responsibleEmployee->name;
                    }
                    // Fallback: User name (nếu không phải customer)
                    elseif ($replacement->user && $replacement->user->role !== 'customer') {
                        $employeeName = $replacement->user->name;
                    }
                    
                    // Thêm thông tin nhân viên vào replacement
                    $replacement->employee_name = $employeeName;
                    
                    return $replacement;
                });
            
            return response()->json([
                'success' => true,
                'dispatchItem' => $dispatchItem,
                'replacements' => $replacements,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting equipment history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy lịch sử thay đổi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Cập nhật số lượng trong kho
     */
    private function updateWarehouseQuantity($itemType, $itemId, $warehouseId, $quantity)
    {
        // Kiểm tra xem đã có bản ghi trong warehouse_materials chưa
        $warehouseMaterial = DB::table('warehouse_materials')
            ->where('item_type', $itemType)
            ->where('material_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($warehouseMaterial) {
            // Cập nhật số lượng nếu đã có
            DB::table('warehouse_materials')
                ->where('id', $warehouseMaterial->id)
                ->update([
                    'quantity' => $warehouseMaterial->quantity + $quantity,
                    'updated_at' => now()
                ]);
        } else {
            // Tạo bản ghi mới nếu chưa có
            DB::table('warehouse_materials')
                ->insert([
                    'warehouse_id' => $warehouseId,
                    'item_type' => $itemType,
                    'material_id' => $itemId,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Helper: Tạo phiếu bảo hành mới
     */
    private function createWarranty($originalItem, $replacementItem, $reason)
    {
        // Lấy thông tin thiết bị
        $item = null;
        switch ($originalItem->item_type) {
            case 'material':
                $item = Material::findOrFail($originalItem->item_id);
                break;
            case 'product':
                $item = Product::findOrFail($originalItem->item_id);
                break;
            case 'good':
                $item = Good::findOrFail($originalItem->item_id);
                break;
        }

        // Tính toán thời gian bảo hành
        $warrantyPeriodMonths = 12; // Mặc định 12 tháng
        $warrantyStartDate = $originalItem->dispatch->dispatch_date;
        $warrantyEndDate = $warrantyStartDate->copy()->addMonths($warrantyPeriodMonths);

        // Sinh mã bảo hành duy nhất
        $baseCode = 'BH' . date('Ymd') . str_pad($originalItem->id, 4, '0', STR_PAD_LEFT);
        $warrantyCode = $baseCode;
        $suffix = 1;
        while (\App\Models\Warranty::where('warranty_code', $warrantyCode)->exists()) {
            $warrantyCode = $baseCode . '-' . $suffix++;
        }

        // Tạo phiếu bảo hành
        $warranty = Warranty::create([
            'warranty_code' => $warrantyCode,
            'dispatch_id' => $originalItem->dispatch_id,
            'dispatch_item_id' => $originalItem->id,
            'item_type' => $originalItem->item_type,
            'item_id' => $originalItem->item_id,
            'serial_number' => is_array($originalItem->serial_numbers) ? implode(', ', $originalItem->serial_numbers) : null,
            'customer_name' => $originalItem->dispatch->project_receiver,
            'customer_phone' => null,
            'customer_email' => null,
            'customer_address' => null,
            'project_name' => $originalItem->dispatch->project_receiver,
            'purchase_date' => $originalItem->dispatch->dispatch_date,
            'warranty_start_date' => $warrantyStartDate,
            'warranty_end_date' => $warrantyEndDate,
            'warranty_period_months' => $warrantyPeriodMonths,
            'warranty_type' => 'standard',
            'status' => 'active',
            'warranty_terms' => "Bảo hành tiêu chuẩn cho {$item->name}",
            'notes' => "Bảo hành tạo từ yêu cầu thay thế ngày " . Carbon::now()->format('d/m/Y H:i') . 
                    ". Lý do: {$reason}" . 
                    ". Thiết bị thay thế: " . $this->getItemCode($replacementItem),
            'created_by' => Auth::id() ?? 1,
            'activated_at' => now(),
        ]);

        return $warranty;
    }

    /**
     * Helper: Lấy mã thiết bị
     */
    private function getItemCode($dispatchItem)
    {
        $itemCode = 'N/A';

        switch ($dispatchItem->item_type) {
            case 'material':
                if ($dispatchItem->material) {
                    $itemCode = $dispatchItem->material->code;
                }
                break;
            case 'product':
                if ($dispatchItem->product) {
                    $itemCode = $dispatchItem->product->code;
                }
                break;
            case 'good':
                if ($dispatchItem->good) {
                    $itemCode = $dispatchItem->good->code;
                }
                break;
        }

        return $itemCode;
    }

    /**
     * Helper: Lấy thông tin chi tiết vật tư từ dispatch item
     */
    private function getItemInfo($dispatchItem)
    {
        $itemInfo = [
            'code' => 'N/A',
            'name' => 'N/A'
        ];

        switch ($dispatchItem->item_type) {
            case 'material':
                if ($dispatchItem->material) {
                    $itemInfo['code'] = $dispatchItem->material->code;
                    $itemInfo['name'] = $dispatchItem->material->name;
                }
                break;
            case 'product':
                if ($dispatchItem->product) {
                    $itemInfo['code'] = $dispatchItem->product->code;
                    $itemInfo['name'] = $dispatchItem->product->name;
                }
                break;
            case 'good':
                if ($dispatchItem->good) {
                    $itemInfo['code'] = $dispatchItem->good->code;
                    $itemInfo['name'] = $dispatchItem->good->name;
                }
                break;
        }

        return $itemInfo;
    }

    /**
     * Lấy danh sách thiết bị dự phòng cho một dự án
     */
    public function getBackupItemsForProject($projectId)
    {
        try {
            $dispatches = Dispatch::where('dispatch_type', 'project')
                ->where('project_id', $projectId)
                ->whereIn('status', ['approved', 'completed'])
                ->get();
            
            $backupItems = collect();
            foreach ($dispatches as $dispatch) {
                $items = $dispatch->items()
                    ->where('category', 'backup')
                    ->with(['material', 'product', 'good'])
                    ->get()
                    ->map(function ($item) {
                        // Lấy danh sách serial đã được sử dụng làm replacement
                        $replacementSerials = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                            ->pluck('replacement_serial')
                            ->toArray();
                        
                        // Thêm thông tin replacement_serials vào item
                        $item->replacement_serials = $replacementSerials;
                        
                        return $item;
                    });
                $backupItems = $backupItems->concat($items);
            }
            
            return response()->json([
                'success' => true,
                'backupItems' => $backupItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting backup items for project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thiết bị dự phòng: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy danh sách thiết bị dự phòng cho một phiếu cho thuê
     */
    public function getBackupItemsForRental($rentalId)
    {
        try {
            $rental = \App\Models\Rental::findOrFail($rentalId);
            
            $dispatches = Dispatch::where('dispatch_type', 'rental')
                ->whereIn('status', ['approved', 'completed'])
                ->where(function($query) use ($rental) {
                    $query->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                        ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                })
                ->get();
            
            $backupItems = collect();
            foreach ($dispatches as $dispatch) {
                $items = $dispatch->items()
                    ->where('category', 'backup')
                    ->with(['material', 'product', 'good'])
                    ->get()
                    ->map(function ($item) {
                        // Lấy danh sách serial đã được sử dụng làm replacement
                        $replacementSerials = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                            ->pluck('replacement_serial')
                            ->toArray();
                        
                        // Thêm thông tin replacement_serials vào item
                        $item->replacement_serials = $replacementSerials;
                        
                        return $item;
                    });
                $backupItems = $backupItems->concat($items);
            }
            
            return response()->json([
                'success' => true,
                'backupItems' => $backupItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting backup items for rental: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thiết bị dự phòng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy danh sách serial của 1 dispatch item
     */
    public function getItemSerials($id)
    {
        $item = \App\Models\DispatchItem::find($id);
        if (!$item) {
            return response()->json(['serials' => []]);
        }
        $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
        return response()->json(['serials' => $serials]);
    }
} 