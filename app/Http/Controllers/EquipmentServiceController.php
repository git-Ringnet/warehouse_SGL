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
use App\Helpers\SerialHelper;
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
            'equipment_serial' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'rental_id' => 'nullable|exists:rentals,id',
            'project_id' => 'nullable|exists:projects,id',
            'quantity' => 'nullable|numeric|min:0.001',
            'replacement_id' => 'nullable|exists:dispatch_replacements,id',
            'is_used' => 'nullable|boolean',
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
            $dispatchItem = DispatchItem::with(['dispatch', 'material', 'product', 'good'])->findOrFail($validatedData['equipment_id']);
            $warehouse = Warehouse::findOrFail($validatedData['warehouse_id']);
            $serialToReturn = $validatedData['equipment_serial'];
            $isMeasurement = ($serialToReturn === 'MEASUREMENT' || $serialToReturn === 'REPLACEMENT' || $this->isMeasurementItem($dispatchItem));
            $returnQty = $isMeasurement ? (float)($validatedData['quantity'] ?? 1) : 1;

            // Kiểm tra thiết bị phải thuộc loại hợp đồng hoặc dự phòng/bảo hành
            if (!in_array($dispatchItem->category, ['backup', 'contract', 'general'])) {
                return redirect()->back()
                    ->with('error', 'Chỉ có thể thu hồi thiết bị theo hợp đồng hoặc dự phòng/bảo hành.');
            }

            if ($isMeasurement) {
                // Tính tổng số lượng thực tế tại dự án (bao gồm cả phần chưa dùng, phần đang dùng thay thế cho món khác, và phần đã bị món khác thay thế)
                $incomingReplacementQty = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $dispatchItem->id)
                    ->selectRaw('SUM(quantity - COALESCE(replacement_returned_quantity, 0)) as total')->value('total') ?? 0;
                $outgoingReplacementQty = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
                    ->selectRaw('SUM(quantity - COALESCE(original_returned_quantity, 0)) as total')->value('total') ?? 0;
                $totalAtSite = (float)($dispatchItem->quantity + $incomingReplacementQty + $outgoingReplacementQty);

                if ($returnQty > $totalAtSite) {
                    return redirect()->back()->with('error', "Số lượng thu hồi ({$returnQty}) vượt quá thực tế tại dự án ({$totalAtSite}).");
                }

                $isUsedRecall = filter_var($request->input('is_used'), FILTER_VALIDATE_BOOLEAN);
                $remainingToReturn = $returnQty;

                if ($isUsedRecall) {
                    // Nếu thu hồi từ dòng "Đã sử dụng", ưu tiên trừ vào Outgoing replacements trước (để dòng bị thay thế biến mất)
                    $remainingToReturn = $this->reduceReplacementsForReturn($dispatchItem, $remainingToReturn, $validatedData['replacement_id'] ?? null, true);
                } else {
                    // Thu hồi thông thường từ Hợp đồng:
                    // 1. Nếu có replacement_id (thu hồi từ dòng "Hàng thay thế"), ưu tiên trừ vào bản ghi đó trước
                    if (!empty($validatedData['replacement_id'])) {
                        $remainingToReturn = $this->reduceReplacementsForReturn($dispatchItem, $remainingToReturn, $validatedData['replacement_id'], false);
                    }

                    // 2. Trừ vào hàng chưa dùng (dispatch_item->quantity)
                    if ($remainingToReturn > 0) {
                        $reduceFromQuantity = min((float)$dispatchItem->quantity, (float)$remainingToReturn);
                        $dispatchItem->quantity -= $reduceFromQuantity;
                        $remainingToReturn -= $reduceFromQuantity;
                    }

                    // 3. Cuối cùng mới trừ vào các replacements khác (Incoming -> Outgoing)
                    if ($remainingToReturn > 0) {
                        $remainingToReturn = $this->reduceReplacementsForReturn($dispatchItem, $remainingToReturn, null, false);
                    }
                }
                
                $dispatchItem->save();
            } else {
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

                // LẤY SERIAL THỰC TỪ device_codes
                $actualSerialToReturn = $serialToReturn;
                if (SerialHelper::isVirtualSerial($serialToReturn)) {
                    $deviceCode = DB::table('device_codes')
                        ->where('dispatch_id', $dispatchItem->dispatch_id)
                        ->where('item_id', $dispatchItem->item_id)
                        ->where('item_type', $dispatchItem->item_type)
                        ->where('old_serial', $serialToReturn)
                        ->first();
                    
                    if ($deviceCode && !empty($deviceCode->serial_main)) {
                        $actualSerialToReturn = trim($deviceCode->serial_main);
                    }
                } else {
                    $deviceCode = DB::table('device_codes')
                        ->where('dispatch_id', $dispatchItem->dispatch_id)
                        ->where('item_id', $dispatchItem->item_id)
                        ->where('item_type', $dispatchItem->item_type)
                        ->where('serial_main', $serialToReturn)
                        ->first();
                    if ($deviceCode) {
                        $actualSerialToReturn = trim($deviceCode->serial_main);
                    }
                }
            }

            // Tạo phiếu thu hồi
            $dispatchReturn = DispatchReturn::create([
                'return_code' => DispatchReturn::generateReturnCode(),
                'dispatch_item_id' => $dispatchItem->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $this->getAuditUserId(),
                'return_date' => Carbon::now(),
                'reason_type' => 'return',
                'reason' => $validatedData['reason'],
                'condition' => 'good',
                'status' => 'completed',
                'serial_number' => $serialToReturn,
                'quantity' => $returnQty,
            ]);

            // Cập nhật số lượng và serial trong kho
            if ($isMeasurement) {
                $this->updateWarehouseQuantity($dispatchItem->item_type, $dispatchItem->item_id, $warehouse->id, $returnQty);
            } else {
                $item = null;
                switch ($dispatchItem->item_type) {
                    case 'material':
                        $item = Material::findOrFail($dispatchItem->item_id);
                        $this->updateWarehouseQuantityWithSerial('material', $item->id, $warehouse->id, 1, $actualSerialToReturn);
                        break;
                    case 'product':
                        $item = Product::findOrFail($dispatchItem->item_id);
                        $this->updateWarehouseQuantityWithSerial('product', $item->id, $warehouse->id, 1, $actualSerialToReturn);
                        break;
                    case 'good':
                        $item = Good::findOrFail($dispatchItem->item_id);
                        $this->updateWarehouseQuantityWithSerial('good', $item->id, $warehouse->id, 1, $actualSerialToReturn);
                        break;
                }
            }

            if (!$isMeasurement) {
                $serials = is_array($dispatchItem->serial_numbers) ? $dispatchItem->serial_numbers : [];
                if (empty($serials)) {
                    $dispatchItem->quantity = max(0, $dispatchItem->quantity - $returnQty);
                } else {
                    $newSerials = array_values(array_diff($serials, [$serialToReturn]));
                    $dispatchItem->serial_numbers = $newSerials;
                    $dispatchItem->quantity = count($newSerials);
                }
            }
            
            // Cập nhật ghi chú trong dispatch_item
            $returnInfoText = $isMeasurement 
                ? "Số lượng {$returnQty}" 
                : (($actualSerialToReturn !== $serialToReturn) ? "Serial {$serialToReturn} (gốc: {$actualSerialToReturn})" : "Serial {$serialToReturn}");
                
            $dispatchItem->notes = ($dispatchItem->notes ? $dispatchItem->notes . "\n" : "") . 
                "{$returnInfoText} đã thu hồi ngày " . Carbon::now()->format('d/m/Y H:i') . 
                ". Lý do: " . $validatedData['reason'] . 
                ". Kho thu hồi: " . $warehouse->name;
            $dispatchItem->save();

            // Lưu nhật ký thay đổi
            try {
                $description = 'Thu hồi thiết bị dự phòng/bảo hành';
                $detailedInfo = [
                    'dispatch_return_id' => $dispatchReturn->id,
                    'dispatch_item_id' => $dispatchItem->id,
                    'dispatch_id' => $dispatchItem->dispatch->id,
                    'warehouse_id' => $warehouse->id,
                    'reason' => $validatedData['reason'],
                    'return_date' => $dispatchReturn->return_date->toDateTimeString(),
                    'serial_number' => $serialToReturn,
                ];

                ChangeLogHelper::thuHoi(
                    $dispatchItem->material->code ?? $dispatchItem->product->code ?? $dispatchItem->good->code ?? 'N/A',
                    $dispatchItem->material->name ?? $dispatchItem->product->name ?? $dispatchItem->good->name ?? 'N/A',
                    $returnQty,
                    $dispatchReturn->return_code,
                    $description,
                    $detailedInfo,
                    "Thu hồi - Lý do: " . $validatedData['reason']
                );
            } catch (\Exception $logException) {
                Log::error('Lỗi khi lưu nhật ký thu hồi: ' . $logException->getMessage());
            }

            DB::commit();
            return redirect()->back()->with('success', 'Thiết bị đã được thu hồi thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning equipment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi thu hồi thiết bị: ' . $e->getMessage());
        }
    }

    public function replaceEquipment(Request $request)
    {
        // Validate request
        $validatedData = $request->validate([
            'equipment_id' => 'required|exists:dispatch_items,id',
            'equipment_serial' => 'required|string',
            'replacement_device_id' => 'required|string',
            'replacement_serial' => 'required|string',
            'reason' => 'required|string',
            'rental_id' => 'nullable|exists:rentals,id',
            'quantity' => 'nullable|numeric|min:0.001',
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
            // Xử lý replacement_device_id có thể là "itemId:serialNumber"
            $replacementDeviceIdInput = $validatedData['replacement_device_id'];
            $replacementDeviceId = $replacementDeviceIdInput;
            if (strpos($replacementDeviceIdInput, ':') !== false) {
                $replacementDeviceId = explode(':', $replacementDeviceIdInput)[0];
            }
            
            // Validate replacement_device_id sau khi parse
            if (!DispatchItem::find($replacementDeviceId)) {
                return redirect()->back()->with('error', 'Thiết bị thay thế không tồn tại.');
            }

            // Lấy thông tin thiết bị
            $originalItem = DispatchItem::with(['dispatch', 'material', 'product', 'good'])->findOrFail($validatedData['equipment_id']);
            $replacementItem = DispatchItem::with(['dispatch', 'material', 'product', 'good'])->findOrFail($replacementDeviceId);

            // Kiểm tra mã thiết bị
            $originalCode = $this->getItemCode($originalItem);
            $replacementCode = $this->getItemCode($replacementItem);
            if ($originalCode !== $replacementCode) {
                return redirect()->back()->with('error', 'Thiết bị thay thế phải có cùng mã thiết bị với thiết bị cần thay thế.');
            }

            $requestedOriginalSerialInput = trim((string) $validatedData['equipment_serial']);
            $requestedReplacementSerialInput = trim((string) $validatedData['replacement_serial']);
            
            $isOrigMeasurement = ($requestedOriginalSerialInput === 'MEASUREMENT' || $this->isMeasurementItem($originalItem));
            $isReplMeasurement = ($requestedReplacementSerialInput === 'MEASUREMENT' || $this->isMeasurementItem($replacementItem));
            $replaceQty = ($isOrigMeasurement || $isReplMeasurement) ? (float)($validatedData['quantity'] ?? 1) : 1;

            if ($isOrigMeasurement || $isReplMeasurement) {
                // Logic cho hàng đo lường
                // Tính toán số lượng có thể thay thế hiện tại
                // Lưu ý: Đối với hàng dự phòng/thay thế, số lượng thực tế trong dự án = quantity (phần chưa dùng) + số lượng đang đóng vai trò thay thế cho hàng khác.
                $incomingQty = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $originalItem->id)->sum('quantity');
                $totalInProject = (float)($originalItem->quantity + $incomingQty);
                $alreadyReplaced = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $originalItem->id)->sum('quantity');

                $availableToReplace = max(0.0, $totalInProject - $alreadyReplaced);

                if ($originalItem->id === $replacementItem->id) {
                    return redirect()->back()->with('error', "Thiết bị không thể tự thay thế cho chính nó.");
                }

                if ($replaceQty > $availableToReplace) {
                    return redirect()->back()->with('error', "Số lượng thay thế ({$replaceQty}) vượt quá số lượng còn lại có thể thay thế trong hợp đồng ({$availableToReplace}).");
                }
                if ($replaceQty > $replacementItem->quantity) {
                    return redirect()->back()->with('error', "Số lượng thiết bị dự phòng ({$replacementItem->quantity}) không đủ để thay thế.");
                }

                $replacementItem->quantity -= $replaceQty;
                
                // Đảm bảo serial được ghi nhận đúng trong DB
                $requestedOriginalSerial = 'MEASUREMENT';
                $requestedReplacementSerial = 'MEASUREMENT';
            } else {
                // Logic cho thiết bị có serial
                $originalSerialsRaw = is_array($originalItem->serial_numbers) ? $originalItem->serial_numbers : [];
                $replacementSerialsRaw = is_array($replacementItem->serial_numbers) ? $replacementItem->serial_numbers : [];

                $originalSerialsReal = array_map(function ($s) { return trim((string) $s); }, $originalSerialsRaw);
                $replacementSerialsReal = array_map(function ($s) { return trim((string) $s); }, $replacementSerialsRaw);

                $originalSerials = SerialHelper::expandSerials($originalSerialsReal, (int)$originalItem->quantity);
                $replacementSerials = SerialHelper::expandSerials($replacementSerialsReal, (int)$replacementItem->quantity);

                $resolveToOriginal = function (int $dispatchId, int $itemId, string $itemType, string $inputSerial, array $serialsInItem): string {
                    if (in_array($inputSerial, $serialsInItem, true)) return $inputSerial;
                    $dc = DB::table('device_codes')
                        ->where('dispatch_id', $dispatchId)
                        ->where('item_id', $itemId)
                        ->where('item_type', $itemType)
                        ->where('serial_main', $inputSerial)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($dc && !empty($dc->old_serial)) return trim((string) $dc->old_serial);
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

                if (!SerialHelper::isVirtualSerial($requestedReplacementSerial)) {
                    $isReplacementSerialUsed = DispatchReplacement::where('replacement_dispatch_item_id', $replacementItem->id)
                        ->where('replacement_serial', $requestedReplacementSerial)
                        ->exists();
                    if ($isReplacementSerialUsed) {
                        return redirect()->back()->with('error', 'Serial thiết bị dự phòng đã được sử dụng để thay thế trước đó.');
                    }
                }
                
                $originalSerials = array_values(array_diff($originalSerials, [$requestedOriginalSerial]));
                $replacementSerials = array_values(array_diff($replacementSerials, [$requestedReplacementSerial]));
                $originalSerials[] = $requestedReplacementSerial;
                $replacementSerials[] = $requestedOriginalSerial;
                
                $originalItem->serial_numbers = array_values(array_unique($originalSerials));
                $replacementItem->serial_numbers = array_values(array_unique($replacementSerials));
                $originalItem->quantity = count($originalItem->serial_numbers);
                $replacementItem->quantity = count($replacementItem->serial_numbers);
            }

            // Cập nhật ghi chú
            $originalSerialText = $isOrigMeasurement ? "Số lượng {$replaceQty}" : (SerialHelper::isVirtualSerial($requestedOriginalSerial) ? SerialHelper::formatSerialForDisplay($requestedOriginalSerial) : "Serial {$requestedOriginalSerial}");
            $replacementSerialText = $isReplMeasurement ? "Số lượng {$replaceQty}" : (SerialHelper::isVirtualSerial($requestedReplacementSerial) ? SerialHelper::formatSerialForDisplay($requestedReplacementSerial) : "Serial {$requestedReplacementSerial}");
            
            $originalItem->notes = ($originalItem->notes ? $originalItem->notes . "\n" : "") .
                "{$originalSerialText} đã được thay thế bằng {$replacementSerialText} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];
            $replacementItem->notes = ($replacementItem->notes ? $replacementItem->notes . "\n" : "") .
                "{$replacementSerialText} đã thay thế cho {$originalSerialText} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];

            $originalItem->save();
            $replacementItem->save();

            // Tạo phiếu thay thế
            $replacement = DispatchReplacement::create([
                'replacement_code' => DispatchReplacement::generateReplacementCode(),
                'original_dispatch_item_id' => $originalItem->id,
                'replacement_dispatch_item_id' => $replacementItem->id,
                'original_serial' => $requestedOriginalSerial,
                'replacement_serial' => $requestedReplacementSerial,
                'user_id' => $this->getAuditUserId(),
                'replacement_date' => Carbon::now(),
                'reason' => $validatedData['reason'],
                'status' => 'completed',
                'quantity' => $replaceQty,
            ]);

            // Xử lý bảo hành
            $warranty = null;
            if ($originalItem->dispatch->dispatch_type === 'rental') {
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($query) use ($originalItem) {
                        $query->where('project_id', $originalItem->dispatch->project_id)
                            ->where('dispatch_type', 'rental');
                    })->first();
            } elseif ($originalItem->dispatch->project_id) {
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($query) use ($originalItem) {
                        $query->where('project_id', $originalItem->dispatch->project_id)
                            ->where('dispatch_type', '!=', 'rental');
                    })->first();
            }
            
            if (!$warranty) {
                $this->createWarranty($originalItem, $replacementItem, $validatedData['reason']);
            } else {
                $warranty->notes = ($warranty->notes ? $warranty->notes . "\n" : "") . 
                    "Thiết bị được thay thế ngày " . Carbon::now()->format('d/m/Y H:i') . 
                    ". Lý do: " . $validatedData['reason'] . 
                    ". Thiết bị thay thế: " . $this->getItemCode($replacementItem);
                $warranty->save();
            }

            DB::commit();

            Cache::flush();
            session()->forget(['backup_items_cache', 'contract_items_cache']);
            
            $redirectUrl = redirect()->back()->getTargetUrl();
            if ($originalItem->dispatch->project_id && $originalItem->dispatch->dispatch_type !== 'rental') {
                $redirectUrl = route('projects.show', $originalItem->dispatch->project_id);
            }

            return redirect($redirectUrl)->with('success', 'Thiết bị đã được thay thế thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error replacing equipment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi thay thế thiết bị: ' . $e->getMessage());
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
     * Helper: Cập nhật số lượng và serial trong kho (dành cho thu hồi thiết bị)
     */
    private function updateWarehouseQuantityWithSerial($itemType, $itemId, $warehouseId, $quantity, $serialNumber)
    {
        // Kiểm tra xem đã có bản ghi trong warehouse_materials chưa
        $warehouseMaterial = DB::table('warehouse_materials')
            ->where('item_type', $itemType)
            ->where('material_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($warehouseMaterial) {
            // Lấy serial numbers hiện tại
            $currentSerials = [];
            if (!empty($warehouseMaterial->serial_number)) {
                $currentSerials = json_decode($warehouseMaterial->serial_number, true) ?: [];
            }
            
            // Thêm serial mới vào danh sách
            if (!in_array($serialNumber, $currentSerials)) {
                $currentSerials[] = $serialNumber;
            }
            
            // Cập nhật số lượng và serial numbers
            DB::table('warehouse_materials')
                ->where('id', $warehouseMaterial->id)
                ->update([
                    'quantity' => $warehouseMaterial->quantity + $quantity,
                    'serial_number' => json_encode($currentSerials),
                    'updated_at' => now()
                ]);
        } else {
            // Tạo bản ghi mới với serial number
            DB::table('warehouse_materials')
                ->insert([
                    'warehouse_id' => $warehouseId,
                    'item_type' => $itemType,
                    'material_id' => $itemId,
                    'quantity' => $quantity,
                    'serial_number' => json_encode([$serialNumber]),
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
                        $item->used_serials_global = $usedSerialsGlobal;
                        
                        // Lọc danh sách serial_numbers để chỉ giữ serial chưa sử dụng
                        $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
                        $serials = array_filter(array_map(function ($s) { return trim((string) $s); }, $serials));
                        
                        // Tách serial thật và virtual serial
                        $realSerials = array_filter($serials, function($s) {
                            return strpos($s, 'N/A-') !== 0;
                        });
                        $virtualSerials = array_filter($serials, function($s) {
                            return strpos($s, 'N/A-') === 0;
                        });
                        
                        // Sử dụng SerialDisplayHelper để lấy serial thật đã đổi tên
                        $displayRealSerials = \App\Helpers\SerialDisplayHelper::getDisplaySerials(
                            $dispatch->id,
                            $item->item_id,
                            $item->item_type,
                            $realSerials
                        );
                        
                        // Gộp serial thật và virtual serial
                        $allSerials = array_merge($displayRealSerials, array_values($virtualSerials));
                        
                        $availableSerials = array_values(array_filter($allSerials, function ($serial) use ($item, $usedSerialsGlobal) {
                            return !in_array($serial, $item->replacement_serials, true) && !in_array($serial, $usedSerialsGlobal, true);
                        }));
                        
                        $item->serial_numbers = $availableSerials;
                        $item->available_quantity = 0; // Không cần nữa vì đã có virtual serial trong DB
                        
                        return $item;
                    });
                $backupItems = $backupItems->concat($items);
            }
            
            return response()->json([
                'success' => true,
                'backupItems' => $backupItems->values(),
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
                        $item->used_serials_global = $usedSerialsGlobal;
                        
                        // Lọc danh sách serial_numbers để chỉ giữ serial chưa sử dụng
                        $serials = is_array($item->serial_numbers) ? $item->serial_numbers : [];
                        $serials = array_filter(array_map(function ($s) { return trim((string) $s); }, $serials));
                        
                        // Tách serial thật và virtual serial
                        $realSerials = array_filter($serials, function($s) {
                            return strpos($s, 'N/A-') !== 0;
                        });
                        $virtualSerials = array_filter($serials, function($s) {
                            return strpos($s, 'N/A-') === 0;
                        });
                        
                        // Sử dụng SerialDisplayHelper để lấy serial thật đã đổi tên
                        $displayRealSerials = \App\Helpers\SerialDisplayHelper::getDisplaySerials(
                            $dispatch->id,
                            $item->item_id,
                            $item->item_type,
                            $realSerials
                        );
                        
                        // Gộp serial thật và virtual serial
                        $allSerials = array_merge($displayRealSerials, array_values($virtualSerials));
                        
                        $availableSerials = array_values(array_filter($allSerials, function ($serial) use ($item, $usedSerialsGlobal) {
                            return !in_array($serial, $item->replacement_serials, true) && !in_array($serial, $usedSerialsGlobal, true);
                        }));
                        
                        $item->serial_numbers = $availableSerials;
                        $item->available_quantity = 0; // Không cần nữa vì đã có virtual serial trong DB
                        
                        return $item;
                    });
                $backupItems = $backupItems->concat($items);
            }
            
            return response()->json([
                'success' => true,
                'backupItems' => $backupItems->values(),
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

    /**
     * Kiểm tra xem thiết bị có phải là hàng đo lường dựa trên đơn vị
     */
    private function isMeasurementItem($dispatchItem)
    {
        $unit = '';
        if ($dispatchItem->item_type === 'material' && $dispatchItem->material) {
            $unit = $dispatchItem->material->unit;
        } elseif ($dispatchItem->item_type === 'product' && $dispatchItem->product) {
            $unit = 'Cái'; // Thành phẩm mặc định là cái
        } elseif ($dispatchItem->item_type === 'good' && $dispatchItem->good) {
            $unit = $dispatchItem->good->unit;
        }
        
        if (empty($unit)) {
            // Nếu không có đơn vị, nhưng có quantity > 1 và không có serials, coi là bulk item (giống đo lường)
            return empty($dispatchItem->serial_numbers) && $dispatchItem->quantity > 1;
        }
        
        $isMeasureUnit = in_array(strtolower(trim($unit)), ['cm', 'mét', 'm', 'gram', 'kg', 'mét ', 'mét ']);
        
        // Nếu là đơn vị đo lường HOẶC là hàng không serial với số lượng > 1
        return $isMeasureUnit || (empty($dispatchItem->serial_numbers) && $dispatchItem->quantity > 1);
    }

    /**
     * Giảm số lượng trong các bản ghi DispatchReplacement mà item này đang là replacement (greedy reduction)
     */
    private function reduceReplacementsForReturn($dispatchItem, $qtyToReduce, $priorityReplacementId = null, $prioritizeOutgoing = false): float
    {
        $totalToReduce = (float)$qtyToReduce;
        
        // 1. Ưu tiên trừ vào replacement_id cụ thể nếu có
        if (!empty($priorityReplacementId)) {
            $priorityRep = \App\Models\DispatchReplacement::find($priorityReplacementId);
            if ($priorityRep && ($priorityRep->replacement_dispatch_item_id == $dispatchItem->id || $priorityRep->original_dispatch_item_id == $dispatchItem->id)) {
                // Phải xác định hướng thu hồi cho priorityRep
                $isOutgoing = ($priorityRep->original_dispatch_item_id == $dispatchItem->id);
                
                $availableToReturn = (float)$priorityRep->quantity - (float)($isOutgoing ? ($priorityRep->original_returned_quantity ?? 0) : ($priorityRep->replacement_returned_quantity ?? 0));
                
                if ($availableToReturn > 0) {
                    $canReduce = min($availableToReturn, $totalToReduce);
                    
                    if ($isOutgoing) {
                        $priorityRep->original_returned_quantity = (float)($priorityRep->original_returned_quantity ?? 0) + $canReduce;
                    } else {
                        $priorityRep->replacement_returned_quantity = (float)($priorityRep->replacement_returned_quantity ?? 0) + $canReduce;
                    }
                    
                    $totalToReduce -= $canReduce;
                    $priorityRep->save();
                }
            }
        }
        
        if ($totalToReduce <= 0) return (float)$totalToReduce;

        // Định nghĩa thứ tự ưu tiên: incoming (món này đi thay thế món khác) và outgoing (món này bị món khác thay thế)
        $order = $prioritizeOutgoing ? ['outgoing', 'incoming'] : ['incoming', 'outgoing'];

        foreach ($order as $type) {
            if ($totalToReduce <= 0) break;

            if ($type === 'incoming') {
                $replacements = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $dispatchItem->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            } else {
                $replacements = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }

            foreach ($replacements as $rep) {
                if ($totalToReduce <= 0) break;
                if (!empty($priorityReplacementId) && $rep->id == $priorityReplacementId) continue;

                // Tính số lượng còn lại có thể thu hồi dựa trên hướng thu hồi
                $availableToReturn = (float)$rep->quantity - (float)($prioritizeOutgoing ? ($rep->original_returned_quantity ?? 0) : ($rep->replacement_returned_quantity ?? 0));
                if ($availableToReturn <= 0) continue;

                $canReduce = min($availableToReturn, $totalToReduce);
                
                if ($prioritizeOutgoing) {
                    $rep->original_returned_quantity = (float)($rep->original_returned_quantity ?? 0) + $canReduce;
                } else {
                    $rep->replacement_returned_quantity = (float)($rep->replacement_returned_quantity ?? 0) + $canReduce;
                }
                
                $totalToReduce -= $canReduce;
                $rep->save();
            }
        }

        return (float)$totalToReduce;
    }
}