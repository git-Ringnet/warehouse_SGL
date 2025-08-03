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
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'rental_id' => 'nullable|exists:rentals,id',
        ], [
            'equipment_id.required' => 'Thiết bị không được để trống',
            'equipment_id.exists' => 'Thiết bị không tồn tại',
            'warehouse_id.required' => 'Kho không được để trống',
            'warehouse_id.exists' => 'Kho không tồn tại',
            'reason.required' => 'Lý do thu hồi không được để trống',
            'rental_id.exists' => 'Phiếu thuê không tồn tại',
        ]);

        DB::beginTransaction();
        try {
            // Lấy thông tin thiết bị
            $dispatchItem = DispatchItem::with('dispatch')->findOrFail($validatedData['equipment_id']);
            $warehouse = Warehouse::findOrFail($validatedData['warehouse_id']);

            // Kiểm tra thiết bị phải thuộc loại dự phòng/bảo hành
            if ($dispatchItem->category !== 'backup') {
                return redirect()->back()
                    ->with('error', 'Chỉ có thể thu hồi thiết bị dự phòng/bảo hành.');
            }

            // Kiểm tra thiết bị chưa được sử dụng
            $isUsed = DispatchReplacement::where('replacement_dispatch_item_id', $dispatchItem->id)->exists();
            if ($isUsed) {
                return redirect()->back()
                    ->with('error', 'Không thể thu hồi thiết bị đã được sử dụng.');
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
            ]);

            // Cập nhật số lượng trong kho (không cần phiếu nhập)
            $item = null;
            switch ($dispatchItem->item_type) {
                case 'material':
                    $item = Material::findOrFail($dispatchItem->item_id);
                    $this->updateWarehouseQuantity('material', $item->id, $warehouse->id, $dispatchItem->quantity);
                    break;
                case 'product':
                    $item = Product::findOrFail($dispatchItem->item_id);
                    $this->updateWarehouseQuantity('product', $item->id, $warehouse->id, $dispatchItem->quantity);
                    break;
                case 'good':
                    $item = Good::findOrFail($dispatchItem->item_id);
                    $this->updateWarehouseQuantity('good', $item->id, $warehouse->id, $dispatchItem->quantity);
                    break;
            }

            // Cập nhật ghi chú trong dispatch_item
            $dispatchItem->notes = ($dispatchItem->notes ? $dispatchItem->notes . "\n" : "") . 
                "Đã thu hồi ngày " . Carbon::now()->format('d/m/Y H:i') . 
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

            // Redirect với thông báo thành công
            if ($dispatchItem->dispatch->dispatch_type == 'project') {
                return redirect()->route('projects.show', $dispatchItem->dispatch->project_id)
                    ->with('success', 'Thiết bị dự phòng đã được thu hồi thành công.');
            } elseif ($dispatchItem->dispatch->dispatch_type == 'rental') {
                // Sử dụng rental_id từ form nếu có
                if (isset($validatedData['rental_id'])) {
                    return redirect()->route('rentals.show', $validatedData['rental_id'])
                        ->with('success', 'Thiết bị dự phòng đã được thu hồi thành công.');
                }
                
                // Fallback: Tìm rental ID từ dispatch nếu không có rental_id
                Log::info('Debug - Dispatch info for rental return redirect:', [
                    'dispatch_id' => $dispatchItem->dispatch->id,
                    'dispatch_note' => $dispatchItem->dispatch->dispatch_note,
                    'project_receiver' => $dispatchItem->dispatch->project_receiver,
                    'dispatch_type' => $dispatchItem->dispatch->dispatch_type
                ]);
                
                // Tìm rental ID từ dispatch - thử nhiều cách
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
                
                Log::info('Debug - Found rental for return:', [
                    'rental_id' => $rental ? $rental->id : null,
                    'rental_code' => $rental ? $rental->rental_code : null,
                    'rental_name' => $rental ? $rental->rental_name : null
                ]);
                
                if ($rental) {
                    return redirect()->route('rentals.show', $rental->id)
                        ->with('success', 'Thiết bị dự phòng đã được thu hồi thành công.');
                } else {
                    return redirect()->back()->with('success', 'Thiết bị dự phòng đã được thu hồi thành công.');
                }
            } else {
                return redirect()->back()->with('success', 'Thiết bị dự phòng đã được thu hồi thành công.');
            }
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
            'replacement_device_id' => 'required|exists:dispatch_items,id',
            'reason' => 'required|string',
            'rental_id' => 'nullable|exists:rentals,id',
        ], [
            'equipment_id.required' => 'Thiết bị cần thay thế không được để trống',
            'equipment_id.exists' => 'Thiết bị cần thay thế không tồn tại',
            'replacement_device_id.required' => 'Thiết bị thay thế không được để trống',
            'replacement_device_id.exists' => 'Thiết bị thay thế không tồn tại',
            'reason.required' => 'Lý do thay thế không được để trống',
            'rental_id.exists' => 'Phiếu thuê không tồn tại',
        ]);

        DB::beginTransaction();
        try {
            // Lấy thông tin thiết bị
            $originalItem = DispatchItem::with('dispatch')->findOrFail($validatedData['equipment_id']);
            $replacementItem = DispatchItem::with('dispatch')->findOrFail($validatedData['replacement_device_id']);

            // Kiểm tra thiết bị thay thế phải có cùng mã thiết bị
            $originalCode = $this->getItemCode($originalItem);
            $replacementCode = $this->getItemCode($replacementItem);
            
            if ($originalCode !== $replacementCode) {
                return redirect()->back()
                    ->with('error', 'Thiết bị thay thế phải có cùng mã thiết bị với thiết bị cần thay thế.');
            }

            // Kiểm tra thiết bị thay thế phải có trạng thái "Chưa sử dụng"
            $isReplacementUsed = DispatchReplacement::where('replacement_dispatch_item_id', $replacementItem->id)->exists();
            if ($isReplacementUsed) {
                return redirect()->back()
                    ->with('error', 'Thiết bị thay thế đã được sử dụng, không thể sử dụng lại.');
            }

            // Tạo phiếu thay thế
            $replacement = DispatchReplacement::create([
                'replacement_code' => DispatchReplacement::generateReplacementCode(),
                'original_dispatch_item_id' => $originalItem->id,
                'replacement_dispatch_item_id' => $replacementItem->id,
                'user_id' => Auth::id() ?? 1,
                'replacement_date' => Carbon::now(),
                'reason' => $validatedData['reason'],
                'status' => 'completed',
            ]);

            // Cập nhật ghi chú trong original_dispatch_item
            $originalItem->notes = ($originalItem->notes ? $originalItem->notes . "\n" : "") . 
                "Đã thay thế ngày " . Carbon::now()->format('d/m/Y H:i') . 
                ". Lý do: " . $validatedData['reason'] . 
                ". Thiết bị thay thế: " . $this->getItemCode($replacementItem);
            $originalItem->save();

            // Cập nhật ghi chú trong replacement_dispatch_item
            $replacementItem->notes = ($replacementItem->notes ? $replacementItem->notes . "\n" : "") . 
                "Được sử dụng để thay thế ngày " . Carbon::now()->format('d/m/Y H:i') . 
                " cho thiết bị: " . $this->getItemCode($originalItem);
            $replacementItem->save();

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
                    'replacement_item_code' => $replacementItemInfo['code']
                ];

                if ($originalItem->dispatch->dispatch_type === 'project') {
                    $project = \App\Models\Project::find($originalItem->dispatch->project_id);
                    $projectType = 'dự án';
                    $projectName = $project ? $project->project_name : 'Không xác định';
                    $detailedInfo['project_id'] = $project ? $project->id : null;
                    $detailedInfo['project_name'] = $projectName;
                    $detailedInfo['project_code'] = $project ? $project->project_code : null;
                } elseif ($originalItem->dispatch->dispatch_type === 'rental') {
                    $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$originalItem->dispatch->dispatch_note}%")
                        ->orWhere('rental_code', 'LIKE', "%{$originalItem->dispatch->project_receiver}%")
                        ->first();
                    $projectType = 'phiếu cho thuê';
                    $projectName = $rental ? $rental->rental_name : 'Không xác định';
                    $detailedInfo['rental_id'] = $rental ? $rental->id : null;
                    $detailedInfo['rental_name'] = $projectName;
                    $detailedInfo['rental_code'] = $rental ? $rental->rental_code : null;
                }

                $description = "Thu hồi {$itemTypeLabel} từ {$projectType}: {$projectName}";

                ChangeLogHelper::thuHoi(
                    $originalItemInfo['code'],
                    $originalItemInfo['name'],
                    $originalItem->quantity,
                    $replacement->replacement_code,
                    $description,
                    $detailedInfo,
                    "Thu hồi {$itemTypeLabel} - Lý do thay thế: " . $validatedData['reason']
                );

                Log::info('Thu hồi vật tư khi thay thế - Đã lưu nhật ký', [
                    'replacement_code' => $replacement->replacement_code,
                    'original_item_code' => $originalItemInfo['code'],
                    'dispatch_type' => $originalItem->dispatch->dispatch_type
                ]);

            } catch (\Exception $logException) {
                Log::error('Lỗi khi lưu nhật ký thu hồi vật tư khi thay thế', [
                    'replacement_code' => $replacement->replacement_code,
                    'error' => $logException->getMessage(),
                    'trace' => $logException->getTraceAsString()
                ]);
                // Không throw exception để không ảnh hưởng đến quá trình thay thế chính
            }

            DB::commit();

            // Redirect với thông báo thành công
            if ($originalItem->dispatch->dispatch_type == 'project') {
                return redirect()->route('projects.show', $originalItem->dispatch->project_id)
                    ->with('success', 'Thiết bị đã được thay thế thành công.');
            } elseif ($originalItem->dispatch->dispatch_type == 'rental') {
                // Sử dụng rental_id từ form nếu có
                if (isset($validatedData['rental_id'])) {
                    return redirect()->route('rentals.show', $validatedData['rental_id'])
                        ->with('success', 'Thiết bị đã được thay thế thành công.');
                }
                
                // Fallback: Tìm rental ID từ dispatch nếu không có rental_id
                Log::info('Debug - Dispatch info for rental redirect:', [
                    'dispatch_id' => $originalItem->dispatch->id,
                    'dispatch_note' => $originalItem->dispatch->dispatch_note,
                    'project_receiver' => $originalItem->dispatch->project_receiver,
                    'dispatch_type' => $originalItem->dispatch->dispatch_type
                ]);
                
                // Tìm rental ID từ dispatch - thử nhiều cách
                $rental = null;
                
                // Cách 1: Tìm theo dispatch_note
                if ($originalItem->dispatch->dispatch_note) {
                    $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$originalItem->dispatch->dispatch_note}%")->first();
                }
                
                // Cách 2: Tìm theo project_receiver nếu cách 1 không tìm thấy
                if (!$rental && $originalItem->dispatch->project_receiver) {
                    $rental = \App\Models\Rental::where('rental_code', 'LIKE', "%{$originalItem->dispatch->project_receiver}%")->first();
                }
                
                // Cách 3: Tìm tất cả rentals và so sánh
                if (!$rental) {
                    $allRentals = \App\Models\Rental::all();
                    foreach ($allRentals as $r) {
                        if (strpos($originalItem->dispatch->dispatch_note ?? '', $r->rental_code) !== false ||
                            strpos($originalItem->dispatch->project_receiver ?? '', $r->rental_code) !== false) {
                            $rental = $r;
                            break;
                        }
                    }
                }
                
                Log::info('Debug - Found rental:', [
                    'rental_id' => $rental ? $rental->id : null,
                    'rental_code' => $rental ? $rental->rental_code : null,
                    'rental_name' => $rental ? $rental->rental_name : null
                ]);
                
                if ($rental) {
                    return redirect()->route('rentals.show', $rental->id)
                        ->with('success', 'Thiết bị đã được thay thế thành công.');
                } else {
                    return redirect()->back()->with('success', 'Thiết bị đã được thay thế thành công.');
                }
            } else {
                return redirect()->back()->with('success', 'Thiết bị đã được thay thế thành công.');
            }
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

        // Tạo phiếu bảo hành
        $warranty = Warranty::create([
            'warranty_code' => Warranty::generateWarrantyCode(),
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
                        // Kiểm tra xem thiết bị đã được sử dụng làm thiết bị thay thế chưa
                        $isUsed = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)->exists();
                        
                        // Thêm thông tin is_used vào item
                        $item->is_used = $isUsed;
                        
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
                        // Kiểm tra xem thiết bị đã được sử dụng làm thiết bị thay thế chưa
                        $isUsed = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)->exists();
                        
                        // Thêm thông tin is_used vào item
                        $item->is_used = $isUsed;
                        
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
} 