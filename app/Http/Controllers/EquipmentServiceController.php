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
     * Xử lý yêu cầu thu hồi thiết bị
     */
    public function returnEquipment(Request $request)
    {
        // Validate request
        $validatedData = $request->validate([
            'equipment_id' => 'required|exists:dispatch_items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'condition' => 'required|in:good,damaged,broken',
        ], [
            'equipment_id.required' => 'Thiết bị không được để trống',
            'equipment_id.exists' => 'Thiết bị không tồn tại',
            'warehouse_id.required' => 'Kho không được để trống',
            'warehouse_id.exists' => 'Kho không tồn tại',
            'reason.required' => 'Lý do không được để trống',
            'condition.required' => 'Tình trạng thiết bị không được để trống',
            'condition.in' => 'Tình trạng thiết bị không hợp lệ',
        ]);

        DB::beginTransaction();
        try {
            // Lấy thông tin thiết bị
            $dispatchItem = DispatchItem::with('dispatch')->findOrFail($validatedData['equipment_id']);
            $warehouse = Warehouse::findOrFail($validatedData['warehouse_id']);

            // Tạo phiếu nhập trả
            $dispatchReturn = DispatchReturn::create([
                'return_code' => DispatchReturn::generateReturnCode(),
                'dispatch_item_id' => $dispatchItem->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => Auth::id() ?? 1,
                'return_date' => Carbon::now(),
                'reason_type' => 'return',
                'reason' => $validatedData['reason'],
                'condition' => $validatedData['condition'],
                'status' => 'completed',
            ]);

            // Cập nhật số lượng trong kho
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
                ". Tình trạng: " . match($validatedData['condition']) {
                    'good' => 'Hoạt động tốt',
                    'damaged' => 'Hư hỏng nhẹ',
                    'broken' => 'Hư hỏng nặng',
                    default => 'Không xác định'
                };
            $dispatchItem->save();

            // Lưu nhật ký thu hồi thành phẩm
            try {
                // Xác định loại thu hồi (dự án hoặc cho thuê)
                $description = '';
                $detailedInfo = [
                    'dispatch_return_id' => $dispatchReturn->id,
                    'dispatch_item_id' => $dispatchItem->id,
                    'dispatch_id' => $dispatchItem->dispatch->id,
                    'dispatch_code' => $dispatchItem->dispatch->dispatch_code,
                    'dispatch_type' => $dispatchItem->dispatch->dispatch_type,
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                    'reason' => $validatedData['reason'],
                    'condition' => $validatedData['condition'],
                    'condition_text' => match($validatedData['condition']) {
                        'good' => 'Hoạt động tốt',
                        'damaged' => 'Hư hỏng nhẹ',
                        'broken' => 'Hư hỏng nặng',
                        default => 'Không xác định'
                    },
                    'return_date' => $dispatchReturn->return_date->toDateTimeString(),
                    'returned_by' => Auth::id(),
                ];

                if ($dispatchItem->dispatch->dispatch_type === 'project') {
                    // Thu hồi từ dự án
                    $project = \App\Models\Project::find($dispatchItem->dispatch->project_id);
                    $description = 'Thu hồi từ dự án: ' . ($project ? $project->project_name : 'Không xác định');
                    $detailedInfo['project_id'] = $dispatchItem->dispatch->project_id;
                    $detailedInfo['project_name'] = $project ? $project->project_name : null;
                    $detailedInfo['project_code'] = $project ? $project->project_code : null;
                } elseif ($dispatchItem->dispatch->dispatch_type === 'rental') {
                    // Thu hồi từ cho thuê
                    $description = 'Thu hồi từ cho thuê: ' . $dispatchItem->dispatch->project_receiver;
                    $detailedInfo['rental_info'] = $dispatchItem->dispatch->project_receiver;
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
                    'Thu hồi thành phẩm - Lý do: ' . $validatedData['reason']
                );

                Log::info('Thu hồi thành phẩm - Đã lưu nhật ký', [
                    'return_code' => $dispatchReturn->return_code,
                    'item_code' => $item->code,
                    'dispatch_type' => $dispatchItem->dispatch->dispatch_type
                ]);

            } catch (\Exception $logException) {
                Log::error('Lỗi khi lưu nhật ký thu hồi thành phẩm', [
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
                    ->with('success', 'Thiết bị đã được thu hồi thành công.');
            } else {
                // Tìm rental_id từ dispatch note hoặc project_receiver
                $rentalCode = $dispatchItem->dispatch->project_receiver ?? $dispatchItem->dispatch->dispatch_note;
                return redirect()->back()->with('success', 'Thiết bị đã được thu hồi thành công.');
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
        ], [
            'equipment_id.required' => 'Thiết bị cần thay thế không được để trống',
            'equipment_id.exists' => 'Thiết bị cần thay thế không tồn tại',
            'replacement_device_id.required' => 'Thiết bị thay thế không được để trống',
            'replacement_device_id.exists' => 'Thiết bị thay thế không tồn tại',
            'reason.required' => 'Lý do thay thế không được để trống',
        ]);

        DB::beginTransaction();
        try {
            // Lấy thông tin thiết bị
            $originalItem = DispatchItem::with('dispatch')->findOrFail($validatedData['equipment_id']);
            $replacementItem = DispatchItem::with('dispatch')->findOrFail($validatedData['replacement_device_id']);

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
            $warranty = Warranty::where('dispatch_item_id', $originalItem->id)->first();
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

            DB::commit();

            // Redirect với thông báo thành công
            if ($originalItem->dispatch->dispatch_type == 'project') {
                return redirect()->route('projects.show', $originalItem->dispatch->project_id)
                    ->with('success', 'Thiết bị đã được thay thế thành công.');
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
                ->get();
            
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
                    ->get();
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
                    ->get();
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