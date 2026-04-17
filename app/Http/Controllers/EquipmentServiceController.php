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
            'merged_backup_dispatch_item_ids' => 'nullable|string|max:2000',
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

            // Chặn thu hồi Vật tư (material) qua dự án - phải dùng luồng Thu hồi Hàng hoá/Vật phẩm khác
            if ($dispatchItem->item_type === 'material') {
                return redirect()->back()
                    ->with('error', 'Vật tư không thể thu hồi bằng thao tác trong dự án. Vui lòng sử dụng chức năng Thu hồi Hàng hoá/Vật phẩm khác.');
            }

            // Xây dựng thông tin nguồn (Dự án / Phiếu cho thuê) cho mô tả nhật ký
            $projectId = $validatedData['project_id'] ?? null;
            $rentalId = $validatedData['rental_id'] ?? null;
            $recallSourceDescription = '';
            $documentCode = ''; // Mã phiếu hiển thị trong nhật ký (project_code hoặc rental_code)
            
            // Lấy dispatch của item để xác định loại dự án/cho thuê chính xác
            $dispatch = $dispatchItem->dispatch;
            
            if ($projectId) {
                $project = \App\Models\Project::find($projectId);
                $recallSourceDescription = 'Thu hồi từ Dự án: ' . ($project ? $project->project_name : 'Không xác định');
                $documentCode = $project ? $project->project_code : '';
            } elseif ($rentalId) {
                $rental = \App\Models\Rental::find($rentalId);
                $recallSourceDescription = 'Thu hồi từ Phiếu cho thuê: ' . ($rental ? ($rental->rental_name ?: $rental->rental_code) : 'Không xác định');
                $documentCode = $rental ? $rental->rental_code : '';
            } elseif ($dispatch && $dispatch->project_id) {
                // Fallback: lấy từ dispatch của item và kiểm tra dispatch_type
                if ($dispatch->dispatch_type === 'rental') {
                    $rental = \App\Models\Rental::find($dispatch->project_id);
                    $recallSourceDescription = 'Thu hồi từ Phiếu cho thuê: ' . ($rental ? ($rental->rental_name ?: $rental->rental_code) : 'Không xác định');
                    $documentCode = $rental ? $rental->rental_code : '';
                } else {
                    $project = \App\Models\Project::find($dispatch->project_id);
                    $recallSourceDescription = 'Thu hồi từ Dự án: ' . ($project ? $project->project_name : 'Không xác định');
                    $documentCode = $project ? $project->project_code : '';
                }
            } else {
                $recallSourceDescription = 'Thu hồi thiết bị';
            }

            if ($isMeasurement) {
                $remainingToReturn = $returnQty;
                $dispatchReturn = null;
                
                // 2. Greedy return for measurement items - Prioritize Backup over Contract
                if ($remainingToReturn > 0.0001) {
                    $itemCode = $this->getItemCode($dispatchItem);
                    $isUsedRecall = filter_var($request->input('is_used'), FILTER_VALIDATE_BOOLEAN);
                    
                    Log::info("Bắt đầu thu hồi tham lam cho {$dispatchItem->item_type} [{$itemCode}]: {$returnQty} đơn vị");
                    
                    // Lấy TẤT CẢ các bản ghi cùng loại tại site (bao gồm cả bản ghi vừa nhấn nút)
                    $allItemsQuery = DispatchItem::where('item_type', $dispatchItem->item_type)
                        ->where('item_id', $dispatchItem->item_id)
                        ->whereIn('category', ['contract', 'backup', 'general'])
                        ->whereHas('dispatch', function($q) use ($projectId, $rentalId) {
                            if ($projectId) {
                                $q->where('project_id', $projectId);
                            } elseif ($rentalId) {
                                $rental = \App\Models\Rental::find($rentalId);
                                if ($rental) {
                                    $q->where('dispatch_type', 'rental')
                                      ->where(function($qq) use ($rental) {
                                          $qq->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                            ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                      });
                                } else {
                                    $q->where('id', 0); 
                                }
                            }
                        });

                    if ($itemCode) {
                        $allItemsQuery->where(function($q) use ($itemCode, $dispatchItem) {
                            if ($dispatchItem->item_type == 'material') {
                                $q->whereHas('material', fn($qq) => $qq->where('code', $itemCode));
                            } elseif ($dispatchItem->item_type == 'product') {
                                $q->whereHas('product', fn($qq) => $qq->where('code', $itemCode));
                            } elseif ($dispatchItem->item_type == 'good') {
                                $q->whereHas('good', fn($qq) => $qq->where('code', $itemCode));
                            }
                        });
                    }

                    if (!$isUsedRecall) {
                        $allItemsQuery->where('quantity', '>', 0.0001);
                        // Chỉ áp dụng lọc "không phải hàng cũ" cho hạng mục DỰ PHÒNG
                        // Hàng Hợp đồng (contract) luôn được phép thu hồi (hệ thống sẽ tự xử lý lớp thay thế bên trong)
                        $allItemsQuery->where(function($q) {
                            $q->where('category', '!=', 'backup')
                              ->orWhereNotExists(function($sq) {
                                  $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                                    ->from('dispatch_replacements')
                                    ->whereColumn('dispatch_replacements.original_dispatch_item_id', 'dispatch_items.id');
                              });
                        });
                    } else {
                        // Khi thu hồi hàng cũ (isUsed), chỉ lấy các dòng ĐÃ bị thay thế (thường là backup)
                        $allItemsQuery->whereExists(function($q) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('dispatch_replacements')
                                ->whereColumn('dispatch_replacements.original_dispatch_item_id', 'dispatch_items.id');
                        });
                    }

                    // SẮP XẾP ƯU TIÊN ĐỘNG THEO NGỮ CẢNH NGƯỜI DÙNG BẤM:
                    // - Thu hồi từ dòng hợp đồng/general => ưu tiên chính dòng đó trước để số lượng hợp đồng giảm đúng như UI kỳ vọng.
                    // - Thu hồi từ dòng dự phòng => giữ ưu tiên dự phòng trước.
                    // - Thu hồi hàng cũ (is_used) => ưu tiên backup để lấy hàng lỗi đã thay ra.
                    $priorityId = $dispatchItem->id;
                    $categoryOrder = "'backup', 'general', 'contract'";
                    if (!$isUsedRecall) {
                        if ($dispatchItem->category === 'contract') {
                            $categoryOrder = "'contract', 'general', 'backup'";
                        } elseif ($dispatchItem->category === 'general') {
                            $categoryOrder = "'general', 'contract', 'backup'";
                        }
                    }

                    // Dự phòng đo lường: UI gộp cùng mã — chỉ thu trên các dispatch_item trong merged_backup_dispatch_item_ids (theo thứ tự id).
                    if ($dispatchItem->category === 'backup' && !$isUsedRecall) {
                        $items = $this->resolveMeasurementBackupReturnTargets(
                            $request,
                            $dispatchItem,
                            $projectId,
                            $rentalId
                        );
                    } else {
                        $items = $allItemsQuery
                            ->orderByRaw("id = {$priorityId} DESC") // Ưu tiên tuyệt đối dòng được bấm
                            ->orderByRaw("FIELD(category, {$categoryOrder})")
                            ->orderBy('id', 'asc')
                            ->get();
                    }
                    
                    $syncBackupReturnContext = [
                        'warehouse_id' => $warehouse->id,
                        'user_id' => $this->getAuditUserId(),
                        'reason' => $validatedData['reason'],
                    ];

                    foreach ($items as $targetItem) {
                        if ($remainingToReturn <= 0) break;
                        
                        // Thực hiện thu hồi tham lam cho bản ghi này
                        $priorityReplacementId = ($targetItem->id == $dispatchItem->id) ? ($validatedData['replacement_id'] ?? null) : null;
                        $actuallyReturned = $this->returnMeasurementItemGreedy($targetItem, $remainingToReturn, $priorityReplacementId, $isUsedRecall, $syncBackupReturnContext);
                        
                        if ($actuallyReturned > 0) {
                            $newReturn = DispatchReturn::create([
                                'return_code' => DispatchReturn::generateReturnCode(),
                                'dispatch_item_id' => $targetItem->id,
                                'warehouse_id' => $warehouse->id,
                                'user_id' => $this->getAuditUserId(),
                                'return_date' => Carbon::now(),
                                'reason_type' => 'return',
                                'reason' => $validatedData['reason'] . ($targetItem->id != $dispatchItem->id ? " (Thu hồi tự động từ dòng tương tự)" : ""),
                                'condition' => 'good',
                                'status' => 'completed',
                                'serial_number' => 'MEASUREMENT',
                                'quantity' => $actuallyReturned,
                            ]);
                            
                            if (!$dispatchReturn) $dispatchReturn = $newReturn;
                            $remainingToReturn -= $actuallyReturned;

                            try {
                                $targetItemInfo = $this->getItemInfo($targetItem);
                                ChangeLogHelper::thuHoi(
                                    $targetItemInfo['code'],
                                    $targetItemInfo['name'],
                                    $actuallyReturned,
                                    !empty($documentCode) ? $documentCode : $newReturn->return_code,
                                    $recallSourceDescription . ' (Hàng đo lường)',
                                    ['dispatch_item_id' => $targetItem->id, 'return_code' => $newReturn->return_code],
                                    $validatedData['reason']
                                );
                            } catch (\Exception $e) {
                                Log::error("Lỗi khi lưu nhật ký thu hồi (ID: {$targetItem->id}): " . $e->getMessage());
                            }
                        }
                    }
                }
                
                if ($remainingToReturn > 0.001) {
                    // This shouldn't happen if validation was correct, but as a safety:
                    Log::warning("Greedy return finished with {$remainingToReturn} remaining for item ID {$dispatchItem->id}");
                }
            } else {
                // Kiểm tra serial có tồn tại trong item không
                $serialNumbers = is_array($dispatchItem->serial_numbers) ? $dispatchItem->serial_numbers : [];
                if (!in_array($serialToReturn, $serialNumbers)) {
                    // Fallback: serial có thể là serial thực từ device_codes
                    // (UI hiện serial thực nhưng dispatch_items vẫn chứa serial virtual N/A-xxx)
                    $deviceCodeMatch = DB::table('device_codes')
                        ->where('dispatch_id', $dispatchItem->dispatch_id)
                        ->where('item_id', $dispatchItem->item_id)
                        ->where('item_type', $dispatchItem->item_type)
                        ->where('serial_main', $serialToReturn)
                        ->first();
                    
                    if ($deviceCodeMatch && !empty($deviceCodeMatch->old_serial)) {
                        if (in_array($deviceCodeMatch->old_serial, $serialNumbers)) {
                            // Serial thực tìm thấy trong device_codes, dùng old_serial (virtual) cho internal tracking
                            $serialToReturn = $deviceCodeMatch->old_serial;
                        } elseif (empty($serialNumbers)) {
                            // dispatch_items.serial_numbers rỗng nhưng device_codes có serial
                            // (trường hợp xuất kho không serial, sau đó cập nhật serial qua Excel)
                            // Giữ nguyên serial thực từ device_codes — sẽ lưu vào dispatch_returns
                            // và EquipmentServiceController sẽ xử lý giảm quantity
                        } else {
                            return redirect()->back()
                                ->with('error', 'Serial thiết bị không tồn tại trong item này.');
                        }
                    } else {
                        return redirect()->back()
                            ->with('error', 'Serial thiết bị không tồn tại trong item này.');
                    }
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

        // Tạo phiếu thu hồi (Chỉ tạo nếu chưa được Greedy xử lý hoặc là serial item)
            if (!$isMeasurement) {
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

                // Nhật ký cho serial item
                try {
                    $serialItemInfo = $this->getItemInfo($dispatchItem);
                    ChangeLogHelper::thuHoi(
                        $serialItemInfo['code'],
                        $serialItemInfo['name'],
                        $returnQty,
                        !empty($documentCode) ? $documentCode : $dispatchReturn->return_code,
                        $recallSourceDescription . ' (Serial: ' . $serialToReturn . ')',
                        ['serial_number' => $serialToReturn, 'return_code' => $dispatchReturn->return_code],
                        $validatedData['reason']
                    );
                } catch (\Exception $e) {
                    Log::error('Lỗi khi lưu nhật ký thu hồi serial: ' . $e->getMessage());
                }
            }

            // Phần đo lường đã được tạo DispatchReturn trong khối Greedy ở trên


            // Cập nhật số lượng và serial trong kho
            if ($isMeasurement) {
                $this->updateWarehouseQuantity($dispatchItem->item_type, $dispatchItem->item_id, $warehouse->id, $returnQty);
            } else {
                // Nếu serial vẫn là virtual (N/A-xxx), chỉ tăng số lượng, không thêm serial vô nghĩa vào kho
                if (SerialHelper::isVirtualSerial($actualSerialToReturn)) {
                    $this->updateWarehouseQuantity($dispatchItem->item_type, $dispatchItem->item_id, $warehouse->id, 1);
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

                // Cập nhật thông số thu hồi cho hàng hoá có Serial (đảm bảo nó biến mất khỏi bảng hiển thị sau khi thu hồi)
                $isUsedRecall = filter_var($request->input('is_used'), FILTER_VALIDATE_BOOLEAN);
                $replacementId = $validatedData['replacement_id'] ?? null;
                
                $rep = null;
                if (!empty($replacementId)) {
                    $rep = \App\Models\DispatchReplacement::find($replacementId);
                }

                // Nếu không tìm thấy bằng ID thì fallback tìm theo Serial (để bảo vệ dữ liệu cũ)
                if (!$rep) {
                    if ($isUsedRecall) {
                        $rep = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
                            ->where('original_serial', $serialToReturn)
                            ->whereRaw('quantity > COALESCE(original_returned_quantity, 0)')
                            ->first();
                    } else {
                        $rep = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $dispatchItem->id)
                            ->where('replacement_serial', $serialToReturn)
                            ->whereRaw('quantity > COALESCE(replacement_returned_quantity, 0)')
                            ->first();
                    }
                }

                if ($rep) {
                    // Xác định hướng để cập nhật đúng trường
                    if ($isUsedRecall && $rep->original_dispatch_item_id == $dispatchItem->id) {
                        $rep->original_returned_quantity = (float)($rep->original_returned_quantity ?? 0) + 1;
                    } elseif (!$isUsedRecall && $rep->replacement_dispatch_item_id == $dispatchItem->id) {
                        $rep->replacement_returned_quantity = (float)($rep->replacement_returned_quantity ?? 0) + 1;
                    }
                    $rep->save();
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
            'project_id' => 'nullable|exists:projects,id',
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
            // Xử lý replacement_device_id
            $replacementDeviceIdInput = $validatedData['replacement_device_id'];
            $isGrouped = (strpos($replacementDeviceIdInput, 'GROUPED:') === 0);
            
            $backupItems = collect();
            $itemType = null;
            $itemId = null;

            if ($isGrouped) {
                // Parse GROUPED:item_type:item_id:MEASUREMENT
                $parts = explode(':', $replacementDeviceIdInput);
                $itemType = $parts[1];
                $itemId = $parts[2];
                
                $projectId = $validatedData['project_id'] ?? null;
                $rentalId = $validatedData['rental_id'] ?? null;
                
                $dispatches = collect();
                if ($projectId) {
                    $dispatches = Dispatch::where('dispatch_type', 'project')
                        ->where('project_id', $projectId)
                        ->whereIn('status', ['approved', 'completed'])
                        ->get();
                } elseif ($rentalId) {
                    $rental = \App\Models\Rental::find($rentalId);
                    if ($rental) {
                        $dispatches = Dispatch::where('dispatch_type', 'rental')
                            ->whereIn('status', ['approved', 'completed'])
                            ->where(function($query) use ($rental) {
                                $query->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                    ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                            })
                            ->get();
                    }
                }
                
                foreach ($dispatches as $dispatch) {
                    $items = $dispatch->items()
                        ->where('category', 'backup')
                        ->where('item_type', $itemType)
                        ->where('item_id', $itemId)
                        ->where('quantity', '>', 0)
                        ->get();
                    $backupItems = $backupItems->concat($items);
                }
                
                if ($backupItems->isEmpty()) {
                    return redirect()->back()->with('error', 'Không tìm thấy thiết bị dự phòng nào phù hợp.');
                }
                
                $backupItems = $backupItems->sortBy('id');
            } else {
                $replacementDeviceId = $replacementDeviceIdInput;
                if (strpos($replacementDeviceIdInput, ':') !== false) {
                    $replacementDeviceId = explode(':', $replacementDeviceIdInput)[0];
                }
                
                $replacementItem = DispatchItem::with(['dispatch', 'material', 'product', 'good'])->findOrFail($replacementDeviceId);
                $backupItems->push($replacementItem);
            }

            // Lấy thông tin thiết bị gốc
            $originalItem = DispatchItem::with(['dispatch', 'material', 'product', 'good'])->findOrFail($validatedData['equipment_id']);
            
            // Lấy item đầu tiên làm đại diện cho các check ban đầu (nếu là đơn lẻ)
            $firstBackupItem = $backupItems->first();
            $replacementItem = $firstBackupItem;

            // Kiểm tra mã thiết bị
            $originalCode = $this->getItemCode($originalItem);
            $replacementCode = $this->getItemCode($firstBackupItem);
            if ($originalCode !== $replacementCode) {
                return redirect()->back()->with('error', 'Thiết bị thay thế phải có cùng mã thiết bị với thiết bị cần thay thế.');
            }

            $requestedOriginalSerialInput = trim((string) $validatedData['equipment_serial']);
            $requestedReplacementSerialInput = trim((string) $validatedData['replacement_serial']);
            
            $isOrigMeasurement = ($requestedOriginalSerialInput === 'MEASUREMENT' || $this->isMeasurementItem($originalItem));
            $isReplMeasurement = ($requestedReplacementSerialInput === 'MEASUREMENT' || $this->isMeasurementItem($firstBackupItem));
            $replaceQty = ($isOrigMeasurement || $isReplMeasurement) ? (float)($validatedData['quantity'] ?? 1) : 1;

            if ($isOrigMeasurement || $isReplMeasurement) {
                // Logic cho hàng đo lường (Hỗ trợ gộp)
                // Nghiệp vụ: Hàng trên hợp đồng (kể cả hàng đã đổi từ dự phòng lên) vẫn có thể đổi tiếp khi lỗi.
                // Giới hạn mỗi lần: min(tổng hợp đồng − thu hồi, dự phòng còn dùng được) — không trừ dồn "đã thay" khỏi tổng hợp đồng.

                $similarOriginalItems = DispatchItem::where('item_type', $originalItem->item_type)
                    ->where('item_id', $originalItem->item_id)
                    ->where('category', $originalItem->category)
                    ->whereHas('dispatch', function($q) use ($originalItem) {
                        if ($originalItem->dispatch->project_id) {
                            $q->where('project_id', $originalItem->dispatch->project_id);
                        }
                    })->get();

                $availableToReplace = 0;
                foreach ($similarOriginalItems as $sim) {
                    $availableToReplace += $this->contractNetMeasurementQty($sim);
                }

                if ($replaceQty > $availableToReplace) {
                    return redirect()->back()->with('error', "Số lượng thay thế ({$replaceQty}) vượt quá tổng số lượng trên hợp đồng còn tại site ({$availableToReplace}).");
                }

                $totalBackupQty = 0;
                foreach ($backupItems as $bi) {
                    $totalBackupQty += $this->netBackupDispatchableQty($bi);
                }
                if ($replaceQty > $totalBackupQty) {
                    return redirect()->back()->with('error', "Tổng số lượng thiết bị dự phòng khả dụng ({$totalBackupQty}) không đủ để thay thế.");
                }

                $requestedOriginalSerial = 'MEASUREMENT';
                $requestedReplacementSerial = 'MEASUREMENT';

                $qtyRemainingToTake = $replaceQty;
                $originalItemsQueue = $similarOriginalItems->filter(function ($item) {
                    return $this->contractNetMeasurementQty($item) > 0.0001;
                })->sortBy('id')->values();

                $origIdx = 0;
                $currentOrigItem = $originalItemsQueue[$origIdx] ?? null;
                $currentOrigAvailable = 0;
                if ($currentOrigItem) {
                    $currentOrigAvailable = (float) $this->contractNetMeasurementQty($currentOrigItem);
                }

                foreach ($backupItems as $repItem) {
                    if ($qtyRemainingToTake <= 0) break;
                    if ($repItem->quantity <= 0) continue;

                    $repItemQty = $this->netBackupDispatchableQty($repItem);
                    while ($repItemQty > 0 && $qtyRemainingToTake > 0 && $currentOrigItem) {
                        $take = min($repItemQty, $qtyRemainingToTake, $currentOrigAvailable);
                        if ($take <= 0) {
                            $origIdx++;
                            $currentOrigItem = $originalItemsQueue[$origIdx] ?? null;
                            if (!$currentOrigItem) {
                                break;
                            }
                            $currentOrigAvailable = (float) $this->contractNetMeasurementQty($currentOrigItem);
                            continue;
                        }

                        // Thực hiện thay thế $take đơn vị từ $repItem cho $currentOrigItem
                        // KHÔNG trừ $repItem->quantity trong DB - việc sử dụng đã được ghi nhận
                        // trong bảng DispatchReplacement, ProjectController sẽ tính toán từ đó.
                        
                        /** @var DispatchItem $repItem */
                        /** @var DispatchItem $currentOrigItem */
                        
                        $repItemQty -= $take;
                        $qtyRemainingToTake -= $take;
                        $currentOrigAvailable -= $take;

                        $originalSerialText = "Số lượng {$take}";
                        $replacementSerialText = "Số lượng {$take}";
                        
                        $currentOrigItem->notes = ($currentOrigItem->notes ? $currentOrigItem->notes . "\n" : "") .
                            "{$originalSerialText} đã được thay thế bằng {$replacementSerialText} từ " . ($repItem->dispatch->dispatch_code ?? 'dự phòng') . " ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];
                        $repItem->notes = ($repItem->notes ? $repItem->notes . "\n" : "") .
                            "{$replacementSerialText} đã thay thế cho thiết bị hợp đồng ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];

                        $repItem->save();
                        $currentOrigItem->save();
                        
                        DispatchReplacement::create([
                            'replacement_code' => DispatchReplacement::generateReplacementCode(),
                            'original_dispatch_item_id' => $currentOrigItem->id,
                            'replacement_dispatch_item_id' => $repItem->id,
                            'original_serial' => 'MEASUREMENT',
                            'replacement_serial' => 'MEASUREMENT',
                            'user_id' => $this->getAuditUserId(),
                            'replacement_date' => Carbon::now(),
                            'reason' => $validatedData['reason'],
                            'status' => 'completed',
                            'quantity' => $take,
                        ]);
                    }
                }
                
                $originalItem->save();
                
            } else {
                // Logic cho thiết bị có serial (Logic cũ)
                $originalSerialsRaw = is_array($originalItem->serial_numbers) ? $originalItem->serial_numbers : [];
                $replacementSerialsRaw = is_array($replacementItem->serial_numbers) ? $replacementItem->serial_numbers : [];

                // Fallback: nếu serial_numbers rỗng hoặc tất cả là virtual, lấy serial từ device_codes
                // (giống ProjectController — items xuất kho không serial, sau đó cập nhật via Excel)
                if (empty($originalSerialsRaw) || collect($originalSerialsRaw)->every(fn($s) => \App\Helpers\SerialHelper::isVirtualSerial((string)$s))) {
                    $dcSerials = \App\Models\DeviceCode::where('dispatch_id', $originalItem->dispatch_id)
                        ->where('item_id', $originalItem->item_id)
                        ->where('item_type', $originalItem->item_type)
                        ->pluck('serial_main')->filter()->values()->toArray();
                    if (!empty($dcSerials)) {
                        $originalSerialsRaw = $dcSerials;
                    }
                }
                if (empty($replacementSerialsRaw) || collect($replacementSerialsRaw)->every(fn($s) => \App\Helpers\SerialHelper::isVirtualSerial((string)$s))) {
                    $dcSerials = \App\Models\DeviceCode::where('dispatch_id', $replacementItem->dispatch_id)
                        ->where('item_id', $replacementItem->item_id)
                        ->where('item_type', $replacementItem->item_type)
                        ->pluck('serial_main')->filter()->values()->toArray();
                    if (!empty($dcSerials)) {
                        $replacementSerialsRaw = $dcSerials;
                    }
                }

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
                        // Chỉ coi là "đã dùng" nếu serial chưa bị thu hồi hoàn toàn
                        ->whereRaw('COALESCE(replacement_returned_quantity, 0) < quantity')
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

                // Cập nhật ghi chú cho hàng serial
                $originalSerialText = (SerialHelper::isVirtualSerial($requestedOriginalSerial) ? SerialHelper::formatSerialForDisplay($requestedOriginalSerial) : "Serial {$requestedOriginalSerial}");
                $replacementSerialText = (SerialHelper::isVirtualSerial($requestedReplacementSerial) ? SerialHelper::formatSerialForDisplay($requestedReplacementSerial) : "Serial {$requestedReplacementSerial}");
                
                $originalItem->notes = ($originalItem->notes ? $originalItem->notes . "\n" : "") .
                    "{$originalSerialText} đã được thay thế bằng {$replacementSerialText} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];
                $replacementItem->notes = ($replacementItem->notes ? $replacementItem->notes . "\n" : "") .
                    "{$replacementSerialText} đã thay thế cho {$originalSerialText} ngày " . Carbon::now()->format('d/m/Y H:i') . ". Lý do: " . $validatedData['reason'];

                $originalItem->save();
                $replacementItem->save();

                // Tạo phiếu thay thế cho hàng serial
                DispatchReplacement::create([
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
            }

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

            // Lấy thông tin thu hồi
            $returns = \App\Models\DispatchReturn::with(['user'])
                ->where('dispatch_item_id', $dispatchItem->id)
                ->get()
                ->map(function ($ret) {
                    $ret->employee_name = $ret->user ? $ret->user->name : 'Không xác định';
                    return $ret;
                });
            
            return response()->json([
                'success' => true,
                'dispatchItem' => $dispatchItem,
                'replacements' => $replacements,
                'returns' => $returns,
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
            'item_type' => ($originalItem->dispatch->dispatch_type === 'rental' ? 'rental' : 'project'),
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
            // Chỉ bao gồm serial từ replacement records còn "active" (serial chưa bị thu hồi)
            $allReplacements = DispatchReplacement::whereHas('replacementDispatchItem.dispatch', function($q) use ($projectId) {
                $q->where('dispatch_type', 'project')
                  ->where('project_id', $projectId)
                  ->whereIn('status', ['approved', 'completed']);
            })->get(['id', 'replacement_serial', 'original_serial', 'original_dispatch_item_id', 'replacement_dispatch_item_id']);
            
            // Lấy tất cả bản ghi thu hồi liên quan
            $allReturnedSerials = \App\Models\DispatchReturn::whereIn('dispatch_item_id', 
                $allReplacements->pluck('original_dispatch_item_id')
                    ->merge($allReplacements->pluck('replacement_dispatch_item_id'))
                    ->unique()
                    ->values()
            )->get(['dispatch_item_id', 'serial_number']);
            
            $returnedMap = [];
            foreach ($allReturnedSerials as $ret) {
                $returnedMap[$ret->dispatch_item_id][] = trim((string)$ret->serial_number);
            }
            
            $usedSerialsGlobal = $allReplacements->flatMap(function ($r) use ($returnedMap) {
                $serials = [];
                $repSerial = trim((string)$r->replacement_serial);
                $orgSerial = trim((string)$r->original_serial);
                
                // Sau khi swap, serial có thể nằm ở BẤT KỲ dispatch_item nào
                // Nên check cả 2 dispatch_items khi tìm bản ghi thu hồi
                $allReturned = array_merge(
                    $returnedMap[$r->original_dispatch_item_id] ?? [],
                    $returnedMap[$r->replacement_dispatch_item_id] ?? []
                );
                
                // replacement_serial: chỉ coi là "used" nếu serial chưa bị thu hồi
                if ($repSerial && !in_array($repSerial, $allReturned, true)) {
                    $serials[] = $repSerial;
                }
                
                // original_serial: chỉ coi là "used" nếu serial chưa bị thu hồi
                if ($orgSerial && !in_array($orgSerial, $allReturned, true)) {
                    $serials[] = $orgSerial;
                }
                
                return $serials;
            })->filter()->unique()->values()->toArray();
            
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
                        
                        // Loại bỏ serial đã bị thu hồi (có bản ghi DispatchReturn)
                        $returnedSerials = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                            ->pluck('serial_number')
                            ->map(function($s) { return trim((string)$s); })
                            ->toArray();
                        $serials = array_filter($serials, function($s) use ($returnedSerials, $item) {
                            // Nếu serial đã bị thu hồi nhưng VẪN CÒN trong serial_numbers hiện tại (re-added via swap) → giữ lại
                            if (in_array($s, $returnedSerials, true)) {
                                // Chỉ giữ nếu serial xuất hiện nhiều hơn số lần bị thu hồi
                                $countInSerials = count(array_filter(is_array($item->serial_numbers) ? $item->serial_numbers : [], function($sn) use ($s) {
                                    return trim((string)$sn) === $s;
                                }));
                                $countReturned = count(array_filter($returnedSerials, function($rs) use ($s) {
                                    return $rs === $s;
                                }));
                                return $countInSerials > $countReturned;
                            }
                            return true;
                        });
                        
                        // Sử dụng SerialDisplayHelper để resolve TẤT CẢ serial (bao gồm cả N/A-xxx)
                        // vì virtual serial cũng có thể đã được cập nhật mã thiết bị qua device_codes
                        $allSerials = \App\Helpers\SerialDisplayHelper::getDisplaySerials(
                            $dispatch->id,
                            $item->item_id,
                            $item->item_type,
                            array_values($serials)
                        );
                        
                        $availableSerials = array_values(array_filter($allSerials, function ($serial) use ($item, $usedSerialsGlobal) {
                            return !in_array($serial, $item->replacement_serials, true) && !in_array($serial, $usedSerialsGlobal, true);
                        }));
                        
                        // Tính toán số lượng khả dụng cho hàng đo lường (bulk items)
                        if ($this->isMeasurementItem($item)) {
                            $usedQtyIn = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)->sum('quantity');
                            $usedQtyOut = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $item->id)->sum('quantity');
                            $returnQty = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)->sum('quantity');
                            // Avail = Gốc - (Đã dùng để thay cho dòng khác) + (Được dòng khác thay thế cho mình) - (Đã thu hồi)
                            $item->available_quantity = max(0.0, (float)$item->quantity - (float)$usedQtyIn + (float)$usedQtyOut - (float)$returnQty);
                        } else {
                            $item->available_quantity = count($availableSerials);
                        }
                        
                        $item->serial_numbers = $availableSerials;
                        
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
            // Chỉ bao gồm serial từ replacement records còn "active" (serial chưa bị thu hồi)
            $allReplacements = DispatchReplacement::whereHas('replacementDispatchItem.dispatch', function($q) use ($rental) {
                $q->where('dispatch_type', 'rental')
                  ->where(function($qq) use ($rental) {
                      $qq->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                         ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                  })
                  ->whereIn('status', ['approved', 'completed']);
            })->get(['id', 'replacement_serial', 'original_serial', 'original_dispatch_item_id', 'replacement_dispatch_item_id']);
            
            $allReturnedSerials = \App\Models\DispatchReturn::whereIn('dispatch_item_id', 
                $allReplacements->pluck('original_dispatch_item_id')
                    ->merge($allReplacements->pluck('replacement_dispatch_item_id'))
                    ->unique()
                    ->values()
            )->get(['dispatch_item_id', 'serial_number']);
            
            $returnedMap = [];
            foreach ($allReturnedSerials as $ret) {
                $returnedMap[$ret->dispatch_item_id][] = trim((string)$ret->serial_number);
            }
            
            $usedSerialsGlobal = $allReplacements->flatMap(function ($r) use ($returnedMap) {
                $serials = [];
                $repSerial = trim((string)$r->replacement_serial);
                $orgSerial = trim((string)$r->original_serial);
                
                $allReturned = array_merge(
                    $returnedMap[$r->original_dispatch_item_id] ?? [],
                    $returnedMap[$r->replacement_dispatch_item_id] ?? []
                );
                
                if ($repSerial && !in_array($repSerial, $allReturned, true)) {
                    $serials[] = $repSerial;
                }
                
                if ($orgSerial && !in_array($orgSerial, $allReturned, true)) {
                    $serials[] = $orgSerial;
                }
                
                return $serials;
            })->filter()->unique()->values()->toArray();
            
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
                        
                        // Loại bỏ serial đã bị thu hồi (có bản ghi DispatchReturn)
                        $returnedSerials = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                            ->pluck('serial_number')
                            ->map(function($s) { return trim((string)$s); })
                            ->toArray();
                        $serials = array_filter($serials, function($s) use ($returnedSerials, $item) {
                            if (in_array($s, $returnedSerials, true)) {
                                $countInSerials = count(array_filter(is_array($item->serial_numbers) ? $item->serial_numbers : [], function($sn) use ($s) {
                                    return trim((string)$sn) === $s;
                                }));
                                $countReturned = count(array_filter($returnedSerials, function($rs) use ($s) {
                                    return $rs === $s;
                                }));
                                return $countInSerials > $countReturned;
                            }
                            return true;
                        });
                        
                        // Sử dụng SerialDisplayHelper để resolve TẤT CẢ serial (bao gồm cả N/A-xxx)
                        // vì virtual serial cũng có thể đã được cập nhật mã thiết bị qua device_codes
                        $allSerials = \App\Helpers\SerialDisplayHelper::getDisplaySerials(
                            $dispatch->id,
                            $item->item_id,
                            $item->item_type,
                            array_values($serials)
                        );
                        
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

        // Kiểm tra serial: nếu dispatch_items.serial_numbers rỗng, kiểm tra thêm device_codes
        // để đảm bảo phân loại nhất quán với ProjectController (nơi cũng lấy serial từ device_codes)
        $hasSerials = !empty($dispatchItem->serial_numbers);
        if (!$hasSerials && $dispatchItem->dispatch_id) {
            $hasDeviceCodeSerials = \App\Models\DeviceCode::where('dispatch_id', $dispatchItem->dispatch_id)
                ->where('item_id', $dispatchItem->item_id)
                ->where('item_type', $dispatchItem->item_type)
                ->exists();
            if ($hasDeviceCodeSerials) {
                $hasSerials = true;
            }
        }
        
        if (empty($unit)) {
            // Nếu không có đơn vị, nhưng có quantity > 1 và không có serials, coi là bulk item (giống đo lường)
            return !$hasSerials && $dispatchItem->quantity > 1;
        }
        
        $isMeasureUnit = in_array(strtolower(trim($unit)), ['cm', 'mét', 'm', 'kg', 'g', 'gram', 'lít', 'l', 'm2', 'm3', 'mm', 'km', 'lit', 'ml', 'dm', 'cuộn', 'cuon', 'hộp', 'hop', 'thùng', 'thung', 'bộ', 'bo', 'túi', 'goi', 'gói', 'tấm', 'mét tới']);
        
        $isImplementationMaterial = false;
        if ($dispatchItem->material && trim($dispatchItem->material->category) === 'Vật tư triển khai') {
            $isImplementationMaterial = true;
        } elseif ($dispatchItem->product && trim($dispatchItem->product->category) === 'Vật tư triển khai') {
            $isImplementationMaterial = true;
        } elseif ($dispatchItem->good && trim($dispatchItem->good->category) === 'Vật tư triển khai') {
            $isImplementationMaterial = true;
        }

        // Nếu là đơn vị đo lường HOẶC là hàng không serial với số lượng > 1 HOẶC là Vật tư triển khai
        return $isMeasureUnit || $isImplementationMaterial || (!$hasSerials && $dispatchItem->quantity > 1);
    }

    /**
     * Helper logic for greedy return of a measurement item
     * Xử lý trừ số lượng đo lường tham lam
     * Returns the amount actually reduced from THIS item.
     */
    private function returnMeasurementItemGreedy($dispatchItem, $amountToReduceRequested, $priorityReplacementId = null, $isUsedRecallInput = null, ?array $syncBackupReturnContext = null): float
    {
        // ... (Tính toán totalAtSite giữ nguyên như bước trước)
        $incomingReplacementAtSiteQty = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $dispatchItem->id)
            ->selectRaw('SUM(quantity - COALESCE(replacement_returned_quantity, 0)) as total')->value('total') ?? 0;
            
        $outgoingReplacementAtSiteQty = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
            ->selectRaw('SUM(quantity - COALESCE(original_returned_quantity, 0)) as total')->value('total') ?? 0;
            
        $outgoingReplacementSumQuantity = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
            ->sum('quantity') ?? 0;

        $returnedSoFar = \App\Models\DispatchReturn::where('dispatch_item_id', $dispatchItem->id)
            ->where('serial_number', 'MEASUREMENT')
            ->sum('quantity');
        // dispatch_items.quantity không bị trừ khi thu hồi đo lường — phải trừ dispatch_returns để tính đúng còn tại site
        $netQuantity = max(0.0, (float) $dispatchItem->quantity - (float) $returnedSoFar);

        $isUsedRecall = filter_var($isUsedRecallInput, FILTER_VALIDATE_BOOLEAN);

        // Thu hồi từ dòng hợp đồng/general:
        // phải cho phép thu hồi theo số lượng đang hiển thị trên hợp đồng (netQuantity),
        // sau đó mới đồng bộ phần deployment thay thế tương ứng (replacement_returned_quantity).
        if (
            !$isUsedRecall
            && in_array($dispatchItem->category, ['contract', 'general'], true)
        ) {
            $totalAtSite = (float) $netQuantity;
            if ($totalAtSite <= 0) {
                return 0;
            }

            $canReturnFromThis = min((float) $totalAtSite, (float) $amountToReduceRequested);
            $remainingToReduceNow = $canReturnFromThis;

            $deployedReplacementAtSite = (float) DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
                ->selectRaw('SUM(quantity - COALESCE(replacement_returned_quantity, 0)) as total')
                ->value('total');
            $unreplacedAtSite = max(0.0, (float) $netQuantity - $deployedReplacementAtSite);

            // 1) Thu hồi phần chưa bị thay thế trước (không chạm dispatch_replacements)
            if ($remainingToReduceNow > 0) {
                $reduceFromUnreplaced = min($unreplacedAtSite, (float) $remainingToReduceNow);
                $remainingToReduceNow -= $reduceFromUnreplaced;
            }

            // 2) Phần còn lại thu hồi vào các đơn vị đang là "hàng thay thế tại site"
            if ($remainingToReduceNow > 0) {
                $remainingToReduceNow = $this->reduceOutgoingReplacementDeploymentForContractReturn(
                    $dispatchItem,
                    $remainingToReduceNow,
                    $priorityReplacementId,
                    $syncBackupReturnContext
                );
            }

            $dispatchItem->save();
            return (float) ($canReturnFromThis - $remainingToReduceNow);
        }

        if ($isUsedRecall) {
            $totalAtSite = (float) $outgoingReplacementAtSiteQty;
        } elseif (
            $dispatchItem->category === 'backup'
            && (float) $outgoingReplacementSumQuantity < 0.0001
        ) {
            // Dự phòng thường: không cộng netQuantity + incoming (trùng tồn); tối đa thu = còn gắn phiếu
            $totalAtSite = (float) $netQuantity;
        } else {
            $totalAtSite = max(0.0, (float) ($netQuantity - (float) $outgoingReplacementSumQuantity + (float) $incomingReplacementAtSiteQty));
        }

        if ($totalAtSite <= 0) return 0;

        $canReturnFromThis = min((float)$totalAtSite, (float)$amountToReduceRequested);
        $remainingToReduceNow = $canReturnFromThis;

        if ($isUsedRecall) {
            $remainingToReduceNow = $this->reduceReplacementsForReturn($dispatchItem, $remainingToReduceNow, $priorityReplacementId, true);
        } else {
            // 1. Priority replacement
            if (!empty($priorityReplacementId)) {
                $remainingToReduceNow = $this->reduceReplacementsForReturn($dispatchItem, $remainingToReduceNow, $priorityReplacementId, false);
            }

            // 2. Main quantity (un-replaced)
            if ($remainingToReduceNow > 0) {
                $alreadyReplaced = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)->sum('quantity');
                $unReplaced = max(0.0, (float) $netQuantity - (float) $alreadyReplaced);
                
                $reduceFromQuantity = min($unReplaced, (float)$remainingToReduceNow);
                // KHÔNG trừ $dispatchItem->quantity trong DB - việc thu hồi đã được ghi nhận
                // trong bảng dispatch_returns, ProjectController sẽ tính toán từ đó.
                $remainingToReduceNow -= $reduceFromQuantity;
            }

            // 3. General replacements
            if ($remainingToReduceNow > 0) {
                $remainingToReduceNow = $this->reduceReplacementsForReturn($dispatchItem, $remainingToReduceNow, null, false);
            }
        }

        $dispatchItem->save();
        return (float)($canReturnFromThis - $remainingToReduceNow);
    }

    /**
     * Giảm deployment thay thế đang nằm trên hợp đồng khi thu hồi từ contract/general.
     * Ưu tiên replacement_id nếu user bấm ở dòng thay thế cụ thể.
     */
    private function reduceOutgoingReplacementDeploymentForContractReturn($dispatchItem, $qtyToReduce, $priorityReplacementId = null, ?array $syncBackupReturnContext = null): float
    {
        $totalToReduce = (float) $qtyToReduce;

        if (!empty($priorityReplacementId) && $totalToReduce > 0) {
            $priorityRep = DispatchReplacement::find($priorityReplacementId);
            if ($priorityRep && (int) $priorityRep->original_dispatch_item_id === (int) $dispatchItem->id) {
                $available = (float) $priorityRep->quantity - (float) ($priorityRep->replacement_returned_quantity ?? 0);
                if ($available > 0.0001) {
                    $canReduce = min($available, $totalToReduce);
                    $priorityRep->replacement_returned_quantity = (float) ($priorityRep->replacement_returned_quantity ?? 0) + $canReduce;
                    $priorityRep->save();
                    $this->createBackupDispatchReturnForReplacementRecall($priorityRep, $canReduce, $syncBackupReturnContext);
                    $totalToReduce -= $canReduce;
                }
            }
        }

        if ($totalToReduce <= 0) {
            return 0.0;
        }

        $reps = DispatchReplacement::where('original_dispatch_item_id', $dispatchItem->id)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($reps as $rep) {
            if ($totalToReduce <= 0) {
                break;
            }
            if (!empty($priorityReplacementId) && (int) $rep->id === (int) $priorityReplacementId) {
                continue;
            }

            $available = (float) $rep->quantity - (float) ($rep->replacement_returned_quantity ?? 0);
            if ($available <= 0.0001) {
                continue;
            }

            $canReduce = min($available, $totalToReduce);
            $rep->replacement_returned_quantity = (float) ($rep->replacement_returned_quantity ?? 0) + $canReduce;
            
            /** @var DispatchReplacement $rep */
            $rep->save();
            $this->createBackupDispatchReturnForReplacementRecall($rep, $canReduce, $syncBackupReturnContext);
            $totalToReduce -= $canReduce;
        }

        return (float) max(0, $totalToReduce);
    }

    /**
     * Ghi nhận thu hồi trên dòng dự phòng (nguồn xuất thay thế) tương ứng replacement_returned,
     * để idle dự phòng không nhảy (hàng đã về kho, không còn nằm idle trên phiếu dự phòng).
     */
    private function createBackupDispatchReturnForReplacementRecall(DispatchReplacement $rep, float $qty, ?array $syncBackupReturnContext): void
    {
        if ($qty <= 0.0001 || $syncBackupReturnContext === null) {
            return;
        }
        $backupItemId = (int) $rep->replacement_dispatch_item_id;
        if ($backupItemId <= 0) {
            return;
        }
        DispatchReturn::create([
            'return_code' => DispatchReturn::generateReturnCode(),
            'dispatch_item_id' => $backupItemId,
            'warehouse_id' => (int) $syncBackupReturnContext['warehouse_id'],
            'user_id' => (int) $syncBackupReturnContext['user_id'],
            'return_date' => Carbon::now(),
            'reason_type' => 'return',
            'reason' => ($syncBackupReturnContext['reason'] ?? '') . ' — Đồng bộ: phần xuất từ dự phòng để thay thế đã về kho (thu hồi hàng thay thế trên hợp đồng)',
            'condition' => 'good',
            'status' => 'completed',
            'serial_number' => 'MEASUREMENT',
            'quantity' => $qty,
        ]);
    }

    /**
     * Giảm số lượng trong các bản ghi DispatchReplacement mà item này đang là replacement (greedy reduction)
     */
    private function reduceReplacementsForReturn($dispatchItem, $qtyToReduce, $priorityReplacementId = null, $prioritizeOutgoing = false): float
    {
        $totalToReduce = (float)$qtyToReduce;
        
        $isUsedRecallContext = $prioritizeOutgoing;

        // 1. Ưu tiên trừ vào replacement_id cụ thể nếu có
        if (!empty($priorityReplacementId)) {
            $priorityRep = \App\Models\DispatchReplacement::find($priorityReplacementId);
            $isValid = false;
            $isItemTheOriginal = false;

            if ($priorityRep) {
                if ($isUsedRecallContext && $priorityRep->original_dispatch_item_id == $dispatchItem->id) {
                    $isValid = true;
                    $isItemTheOriginal = true;
                } elseif (!$isUsedRecallContext && $priorityRep->replacement_dispatch_item_id == $dispatchItem->id) {
                    $isValid = true;
                    $isItemTheOriginal = false;
                }
            }

            if ($isValid) {
                $returnedField = $isItemTheOriginal ? 'original_returned_quantity' : 'replacement_returned_quantity';
                
                $availableToReturn = (float)$priorityRep->quantity - (float)($priorityRep->$returnedField ?? 0);
                
                if ($availableToReturn > 0) {
                    $canReduce = min($availableToReturn, $totalToReduce);
                    $priorityRep->$returnedField = (float)($priorityRep->$returnedField ?? 0) + $canReduce;
                    $totalToReduce -= $canReduce;
                    /** @var \App\Models\DispatchReplacement $priorityRep */
                    $priorityRep->save();
                }
            }
        }
        
        if ($totalToReduce <= 0) return (float)$totalToReduce;


        // Xử lý đúng luồng theo ngữ cảnh (Chỉ outgoing nếu là hàng cũ, Chỉ incoming nếu là hàng hợp đồng)
        $targets = $isUsedRecallContext ? ['outgoing'] : ['incoming'];

        foreach ($targets as $type) {
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

                // Xác định hướng cho từng record cụ thể
                $isItemTheOriginal = ($rep->original_dispatch_item_id == $dispatchItem->id);
                $returnedField = $isItemTheOriginal ? 'original_returned_quantity' : 'replacement_returned_quantity';

                $availableToReturn = (float)$rep->quantity - (float)($rep->$returnedField ?? 0);
                if ($availableToReturn <= 0.0001) continue;

                $canReduce = min($availableToReturn, $totalToReduce);
                $rep->$returnedField = (float)($rep->$returnedField ?? 0) + $canReduce;
                $totalToReduce -= $canReduce;
                
                /** @var DispatchReplacement $rep */
                $rep->save();
            }
        }

        return (float)max(0, $totalToReduce);
    }

    /**
     * Tổng đơn vị đo lường đã thu hồi trên dispatch_item (phiếu MEASUREMENT).
     */
    private function measurementReturnedQtyForDispatchItem(int $dispatchItemId): float
    {
        return (float) DispatchReturn::where('dispatch_item_id', $dispatchItemId)
            ->where('serial_number', 'MEASUREMENT')
            ->sum('quantity');
    }

    /**
     * Số lượng hợp đồng còn gắn phiếu (trừ thu hồi) — mọi đơn vị trên line đều có thể đổi tiếp khi lỗi (kể cả hàng đã đổi từ dự phòng).
     */
    private function contractNetMeasurementQty(DispatchItem $item): float
    {
        return max(0.0, (float) $item->quantity - $this->measurementReturnedQtyForDispatchItem($item->id));
    }

    /**
     * Dự phòng còn lấy được để thay (đã trừ đã dùng thay thế & thu hồi từ dự phòng).
     */
    private function netBackupDispatchableQty(DispatchItem $bi): float
    {
        $usedOut = DispatchReplacement::where('replacement_dispatch_item_id', $bi->id)
            ->get()
            ->sum(function ($r) {
                return (float) $r->quantity - (float) ($r->replacement_returned_quantity ?? 0);
            });
        $ret = $this->measurementReturnedQtyForDispatchItem($bi->id);

        return max(0.0, (float) $bi->quantity - $usedOut - $ret);
    }

    /**
     * Dự phòng đo lường có thể gộp nhiều dispatch_item cùng mã; khi thu hồi chỉ trừ đúng các dòng trong merged_backup_dispatch_item_ids.
     *
     * @return \Illuminate\Support\Collection<int, DispatchItem>
     */
    private function resolveMeasurementBackupReturnTargets(Request $request, DispatchItem $dispatchItem, ?int $projectId, ?int $rentalId): \Illuminate\Support\Collection
    {
        $with = ['dispatch', 'material', 'product', 'good'];
        $raw = (string) $request->input('merged_backup_dispatch_item_ids', '');
        $mergedIds = array_values(array_unique(array_filter(array_map('intval', preg_split('/\s*,\s*/', $raw) ?: []))));
        // Lọc theo context Idle/Used của nút bấm
        if (count($mergedIds) === 0 || !in_array((int) $dispatchItem->id, $mergedIds, true)) {
            $initial = collect([DispatchItem::with($with)->findOrFail($dispatchItem->id)]);
        } else {
            $initial = DispatchItem::with($with)->whereIn('id', $mergedIds)->get();
        }

        $isUsedReq = filter_var($request->input('is_used'), FILTER_VALIDATE_BOOLEAN);
        
        $itemContextIds = $initial->filter(function($i) use ($isUsedReq) {
            $hasReplacements = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $i->id)->exists();
            return $isUsedReq ? $hasReplacements : !$hasReplacements;
        })->pluck('id')->toArray();

        if (empty($itemContextIds)) {
            return collect([]);
        }

        $q = DispatchItem::with($with)
            ->whereIn('id', $itemContextIds)
            ->where('item_type', $dispatchItem->item_type)
            ->where('item_id', $dispatchItem->item_id)
            ->where('category', 'backup');

        if ($projectId) {
            $q->whereHas('dispatch', fn ($dq) => $dq->where('project_id', $projectId));
        } elseif ($rentalId) {
            $rental = \App\Models\Rental::find($rentalId);
            if ($rental) {
                $q->whereHas('dispatch', function ($dq) use ($rental) {
                    $dq->where('dispatch_type', 'rental')
                        ->where(function ($qq) use ($rental) {
                            $qq->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                        });
                });
            } else {
                $q->whereRaw('1 = 0');
            }
        } else {
            $q->whereRaw('1 = 0');
        }

        $found = $q->orderBy('id')->get();
        $expectedSorted = collect($mergedIds)->sort()->values()->all();
        $gotSorted = $found->pluck('id')->sort()->values()->all();

        if ($found->count() !== count($mergedIds) || $expectedSorted !== $gotSorted) {
            return collect([DispatchItem::with($with)->findOrFail($dispatchItem->id)]);
        }

        return $found;
    }
}