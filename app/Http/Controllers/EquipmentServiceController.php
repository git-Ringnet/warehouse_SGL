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
     * Lấy ID người dùng đang đăng nhập từ guard mặc định hoặc guard 'employee'
     */
    private function getAuthenticatedActorId(): ?int
    {
        $userId = Auth::id();
        if ($userId) {
            return (int) $userId;
        }
        try {
            $employeeId = Auth::guard('employee')->id();
            return $employeeId ? (int) $employeeId : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lấy user_id hợp lệ để lưu vào các bảng có FK tới `users`.
     * Ưu tiên guard mặc định; nếu không có thì fallback về user hệ thống (ID=1).
     */
    private function getAuditUserId(): int
    {
        // Always prefer web guard (users table). Verify existence in users table to avoid FK violations.
        try {
            $webUserId = Auth::guard('web')->id();
            if ($webUserId && DB::table('users')->where('id', $webUserId)->exists()) {
                return (int) $webUserId;
            }
        } catch (\Throwable $e) {
            // ignore guard errors
        }

        // Fallback to default guard but also validate against users table
        $defaultId = Auth::id();
        if ($defaultId && DB::table('users')->where('id', $defaultId)->exists()) {
            return (int) $defaultId;
        }

        // Final fallback: system user id = 1 (must exist in users table)
        return 1;
    }
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

            // Kiểm tra thiết bị phải thuộc loại hợp đồng hoặc dự phòng/bảo hành
            if (!in_array($dispatchItem->category, ['backup', 'contract'])) {
                return redirect()->back()
                    ->with('error', 'Chỉ có thể thu hồi thiết bị theo hợp đồng hoặc dự phòng/bảo hành.');
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
                // dispatch_returns.user_id FK -> users.id
                'user_id' => $this->getAuditUserId(),
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
                    'returned_by' => $this->getAuthenticatedActorId(),
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
                    1, // Số lượng thực tế được thu hồi (1 serial)
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

            // Kiểm tra serial tồn tại trong từng item (so sánh sau khi trim)
            $originalSerialsRaw = is_array($originalItem->serial_numbers) ? $originalItem->serial_numbers : [];
            $replacementSerialsRaw = is_array($replacementItem->serial_numbers) ? $replacementItem->serial_numbers : [];

            $originalSerials = array_map(function ($s) { return trim((string) $s); }, $originalSerialsRaw);
            $replacementSerials = array_map(function ($s) { return trim((string) $s); }, $replacementSerialsRaw);

            $requestedOriginalSerialInput = trim((string) $validatedData['equipment_serial']);
            $requestedReplacementSerialInput = trim((string) $validatedData['replacement_serial']);

            // Hỗ trợ nhận serial đã đổi tên từ UI: map về serial gốc nếu cần
            $resolveToOriginal = function (int $dispatchId, int $itemId, string $itemType, string $inputSerial, array $serialsInItem): string {
                // Nếu serial đã có trong item thì giữ nguyên
                if (in_array($inputSerial, $serialsInItem, true)) {
                    return $inputSerial;
                }
                // Thử tìm trong device_codes (ưu tiên theo dispatch hiện tại)
                $dc = DB::table('device_codes')
                    ->where('dispatch_id', $dispatchId)
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->where('serial_main', $inputSerial)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dc && !empty($dc->old_serial)) {
                    return trim((string) $dc->old_serial);
                }
                // Fallback: tìm theo item, không ràng buộc dispatch
                $dc = DB::table('device_codes')
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->where('serial_main', $inputSerial)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dc && !empty($dc->old_serial)) {
                    return trim((string) $dc->old_serial);
                }
                // Fallback cuối: tìm toàn cục theo serial_main (không ràng buộc item)
                $dc = DB::table('device_codes')
                    ->where('serial_main', $inputSerial)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dc && !empty($dc->old_serial)) {
                    return trim((string) $dc->old_serial);
                }
                // Không tìm thấy mapping, trả về input
                return $inputSerial;
            };

            $requestedOriginalSerial = $resolveToOriginal($originalItem->dispatch_id, $originalItem->item_id, $originalItem->item_type, $requestedOriginalSerialInput, $originalSerials);
            $requestedReplacementSerial = $resolveToOriginal($replacementItem->dispatch_id, $replacementItem->item_id, $replacementItem->item_type, $requestedReplacementSerialInput, $replacementSerials);

            if (!in_array($requestedOriginalSerial, $originalSerials, true)) {
                return redirect()->back()->with('error', 'Serial thiết bị hợp đồng không hợp lệ.');
            }
            if (!in_array($requestedReplacementSerial, $replacementSerials, true)) {
                return redirect()->back()->with('error', 'Serial thiết bị dự phòng không hợp lệ.');
            }

            // Không cho phép dùng lại serial dự phòng đã sử dụng trước đó
            // 1) Kiểm tra theo item cụ thể
            $isReplacementSerialUsed = DispatchReplacement::where('replacement_dispatch_item_id', $replacementItem->id)
                ->where('replacement_serial', $requestedReplacementSerial)
                ->exists();
            if ($isReplacementSerialUsed) {
                return redirect()->back()->with('error', 'Serial thiết bị dự phòng đã được sử dụng để thay thế trước đó. Vui lòng chọn serial khác.');
            }

            // 2) Kiểm tra trong cùng phạm vi (project/rental) bất kể item nào
            $isSerialUsedInScope = DispatchReplacement::where(function($q) use ($requestedReplacementSerial) {
                    $q->where('replacement_serial', $requestedReplacementSerial)
                      ->orWhere('original_serial', $requestedReplacementSerial);
                })
                ->whereHas('replacementDispatchItem.dispatch', function ($q) use ($originalItem) {
                    if ($originalItem->dispatch->dispatch_type === 'rental') {
                        $rentalCode = $originalItem->dispatch->project_receiver; // thường chứa rental_code
                        $projectId = $originalItem->dispatch->project_id; // đôi khi được dùng làm rental_id
                        $q->where('dispatch_type', 'rental')
                          ->where(function ($qq) use ($rentalCode, $projectId) {
                              $qq->where('project_id', $projectId)
                                 ->orWhere('dispatch_note', 'LIKE', "%{$rentalCode}%")
                                 ->orWhere('project_receiver', 'LIKE', "%{$rentalCode}%");
                          });
                    } else {
                        $q->where('dispatch_type', 'project')
                          ->where('project_id', $originalItem->dispatch->project_id);
                    }
                })
                ->exists();
            if ($isSerialUsedInScope) {
                return redirect()->back()->with('error', 'Serial này đã được sử dụng trong phạm vi dự án/phiếu tương ứng. Vui lòng chọn serial khác.');
            }
            
            // Chỉ xóa serial được chọn khỏi mỗi item
            $originalSerials = array_values(array_diff($originalSerials, [$requestedOriginalSerial]));
            $replacementSerials = array_values(array_diff($replacementSerials, [$requestedReplacementSerial]));

            // Thêm serial mới vào từng item (swap chỉ 1 serial)
            $originalSerials[] = $requestedReplacementSerial; // Thiết bị hợp đồng nhận serial dự phòng
            $replacementSerials[] = $requestedOriginalSerial; // Thiết bị dự phòng nhận serial hợp đồng

            // Cập nhật serial_numbers cho từng item
            $originalItem->serial_numbers = $originalSerials;
            $replacementItem->serial_numbers = $replacementSerials;

            // Không đổi category, chỉ cập nhật ghi chú cho serial được swap
            $originalItem->notes = ($originalItem->notes ? $originalItem->notes . "\n" : "") .
                "Serial {$requestedOriginalSerial} đã được thay thế bằng serial {$requestedReplacementSerial} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];
            $replacementItem->notes = ($replacementItem->notes ? $replacementItem->notes . "\n" : "") .
                "Serial {$requestedReplacementSerial} đã thay thế cho serial {$requestedOriginalSerial} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];

            $originalItem->save();
            $replacementItem->save();

            // Tạo phiếu thay thế
            $replacement = DispatchReplacement::create([
                'replacement_code' => DispatchReplacement::generateReplacementCode(),
                'original_dispatch_item_id' => $originalItem->id,
                'replacement_dispatch_item_id' => $replacementItem->id,
                'original_serial' => $requestedOriginalSerial,
                'replacement_serial' => $requestedReplacementSerial,
                // dispatch_replacements.user_id FK -> users.id, nên dùng user web; nếu không có, fallback 1
                'user_id' => $this->getAuditUserId(),
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
                // Cho phiếu cho thuê: ưu tiên bảo hành cấp dự án trong cùng rental
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($query) use ($originalItem) {
                        $query->where('project_id', $originalItem->dispatch->project_id)
                            ->where('dispatch_type', 'rental');
                    })->first();
            } elseif ($originalItem->dispatch->project_id) {
                // Cho dự án thường: ưu tiên bảo hành cấp dự án trong cùng project
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($query) use ($originalItem) {
                        $query->where('project_id', $originalItem->dispatch->project_id)
                            ->where('dispatch_type', '!=', 'rental');
                    })->first();
            } else {
                // Fallback: kiểm tra theo dispatch_item_id cho trường hợp không có project
                $warranty = Warranty::where('item_type', 'project')
                    ->where('dispatch_item_id', $originalItem->id)
                    ->first();
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

            // Đã loại bỏ việc lưu nhật ký thay đổi khi thực hiện Bảo hành/Thay thế theo yêu cầu

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
                // Tìm rental ID từ dispatch - sử dụng logic tương tự getEquipmentHistory
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
                
                if ($rental) {
                    $redirectUrl = route('rentals.show', $rental->id) . '?t=' . time() . '&refresh=1';
                } else {
                    // Fallback: redirect về trang trước đó với thông báo
                    $redirectUrl = redirect()->back()->getTargetUrl();
                    if (empty($redirectUrl) || strpos($redirectUrl, 'rentals.index') !== false) {
                        $redirectUrl = route('rentals.index') . '?t=' . time() . '&refresh=1';
                    }
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
                'employee',
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

                    // Ưu tiên: Nhân viên đang đăng nhập thực hiện thao tác (ghi trong replacement.user_id)
                    if ($replacement->employee) {
                        $employeeName = $replacement->employee->name;
                    }
                    // Fallback: User model (nếu ứng dụng dùng bảng users)
                    elseif ($replacement->user && $replacement->user->role !== 'customer') {
                        $employeeName = $replacement->user->name;
                    }
                    // Cuối cùng: Nhân viên phụ trách của dự án/rental
                    elseif ($responsibleEmployee) {
                        $employeeName = $responsibleEmployee->name;
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

        // Tạo phiếu bảo hành cấp dự án để tránh tạo trùng BH item-level
        $warranty = Warranty::create([
            'warranty_code' => $warrantyCode,
            'dispatch_id' => $originalItem->dispatch_id,
            'dispatch_item_id' => $originalItem->id, // tham chiếu item đầu tiên (giữ tương thích)
            'item_type' => 'project',
            'item_id' => $originalItem->dispatch->project_id ?? 0,
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
            'warranty_terms' => isset($item) ? "Bảo hành tiêu chuẩn cho {$item->name}" : 'Bảo hành tiêu chuẩn',
            'notes' => "Bảo hành tạo từ yêu cầu thay thế ngày " . Carbon::now()->format('d/m/Y H:i') . 
                    ". Lý do: {$reason}" . 
                    ". Thiết bị thay thế: " . $this->getItemCode($replacementItem),
            // warranties.created_by không FK users, nhưng ta vẫn ghi nhận user web nếu có
            'created_by' => $this->getAuditUserId(),
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
            
            // Thu thập toàn bộ serial đã được dùng làm replacement trong phạm vi project
            $usedSerialsGlobal = DispatchReplacement::whereHas('replacementDispatchItem.dispatch', function($q) use ($projectId) {
                $q->where('dispatch_type', 'project')
                  ->where('project_id', $projectId)
                  ->whereIn('status', ['approved', 'completed']);
            })->get(['replacement_serial','original_serial'])
              ->flatMap(function ($r) { return [trim((string)$r->replacement_serial), trim((string)$r->original_serial)]; })
              ->filter()
              ->unique()
              ->values()
              ->toArray();
            
            $backupItems = collect();
            foreach ($dispatches as $dispatch) {
                $items = $dispatch->items()
                    ->where('category', 'backup')
                    ->with(['material', 'product', 'good'])
                    ->get()
                    ->map(function ($item) use ($usedSerialsGlobal, $dispatch) {
                        // Lấy danh sách serial đã được sử dụng làm replacement
                        $replacementSerials = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                            ->pluck('replacement_serial')
                            ->toArray();
                        
                        // Thêm thông tin replacement_serials vào item
                        $item->replacement_serials = array_map(function ($s) { return trim((string) $s); }, $replacementSerials);
                        $item->used_serials_global = $usedSerialsGlobal; // cung cấp thêm danh sách đã dùng trong phạm vi project
                        
                        // Lọc danh sách serial_numbers để chỉ giữ serial chưa sử dụng
                        $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
                        $serials = array_filter(array_map(function ($s) { return trim((string) $s); }, $serials));
                        
                        // Sử dụng SerialDisplayHelper để lấy serial đổi tên
                        $displaySerials = \App\Helpers\SerialDisplayHelper::getDisplaySerials(
                            $dispatch->id,
                            $item->item_id,
                            $item->item_type,
                            $serials
                        );
                        
                        $item->serial_numbers = array_values(array_filter($displaySerials, function ($serial) use ($item, $usedSerialsGlobal) {
                            return !in_array($serial, $item->replacement_serials, true) && !in_array($serial, $usedSerialsGlobal, true);
                        }));
                        
                        return $item;
                    })
                    ->filter(function ($item) {
                        // Chỉ giữ lại items có serial_numbers không rỗng
                        return !empty($item->serial_numbers);
                    });
                $backupItems = $backupItems->concat($items);
            }
            
            return response()->json([
                'success' => true,
                'backupItems' => $backupItems->values(), // Reset array keys
                'usedSerialsGlobal' => $usedSerialsGlobal,
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
            
            // Thu thập toàn bộ serial đã được dùng làm replacement trong phạm vi rental
            $usedSerialsGlobal = DispatchReplacement::whereHas('replacementDispatchItem.dispatch', function($q) use ($rental) {
                $q->where('dispatch_type', 'rental')
                  ->where(function($qq) use ($rental) {
                      $qq->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                         ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                  })
                  ->whereIn('status', ['approved', 'completed']);
            })->get(['replacement_serial','original_serial'])
              ->flatMap(function ($r) { return [trim((string)$r->replacement_serial), trim((string)$r->original_serial)]; })
              ->filter()
              ->unique()
              ->values()
              ->toArray();
            
            $backupItems = collect();
            foreach ($dispatches as $dispatch) {
                $items = $dispatch->items()
                    ->where('category', 'backup')
                    ->with(['material', 'product', 'good'])
                    ->get()
                    ->map(function ($item) use ($usedSerialsGlobal, $dispatch) {
                        // Lấy danh sách serial đã được sử dụng làm replacement
                        $replacementSerials = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                            ->pluck('replacement_serial')
                            ->toArray();
                        
                        // Thêm thông tin replacement_serials vào item
                        $item->replacement_serials = array_map(function ($s) { return trim((string) $s); }, $replacementSerials);
                        $item->used_serials_global = $usedSerialsGlobal; // cung cấp thêm danh sách đã dùng trong phạm vi rental
                        
                        // Lọc danh sách serial_numbers để chỉ giữ serial chưa sử dụng
                        $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
                        $serials = array_filter(array_map(function ($s) { return trim((string) $s); }, $serials));
                        
                        // Sử dụng SerialDisplayHelper để lấy serial đổi tên
                        $displaySerials = \App\Helpers\SerialDisplayHelper::getDisplaySerials(
                            $dispatch->id,
                            $item->item_id,
                            $item->item_type,
                            $serials
                        );
                        
                        $item->serial_numbers = array_values(array_filter($displaySerials, function ($serial) use ($item, $usedSerialsGlobal) {
                            return !in_array($serial, $item->replacement_serials, true) && !in_array($serial, $usedSerialsGlobal, true);
                        }));
                        
                        return $item;
                    })
                    ->filter(function ($item) {
                        // Chỉ giữ lại items có serial_numbers không rỗng
                        return !empty($item->serial_numbers);
                    });
                $backupItems = $backupItems->concat($items);
            }
            
            return response()->json([
                'success' => true,
                'backupItems' => $backupItems->values(), // Reset array keys
                'usedSerialsGlobal' => $usedSerialsGlobal,
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