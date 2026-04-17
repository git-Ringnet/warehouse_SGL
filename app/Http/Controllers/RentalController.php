<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\UserLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Helpers\DateHelper;
use App\Helpers\SerialDisplayHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RentalController extends Controller
{
    /**
     * Hiển thị danh sách phiếu cho thuê
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $warranty_status = $request->input('warranty_status');

        $query = Rental::with('customer');

        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'rental_code':
                        $query->where('rental_code', 'like', "%{$search}%");
                        break;
                    case 'rental_name':
                        $query->where('rental_name', 'like', "%{$search}%");
                        break;
                    case 'customer':
                        $query->whereHas('customer', function ($q) use ($search) {
                            $q->where('company_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('rental_code', 'like', "%{$search}%")
                        ->orWhere('rental_name', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($subq) use ($search) {
                            $subq->where('company_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            }
        }

        // Xử lý bộ lọc bảo hành
        if ($warranty_status) {
            switch ($warranty_status) {
                case 'active':
                    // Còn bảo hành: due_date > hiện tại
                    $query->where('due_date', '>', now()->toDateString());
                    break;
                case 'expired':
                    // Hết bảo hành: due_date <= hiện tại
                    $query->where('due_date', '<=', now()->toDateString());
                    break;
            }
        }

        $rentals = $query->latest()->paginate(10);

        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $rentals->appends([
            'search' => $search,
            'filter' => $filter,
            'warranty_status' => $warranty_status
        ]);

        return view('rentals.index', compact('rentals', 'search', 'filter', 'warranty_status'));
    }

    /**
     * Hiển thị form tạo phiếu cho thuê mới
     */
    public function create()
    {
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();
        return view('rentals.create', compact('customers', 'employees'));
    }

    /**
     * Lưu phiếu cho thuê mới vào database
     */
    public function store(Request $request)
    {
        // Chuyển đổi định dạng ngày từ dd/mm/yyyy sang yyyy-mm-dd
        $request->merge([
            'rental_date' => DateHelper::convertToDatabaseFormat($request->rental_date),
            'due_date' => DateHelper::convertToDatabaseFormat($request->due_date)
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'rental_code' => 'required|string|max:255|unique:rentals',
            'rental_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'rental_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:rental_date',
            'notes' => 'nullable|string',
        ], [
            'rental_code.required' => 'Mã phiếu cho thuê không được để trống',
            'rental_code.unique' => 'Mã phiếu cho thuê đã tồn tại',
            'rental_name.required' => 'Tên phiếu cho thuê không được để trống',
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'employee_id.exists' => 'Nhân viên phụ trách không tồn tại',
            'rental_date.required' => 'Ngày cho thuê không được để trống',
            'rental_date.date' => 'Ngày cho thuê không hợp lệ',
            'due_date.required' => 'Ngày hẹn trả không được để trống',
            'due_date.date' => 'Ngày hẹn trả không hợp lệ',
            'due_date.after_or_equal' => 'Ngày hẹn trả phải sau hoặc bằng ngày cho thuê',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Tạo phiếu cho thuê mới
            $rental = Rental::create([
                'rental_code' => $request->rental_code,
                'rental_name' => $request->rental_name,
                'customer_id' => $request->customer_id,
                'employee_id' => $request->employee_id,
                'rental_date' => $request->rental_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // Ghi nhật ký tạo mới phiếu cho thuê
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'rentals',
                    'Tạo mới phiếu cho thuê: ' . $rental->rental_code,
                    null,
                    $rental->toArray()
                );
            }

            // Gửi thông báo cho khách hàng
            $customerUsers = \App\Models\User::where('customer_id', $rental->customer_id)->where('active', true)->get();
            foreach ($customerUsers as $user) {
                Notification::createNotification(
                    'Phiếu cho thuê mới',
                    'Phiếu cho thuê #' . $rental->rental_code . ' đã được tạo cho đơn vị của bạn.',
                    'info',
                    $user->id,
                    'rental',
                    $rental->id,
                    route('customer.dashboard'),
                    null,
                    'customer'
                );
            }

            return redirect()->route('rentals.index')
                ->with('success', 'Phiếu cho thuê đã được thêm thành công.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị chi tiết phiếu cho thuê
     */
    public function show($id)
    {
        $rental = Rental::with(['customer'])->findOrFail($id);
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();

        // Lấy danh sách thiết bị theo hợp đồng với chi tiết từng thiết bị
        $contractItems = collect();
        // Lấy TẤT CẢ các phiếu xuất liên quan đến phiếu thuê (để dùng cho phần dự phòng & thu hồi)
        $allDispatches = \App\Models\Dispatch::whereIn('dispatch_type', ['rental', 'warranty'])
            ->whereIn('status', ['approved', 'completed'])
            ->where('project_id', $rental->id)
            ->get();

        // Lọc riêng các phiếu xuất dùng cho danh sách 'Hợp đồng' (tránh hiện các phiếu lắp ráp/kiểm thử trùng lặp)
        $contractDispatches = $allDispatches->filter(function($d) {
            // Phiếu thuê thường dùng phiếu xuất trực tiếp hoặc phiếu từ dự án
            // Bỏ qua các phiếu lắp ráp/kiểm thử nếu chúng không phải là phiếu xuất chính cho khách hàng
            if (strpos($d->dispatch_note ?? '', 'Sinh từ phiếu lắp ráp') !== false || 
                strpos($d->dispatch_note ?? '', 'Sinh từ phiếu kiểm thử') !== false) {
                return false;
            }
            return true;
        });

        // Lấy tất cả Item IDs liên quan trực tiếp đến các phiếu xuất của phiếu thuê (dùng bản gốc ALL)
        $allItemIdsRaw = $allDispatches->map(function($d) {
            return $d->items->pluck('id');
        })->flatten()->unique()->toArray();


        // Tìm tất cả các record thay đổi (replacements) liên quan đến các item này
        $replacements = \App\Models\DispatchReplacement::whereIn('original_dispatch_item_id', $allItemIdsRaw)
            ->orWhereIn('replacement_dispatch_item_id', $allItemIdsRaw)
            ->get();

        // Tổng hợp số lượng thay thế cho hàng đo lường (Trừ đi phần đã thu hồi của hàng cũ)
        $replacementTotals = $replacements->where('original_serial', 'MEASUREMENT')
            ->groupBy('original_dispatch_item_id')
            ->map(function($group) {
                return $group->sum(function($r) {
                    return (float)$r->quantity - (float)($r->original_returned_quantity ?? 0);
                });
            });

        // Lấy danh sách tất cả Item IDs để tính toán thu hồi
        $allItemIds = array_unique(array_merge(
            $allItemIdsRaw,
            $replacements->pluck('original_dispatch_item_id')->toArray(),
            $replacements->pluck('replacement_dispatch_item_id')->toArray()
        ));

        $returnTotals = \App\Models\DispatchReturn::whereIn('dispatch_item_id', $allItemIds)
            ->where('serial_number', 'MEASUREMENT')
            ->get()
            ->groupBy('dispatch_item_id')
            ->map(function($group) {
                return $group->sum('quantity');
            });


        // Track already processed dispatch_item_id to avoid duplication
        $processedDispatchItemIds = [];

        foreach ($contractDispatches as $dispatch) {

            $items = $dispatch->items()->where('category', 'contract')->get();

            foreach ($items as $item) {
                // Skip if this item ID has already been processed in this loop
                if (in_array($item->id, $processedDispatchItemIds)) {
                    continue;
                }
                
                $processedDispatchItemIds[] = $item->id;

                $serialNumbers = $item->serial_numbers ?? [];
                $rawSerialNumbers = array_values(array_filter(array_map(function ($s) {
                    return trim((string) $s);
                }, is_array($item->serial_numbers) ? $item->serial_numbers : [])));
                $serialNumbersFromDeviceCodes = false;

                // Fallback: chỉ lấy serial từ device_codes khi dispatch_items.serial_numbers thực sự rỗng.
                // Nếu item đang chứa virtual serial (N/A-xxx), đó là trạng thái active sau thay thế
                // và KHÔNG được map ngược sang serial cũ từ device_codes.
                $quantity = (int) ($item->quantity ?? 1);
                if (empty($serialNumbers) && $quantity > 0) {
                    $deviceCodeSerials = \App\Models\DeviceCode::where('dispatch_id', $dispatch->id)
                        ->where('item_id', $item->item_id)
                        ->where('item_type', $item->item_type)
                        ->pluck('serial_main')
                        ->filter()
                        ->values()
                        ->toArray();
                    if (!empty($deviceCodeSerials)) {
                        $serialNumbers = $deviceCodeSerials;
                        $serialNumbersFromDeviceCodes = true;
                    }
                }

                $unit = $item->item_type === 'material' ? ($item->material->unit ?? 'Cái') : ($item->item_type === 'product' ? 'Cái' : ($item->good->unit ?? 'Cái'));
                $unitLower = strtolower(trim($unit));
                $measurementUnits = ['cm', 'mét', 'm', 'kg', 'g', 'gram', 'lít', 'l', 'm2', 'm3', 'mm', 'km', 'lit', 'ml', 'dm', 'cuộn', 'cuon', 'hộp', 'hop', 'thùng', 'thung', 'bộ', 'bo', 'túi', 'goi', 'gói', 'tấm', 'mét tới'];
                $isMeasurementUnit = in_array($unitLower, $measurementUnits);

                $isImplementationMaterial = false;
                if ($item->item_type === 'good' && $item->good) {
                    $isImplementationMaterial = (trim($item->good->category) === 'Vật tư triển khai');
                }

                // Coi là hàng đo lường/bulk nếu đơn vị thuộc danh sách HOẶC là Vật tư triển khai HOẶC không có serial và số lượng > 1
                $isBulkOrMeasure = $isMeasurementUnit || $isImplementationMaterial || (empty($serialNumbers) && $item->quantity > 1);

                if ($isBulkOrMeasure) {
                    // Skip if quantity is 0 (removed or fully recalled)
                    if ($item->quantity <= 0) {
                        continue;
                    }

                    $replacementQty = $replacementTotals->get($item->id, 0);
                    $returnQty = $returnTotals->get($item->id, 0);
                    
                    // Hiển thị theo hợp đồng: tổng còn gắn với phiếu (trừ thu hồi), không trừ phần đã thay thế.
                    $displayQty = max(0.0, (float)$item->quantity - (float)$returnQty);
                    $remainingAtSiteQty = max(0.0, (float)$item->quantity - (float)$returnQty - (float)$replacementQty);

                    if ($displayQty <= 0.0001) {
                        continue;
                    }

                    $contractItems->push([
                        'dispatch_item' => $item,
                        'dispatch' => $dispatch,
                        'serial_index' => 0,
                        'serial_number' => 'MEASUREMENT',
                        'has_serial' => false,
                        'is_measurement_unit' => true,
                        'unit' => $unit,
                        'override_quantity' => $displayQty,
                        'replaced_quantity' => $replacementQty,
                        'returned_quantity' => $returnQty,
                        'original_quantity' => $item->quantity,
                        'is_partially_replaced' => ($replacementQty > 0),
                        'used_quantity' => $replacementQty,
                        'available_quantity' => $remainingAtSiteQty
                    ]);
                    continue;
                }

                // Tạo bản ghi cho TẤT CẢ serial (bao gồm cả virtual serial đã lưu trong DB)
                foreach ($serialNumbers as $i => $serial) {
                    $serial = trim($serial);
                    if (!empty($serial)) {
                        // Bỏ qua nếu serial này đã bị thu hồi
                        $isAlreadyReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                            ->where(function($q) use ($serial, $dispatch, $item, $serialNumbersFromDeviceCodes) {
                                $q->where('serial_number', $serial);
                                
                                // Cũng check virtual serial tương ứng (trường hợp serial thực từ device_codes)
                                if (!$serialNumbersFromDeviceCodes && !\App\Helpers\SerialHelper::isVirtualSerial((string)$serial)) {
                                    $dc = DB::table('device_codes')
                                        ->where('dispatch_id', $dispatch->id)
                                        ->where('item_id', $item->item_id)
                                        ->where('item_type', $item->item_type)
                                        ->where('serial_main', $serial)
                                        ->first();
                                    if ($dc && !empty($dc->old_serial)) {
                                        $q->orWhere('serial_number', $dc->old_serial);
                                    }
                                }

                                // Ngược lại: nếu serial hiện tại là virtual nhưng trong DB có thể đã lưu serial thực
                                if (!$serialNumbersFromDeviceCodes && \App\Helpers\SerialHelper::isVirtualSerial((string)$serial)) {
                                    $dc = DB::table('device_codes')
                                        ->where('dispatch_id', $dispatch->id)
                                        ->where('item_id', $item->item_id)
                                        ->where('item_type', $item->item_type)
                                        ->where('old_serial', $serial)
                                        ->first();
                                    if (is_object($dc) && isset($dc->serial_main) && !empty($dc->serial_main)) {
                                        $q->orWhere('serial_number', $dc->serial_main);
                                    }
                                }
                            })
                            ->exists();
                        // Nếu serial vẫn đang nằm trong serial_numbers gốc của item,
                        // coi như serial đang active tại hợp đồng (tránh ẩn nhầm do lịch sử virtual serial cũ).
                        if ($isAlreadyReturned && in_array($serial, $rawSerialNumbers, true)) {
                            $isAlreadyReturned = false;
                        }
                        if ($isAlreadyReturned) continue;

                        $isVirtual = strpos($serial, 'N/A-') === 0;

                        $contractItems->push([
                            'dispatch_item' => $item,
                            'dispatch' => $dispatch,
                            'serial_index' => $i,
                            'serial_number' => $serial,
                            'has_serial' => !$isVirtual
                        ]);
                    }
                }

            }
        }

        // Lấy danh sách thiết bị dự phòng cho bảo hành/thay thế
        $backupItems = collect();
        $processedBackupItemIds = [];

        foreach ($allDispatches as $dispatch) {
            $items = $dispatch->items()->where('category', 'backup')->get();

            foreach ($items as $item) {
                $serialNumbers = $item->serial_numbers ?? [];

                $unit = $item->item_type === 'material' ? ($item->material->unit ?? 'Cái') : ($item->item_type === 'product' ? 'Cái' : ($item->good->unit ?? 'Cái'));
                $unitLower = strtolower(trim($unit));
                $measurementUnits = ['cm', 'mét', 'm', 'kg', 'g', 'gram', 'lít', 'l', 'm2', 'm3', 'mm', 'km', 'lit', 'ml', 'dm', 'cuộn', 'cuon', 'hộp', 'hop', 'thùng', 'thung', 'bộ', 'bo', 'túi', 'goi', 'gói', 'tấm', 'mét tới'];
                $isMeasurementUnit = in_array($unitLower, $measurementUnits);

                $isImplementationMaterial = false;
                if ($item->item_type === 'good' && $item->good) {
                    $isImplementationMaterial = (trim($item->good->category) === 'Vật tư triển khai');
                }

                // Coi là hàng đo lường/bulk nếu đơn vị thuộc danh sách HOẶC là Vật tư triển khai HOẶC không có serial và số lượng > 1
                $isBulkOrMeasure = $isMeasurementUnit || $isImplementationMaterial || (empty($serialNumbers) && $item->quantity > 1);

                if ($isBulkOrMeasure) {
                    // Tính số lượng đã sử dụng để thay thế (incoming) - Trừ đi phần đã thu hồi của hàng thay thế
                    $usedQtyIn = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                        ->get()
                        ->sum(function($r) {
                            return (float)$r->quantity - (float)($r->replacement_returned_quantity ?? 0);
                        });
                    
                    $returnQty = $returnTotals->get($item->id, 0);
                    
                    // Số lượng khả dụng (Idle) = Tổng - Đã dùng thay thế - Đã thu hồi (phần còn lại chưa dùng và chưa trả)
                    $availableQty = max(0.0, (float)$item->quantity - (float)$usedQtyIn - (float)$returnQty);

                    // Không skip nữa, để hiện cả hàng đã dùng để thu hồi hàng cũ
                    $backupItems->push([
                        'dispatch_item' => $item,
                        'dispatch' => $dispatch,
                        'serial_index' => 0,
                        'serial_number' => 'MEASUREMENT',
                        'has_serial' => false,
                        'is_measurement_unit' => true,
                        'unit' => $unit,
                        'used_quantity' => $usedQtyIn,
                        'available_quantity' => $availableQty,
                        'override_quantity' => $availableQty,
                        'returnable_max_quantity' => (float) $availableQty,
                    ]);
                    continue;

                }



                // Tạo bản ghi cho TẤT CẢ serial (bao gồm cả virtual serial đã lưu trong DB)
                if (!empty($serialNumbers) && is_array($serialNumbers)) {
                    foreach ($serialNumbers as $i => $serial) {
                        $serial = trim($serial);
                        if (!empty($serial)) {
                            // BỎ QUA nếu serial này đã được sử dụng để thay thế
                            $isUsedForReplacement = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                                ->where('replacement_serial', $serial)
                                ->exists();
                            if ($isUsedForReplacement) {
                                continue;
                            }
                            
                            $isVirtual = strpos($serial, 'N/A-') === 0;

                            $backupItems->push([
                                'dispatch_item' => $item,
                                'dispatch' => $dispatch,
                                'serial_index' => $i,
                                'serial_number' => $serial,
                                'has_serial' => !$isVirtual
                            ]);
                        }
                    }
                } else {
                    // Xử lý cho hàng backup ko có serial
                    $usedQtyInRegular = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                        ->sum('quantity');
                    $returnQtyRegular = $returnTotals->get($item->id, 0);
                    $availableQtyRegular = max(0.0, (float)$item->quantity - (float)$usedQtyInRegular - (float)$returnQtyRegular);

                    if ($availableQtyRegular > 0.0001) {
                        for ($i = 0; $i < $availableQtyRegular; $i++) {
                            $backupItems->push([
                                'dispatch_item' => $item,
                                'dispatch' => $dispatch,
                                'serial_index' => $i,
                                'serial_number' => 'N/A',
                                'has_serial' => false
                            ]);
                        }
                    }
                }

            }
        }

        // Ghi nhật ký xem chi tiết phiếu cho thuê
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'rentals',
                'Xem chi tiết phiếu cho thuê: ' . $rental->rental_code,
                null,
                $rental->toArray()
            );
        }

        // Thêm các thiết bị được thay thế (từ dispatch_replacements) cho đơn vị đo lường
        // Tách riêng các dòng thay thế và xử lý outgoingQty theo FIFO
        // Đã gỡ bỏ block check Orig == Rep để khấu trừ đúng cho nested replacements (Hàng thay thế bị thay thế tiếp)
        $remainingOutgoing = $replacements->groupBy('original_dispatch_item_id')
            ->map(function($group) {
                return $group->sum('quantity');
            })->toArray();

        foreach ($replacements as $replacement) {
            // Chỉ hiển thị hàng đo lường ở phần "Hàng thay thế" thêm vào. 
            // Hàng Serial đã được tráo đổi trực tiếp vào danh sách hàng chính (Loop 1), không đẩy thêm vào đây để tránh trùng lặp (ghost row).
            if ($replacement->replacement_serial !== 'MEASUREMENT') continue;

            $replacementItem = $replacement->replacementDispatchItem;
            if (!$replacementItem) continue;

            $unit = $replacementItem->item_type === 'material' ? ($replacementItem->material->unit ?? 'Cái') : ($replacementItem->item_type === 'product' ? 'Cái' : ($replacementItem->good->unit ?? 'Cái'));
            $unitLower = strtolower(trim($unit));
            $measurementUnits = ['cm', 'mét', 'm', 'kg', 'g', 'gram', 'lít', 'l', 'm2', 'm3', 'mm', 'km', 'lit', 'ml', 'dm', 'cuộn', 'cuon', 'hộp', 'hop', 'thùng', 'thung', 'bộ', 'bo', 'túi', 'goi', 'gói', 'tấm', 'mét tới'];
            $isMeasurementUnit = in_array($unitLower, $measurementUnits);
            
            $itemOutgoingQty = $remainingOutgoing[$replacement->replacement_dispatch_item_id] ?? 0;
            $deduction = min((float)$replacement->quantity, (float)$itemOutgoingQty);
            $displayReplacementQty = (float)$replacement->quantity - $deduction;
            
            // Update remaining outgoing for this item
            if (isset($remainingOutgoing[$replacement->replacement_dispatch_item_id])) {
                $remainingOutgoing[$replacement->replacement_dispatch_item_id] -= $deduction;
            }

            $isImplementationMaterial = false;
            if ($replacementItem->item_type === 'good' && $replacementItem->good) {
                $isImplementationMaterial = (trim($replacementItem->good->category) === 'Vật tư triển khai');
            }

            // Chặt chẽ hơn: Chỉ coi là hàng đo lường nếu thực tế là đơn vị đo lường hoặc vật tư triển khai
            $isBulkOrMeasure = $isMeasurementUnit || $isImplementationMaterial;

            // Tính số lượng "đang thay thế" thực tế tại site (trừ đi phần đã thu hồi của hàng thay thế)
            $availableQtyAtSite = (float)$displayReplacementQty - (float)($replacement->replacement_returned_quantity ?? 0);

            if ($isBulkOrMeasure && $availableQtyAtSite > 0) {
                // Hiển thị từng bản ghi thay thế riêng biệt theo yêu cầu của user
                $contractItems->push([
                    'dispatch_item' => $replacementItem,
                    'dispatch' => $replacementItem->dispatch,
                    'serial_index' => 0,
                    'serial_number' => 'REPLACEMENT',
                    'has_serial' => false,
                    'is_measurement_unit' => true,
                    'unit' => $unit,
                    'override_quantity' => $availableQtyAtSite, 
                    'is_replacement' => true, 
                    'is_used' => false,
                    'replacement_id' => $replacement->id,
                    // Thêm thông tin dùng-thu để hiển thị label "Sử dụng một phần"
                    'used_quantity' => $itemOutgoingQty,
                    'available_quantity' => $availableQtyAtSite
                ]);
            }
        }

        // Thêm hàng cũ bị thay thế vào danh sách dự phòng (để có thể thu hồi)
        foreach ($replacements as $replacement) {
            $originalItem = $replacement->originalDispatchItem;
            if (!$originalItem) continue;

            $origUnit = $originalItem->item_type === 'material' ? ($originalItem->material->unit ?? 'Cái') : ($originalItem->item_type === 'product' ? 'Cái' : ($originalItem->good->unit ?? 'Cái'));
            $unitLower = strtolower(trim($origUnit));
            $measurementUnits = ['cm', 'mét', 'm', 'kg', 'g', 'gram', 'lít', 'l', 'm2', 'm3', 'mm', 'km', 'lit', 'ml', 'dm', 'cuộn', 'cuon', 'hộp', 'hop', 'thùng', 'thung', 'bộ', 'bo', 'túi', 'goi', 'gói', 'tấm', 'mét tới'];
            $isMeasurementUnit = in_array($unitLower, $measurementUnits);
            
            $isImplementationMaterial = false;
            if ($originalItem->item_type === 'good' && $originalItem->good) {
                $isImplementationMaterial = (trim($originalItem->good->category) === 'Vật tư triển khai');
            }
            
            $isBulkOrMeasure = $isMeasurementUnit || $isImplementationMaterial || (empty($originalItem->serial_numbers) && $replacement->quantity > 0);
            
            $availableUsedQtyAtSite = (float) $replacement->quantity - (float) ($replacement->original_returned_quantity ?? 0);
            
            if ($isBulkOrMeasure && $availableUsedQtyAtSite > 0) {
                $backupItems->push([
                    'dispatch_item' => $originalItem,
                    'dispatch' => $originalItem->dispatch,
                    'serial_index' => 0,
                    'serial_number' => 'MEASUREMENT',
                    'has_serial' => false,
                    'is_measurement_unit' => true,
                    'unit' => $origUnit,
                    'override_quantity' => $availableUsedQtyAtSite,
                    'is_used' => true, // Đánh dấu là đã sử dụng (hàng cũ bị thay thế)
                    'replacement_id' => $replacement->id
                ]);
            }
            // Hàng serial: KHÔNG push thêm vì replaceEquipment đã swap serial vào backup item
        }

        // --- Bắt đầu phần Gộp hàng hóa đo lường trùng lặp ---
        // 1. Gom nhóm cho hàng hóa theo hợp đồng (contractItems)
        $groupedContractItems = collect();
        $measurementContractGroups = [];

        // Tính tổng số lượng hàng hóa gốc còn lại để hiển thị nhãn cho hàng thay thế
        $totalOriginalAvailable = [];
        foreach ($contractItems as $item) {
            $itemArray = (array)$item;
            if (!empty($itemArray['is_measurement_unit']) && empty($itemArray['is_replacement'])) {
                $di = $itemArray['dispatch_item'];
                $code = $di->item_type === 'material' ? ($di->material->code ?? '') : ($di->item_type === 'product' ? ($di->product->code ?? '') : ($di->good->code ?? ''));
                if (!isset($totalOriginalAvailable[$code])) $totalOriginalAvailable[$code] = 0;
                $totalOriginalAvailable[$code] += (float)($itemArray['available_quantity'] ?? 0);
            }
        }

        foreach ($contractItems as $item) {
            $itemArray = (array)$item;
            if (!empty($itemArray['is_measurement_unit'])) {
                $di = $itemArray['dispatch_item'];
                $unit = $itemArray['unit'] ?? '';
                $isReplacement = !empty($itemArray['is_replacement']) ? '1' : '0';
                $status = 'available';
                $overrideQty = (float)($itemArray['override_quantity'] ?? 0);
                $replacedQty = (float)($itemArray['replaced_quantity'] ?? 0);
                $availQty = (float)($itemArray['available_quantity'] ?? 0);
                
                if ($isReplacement === '1') {
                    $code = $di->item_type === 'material' ? ($di->material->code ?? '') : ($di->item_type === 'product' ? ($di->product->code ?? '') : ($di->good->code ?? ''));
                    $origAvail = $totalOriginalAvailable[$code] ?? 0;
                    if ($origAvail <= 0) {
                        $itemArray['is_fully_replaced_label'] = true;
                    }
                }

                // Group by type, id, unit, and replacement status to show "Original" and "Replacement" rows separately
                $key = "{$di->item_type}_{$di->item_id}_" . strtolower(trim($unit)) . "_{$isReplacement}";
                
                if (isset($measurementContractGroups[$key])) {
                    $measurementContractGroups[$key]['override_quantity'] = (float)($measurementContractGroups[$key]['override_quantity'] ?? 0) + $overrideQty;
                    $measurementContractGroups[$key]['replaced_quantity'] = (float)($measurementContractGroups[$key]['replaced_quantity'] ?? 0) + $replacedQty;
                    $measurementContractGroups[$key]['available_quantity'] = (float)($measurementContractGroups[$key]['available_quantity'] ?? 0) + $availQty;
                    $measurementContractGroups[$key]['returned_quantity'] = (float)($measurementContractGroups[$key]['returned_quantity'] ?? 0) + (float)($itemArray['returned_quantity'] ?? 0);
                    $measurementContractGroups[$key]['original_quantity'] = (float)($measurementContractGroups[$key]['original_quantity'] ?? 0) + (float)($itemArray['original_quantity'] ?? 0);
                    
                    if (!empty($itemArray['is_partially_replaced'])) {
                        $measurementContractGroups[$key]['is_partially_replaced'] = true;
                    }
                } else {
                    $measurementContractGroups[$key] = $itemArray;
                    $measurementContractGroups[$key]['override_quantity'] = $overrideQty;
                    $measurementContractGroups[$key]['replaced_quantity'] = $replacedQty;
                    $measurementContractGroups[$key]['available_quantity'] = $availQty;
                }
            } else {
                // Thêm logic ẩn item không đo lường nếu đã bị thay thế hoàn toàn
                $overrideQty = (float)($itemArray['override_quantity'] ?? 0);
                $replacedQty = (float)($itemArray['replaced_quantity'] ?? 0);
                $isReplacement = !empty($itemArray['is_replacement']) ? '1' : '0';
                
                if ($isReplacement === '0' && $overrideQty <= 0 && $replacedQty > 0) {
                    continue; // Bỏ qua hiển thị dòng gốc không đo lường nếu đã thay thế hoàn toàn
                }
                
                $groupedContractItems->push($itemArray);
            }
        }
        // 2. Gom nhóm cho hàng hóa dự phòng (backupItems) trước để lấy số lượng rảnh gộp vào hợp đồng
        $groupedBackupItems = collect();
        $measurementBackupGroups = [];

        foreach ($backupItems as $item) {
            $itemArray = (array)$item;
            if (!empty($itemArray['is_measurement_unit'])) {
                $di = $itemArray['dispatch_item'];
                $unit = $itemArray['unit'] ?? '';
                $isUsed = !empty($itemArray['is_used']) ? '1' : '0';
                // Hàng lỗi: mỗi replacement một dòng. Dự phòng pool: gộp cùng mã + đơn vị trong dự án (như hợp đồng); thu hồi dùng merged_dispatch_item_ids
                if ($isUsed === '1') {
                    $key = 'meas_bu_' . (int) ($itemArray['replacement_id'] ?? 0) . '_' . (int) $di->id;
                } else {
                    $key = "{$di->item_type}_{$di->item_id}_" . strtolower(trim($unit)) . '_0';
                }
                
                if (isset($measurementBackupGroups[$key])) {
                    if (isset($itemArray['override_quantity'])) {
                        $measurementBackupGroups[$key]['override_quantity'] = (float)($measurementBackupGroups[$key]['override_quantity'] ?? 0) + (float)$itemArray['override_quantity'];
                    }
                    if (isset($itemArray['available_quantity'])) {
                        $measurementBackupGroups[$key]['available_quantity'] = (float)($measurementBackupGroups[$key]['available_quantity'] ?? 0) + (float)$itemArray['available_quantity'];
                    }
                    if (isset($itemArray['used_quantity'])) {
                        $measurementBackupGroups[$key]['used_quantity'] = (float)($measurementBackupGroups[$key]['used_quantity'] ?? 0) + (float)$itemArray['used_quantity'];
                    }
                    if (isset($itemArray['returnable_max_quantity'])) {
                        $measurementBackupGroups[$key]['returnable_max_quantity'] = (float)($measurementBackupGroups[$key]['returnable_max_quantity'] ?? 0) + (float)$itemArray['returnable_max_quantity'];
                    }
                    if ($isUsed === '0') {
                        if ((int) $di->id < (int) $measurementBackupGroups[$key]['dispatch_item']->id) {
                            $measurementBackupGroups[$key]['dispatch_item'] = $di;
                            $measurementBackupGroups[$key]['dispatch'] = $itemArray['dispatch'];
                        }
                        $measurementBackupGroups[$key]['merged_dispatch_item_ids'][] = (int) $di->id;
                        $measurementBackupGroups[$key]['merged_dispatch_item_ids'] = array_values(array_unique($measurementBackupGroups[$key]['merged_dispatch_item_ids']));
                    }
                } else {
                    $measurementBackupGroups[$key] = $itemArray;
                    if ($isUsed === '0') {
                        $measurementBackupGroups[$key]['merged_dispatch_item_ids'] = [(int) $di->id];
                    }
                }
            } else {
                $groupedBackupItems->push($itemArray);
            }
        }
        foreach ($measurementBackupGroups as $groupedItem) {
            $isUsedRow = !empty($groupedItem['is_used']);
            if (!empty($groupedItem['is_measurement_unit']) && !$isUsedRow) {
                $idle = (float)($groupedItem['override_quantity'] ?? 0);
                $used = (float)($groupedItem['used_quantity'] ?? 0);
                if ($idle > 0.0001) {
                    $groupedBackupItems->push($groupedItem);
                }
            } elseif ((float)($groupedItem['override_quantity'] ?? 0) > 0) {
                $groupedBackupItems->push($groupedItem);
            }
        }
        $backupItems = $groupedBackupItems;

        // 3. Xử lý gộp cho hàng hợp đồng (Contract) 
        foreach ($measurementContractGroups as $groupedItem) {
            $isReplacement = !empty($groupedItem['is_replacement']) ? '1' : '0';
            $overrideQty = (float)($groupedItem['override_quantity'] ?? 0);
            $replacedQty = (float)($groupedItem['replaced_quantity'] ?? 0);
            $availQty = (float)($groupedItem['available_quantity'] ?? 0);
            
            // Ẩn dòng gốc chỉ khi đã thu hồi hết khỏi hợp đồng (không ẩn vì đã thay thế hết — tổng hợp đồng vẫn hiển thị)
            if ($isReplacement === '0' && $overrideQty <= 0 && $replacedQty > 0 && $availQty <= 0) {
                continue;
            }
            
            // TÍNH TOÁN LẠI MAX QTY: Hàng hợp đồng cho phép thu hồi TỔNG (bản thân nó + dự phòng rảnh)
            if ($isReplacement === '0') {
                $di = $groupedItem['dispatch_item'];
                $unit = $groupedItem['unit'] ?? '';
                $backupKey = "{$di->item_type}_{$di->item_id}_" . strtolower(trim($unit)) . '_0';
                
                if (isset($measurementBackupGroups[$backupKey])) {
                    $backupIdleQty = (float)($measurementBackupGroups[$backupKey]['override_quantity'] ?? 0);
                    $groupedItem['returnable_max_quantity'] = (float)($groupedItem['returnable_max_quantity'] ?? 0) + $backupIdleQty;
                }
            }
            
            $groupedContractItems->push($groupedItem);
        }
        $contractItems = $groupedContractItems;

        $backupItems = $groupedBackupItems;
        // --- Kết thúc phần Gộp hàng hóa đo lường trùng lặp ---

        return view('rentals.show', compact('rental', 'warehouses', 'backupItems', 'contractItems'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu cho thuê
     */
    public function edit($id)
    {
        $rental = Rental::with(['customer'])->findOrFail($id);
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();

        return view('rentals.edit', compact('rental', 'customers', 'employees'));
    }

    /**
     * Cập nhật phiếu cho thuê trong database
     */
    public function update(Request $request, $id)
    {
        // Chuyển đổi định dạng ngày từ dd/mm/yyyy sang yyyy-mm-dd
        $request->merge([
            'rental_date' => DateHelper::convertToDatabaseFormat($request->rental_date),
            'due_date' => DateHelper::convertToDatabaseFormat($request->due_date)
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'rental_code' => 'required|string|max:255|unique:rentals,rental_code,' . $id,
            'rental_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'rental_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:rental_date',
            'notes' => 'nullable|string',
        ], [
            'rental_code.required' => 'Mã phiếu cho thuê không được để trống',
            'rental_code.unique' => 'Mã phiếu cho thuê đã tồn tại',
            'rental_name.required' => 'Tên phiếu cho thuê không được để trống',
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'employee_id.exists' => 'Nhân viên phụ trách không tồn tại',
            'rental_date.required' => 'Ngày cho thuê không được để trống',
            'rental_date.date' => 'Ngày cho thuê không hợp lệ',
            'due_date.required' => 'Ngày hẹn trả không được để trống',
            'due_date.date' => 'Ngày hẹn trả không hợp lệ',
            'due_date.after_or_equal' => 'Ngày hẹn trả phải sau hoặc bằng ngày cho thuê',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Cập nhật phiếu cho thuê
            $rental = Rental::findOrFail($id);

            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $rental->toArray();

            $rental->update([
                'rental_code' => $request->rental_code,
                'rental_name' => $request->rental_name,
                'customer_id' => $request->customer_id,
                'employee_id' => $request->employee_id,
                'rental_date' => $request->rental_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // Ghi nhật ký cập nhật phiếu cho thuê
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'rentals',
                    'Cập nhật phiếu cho thuê: ' . $rental->rental_code,
                    $oldData,
                    $rental->toArray()
                );
            }

            // Đồng bộ thông tin warranty khi rental thay đổi
            $this->syncWarrantiesFromRental($rental);

            // Tạo thông báo khi cập nhật phiếu cho thuê
            if ($rental->employee_id) {
                Notification::createNotification(
                    'Phiếu cho thuê được cập nhật',
                    "Phiếu cho thuê #{$rental->rental_code} - {$rental->rental_name} đã được cập nhật thông tin.",
                    'info',
                    $rental->employee_id,
                    'rental',
                    $rental->id,
                    route('rentals.show', $rental->id)
                );
            }

            // Gửi thông báo cho khách hàng
            $customerUsers = \App\Models\User::where('customer_id', $rental->customer_id)->where('active', true)->get();
            foreach ($customerUsers as $user) {
                Notification::createNotification(
                    'Phiếu cho thuê được cập nhật',
                    'Thông tin phiếu cho thuê #' . $rental->rental_code . ' đã được cập nhật.',
                    'info',
                    $user->id,
                    'rental',
                    $rental->id,
                    route('customer.dashboard'),
                    null,
                    'customer'
                );
            }

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Phiếu cho thuê đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Đồng bộ bảo hành điện tử khi cập nhật phiếu cho thuê
     * 
     * @param Rental $rental
     * @param bool $syncEndDate Nếu true (gia hạn), cập nhật cả warranty_end_date. Mặc định false (chỉ đồng bộ tên/khách hàng).
     */
    private function syncWarrantiesFromRental(Rental $rental, bool $syncEndDate = false)
    {
        // Chỉ lấy warranty liên kết qua dispatch rental
        $warranties = \App\Models\Warranty::where('item_type', 'rental')
            ->whereHas('dispatch', function ($query) use ($rental) {
                $query->where('project_id', $rental->id)
                      ->where('dispatch_type', 'rental');
            })
            ->get();

        if ($warranties->isEmpty()) {
            return;
        }

        $customerName = optional($rental->customer)->company_name ?: optional($rental->customer)->name;

        foreach ($warranties as $warranty) {
            // Đồng bộ project_name theo format chuẩn: Mã - Tên (Khách hàng)
            $customerDisplay = optional($rental->customer)->company_name ?: optional($rental->customer)->name;
            $projectNameFormatted = \App\Models\Warranty::formatProjectName(
                $rental->rental_code,
                $rental->rental_name,
                $customerDisplay
            );

            $updateData = [
                'project_name' => $projectNameFormatted,
                'customer_name' => $customerName,
            ];

            // Chỉ cập nhật ngày hết hạn khi gia hạn, không ghi đè khi chỉ sửa thông tin
            if ($syncEndDate) {
                $startDate = \Carbon\Carbon::parse($rental->rental_date);
                $endDate = \Carbon\Carbon::parse($rental->due_date);
                $periodMonths = max(1, $startDate->diffInMonths($endDate) ?: 1);

                $updateData['warranty_end_date'] = $endDate->toDateString();
                $updateData['warranty_period_months'] = $periodMonths;
            }

            $warranty->update($updateData);
        }
    }

    public function destroy($id)
    {
        try {
            $rental = Rental::findOrFail($id);

            // Lưu dữ liệu cũ trước khi xóa
            $oldData = $rental->toArray();
            $rentalCode = $rental->rental_code;

            // Kiểm tra điều kiện 1: Thời gian bảo hành còn lại <= 0 ngày (đã hết hạn)
            $remainingWarrantyDays = $rental->remaining_warranty_days;
            if ($remainingWarrantyDays > 0) {
                return redirect()->route('rentals.show', $id)
                    ->with('error', 'Không thể xóa phiếu cho thuê này vì thời gian bảo hành còn lại: ' . $remainingWarrantyDays . ' ngày. Chỉ có thể xóa khi thời gian bảo hành đã hết.');
            }

            // Kiểm tra điều kiện 2: Không còn thiết bị dự phòng/bảo hành nào
            $backupItemsCount = $this->getBackupItemsCount($id);
            if ($backupItemsCount > 0) {
                return redirect()->route('rentals.show', $id)
                    ->with('error', 'Không thể xóa phiếu cho thuê này vì còn ' . $backupItemsCount . ' thiết bị dự phòng/bảo hành. Vui lòng thu hồi tất cả thiết bị dự phòng trước khi xóa phiếu cho thuê.');
            }

            // Nếu thỏa mãn cả 2 điều kiện, tiến hành xóa phiếu cho thuê
            $rental->delete();

            // Ghi nhật ký xóa phiếu cho thuê
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'rentals',
                    'Xóa phiếu cho thuê: ' . $rentalCode,
                    $oldData,
                    null
                );
            }

            // Gửi thông báo cho khách hàng
            $customerUsers = \App\Models\User::where('customer_id', $rental->customer_id)->where('active', true)->get();
            foreach ($customerUsers as $user) {
                Notification::createNotification(
                    'Phiếu cho thuê đã bị xóa',
                    'Phiếu cho thuê #' . $rentalCode . ' - ' . $oldData['rental_name'] . ' đã bị xóa khỏi hệ thống.',
                    'error',
                    $user->id,
                    'rental',
                    null,
                    route('customer.dashboard'),
                    null,
                    'customer'
                );
            }

            return redirect()->route('rentals.index')
                ->with('success', 'Phiếu cho thuê đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('rentals.index')
                ->with('error', 'Có lỗi xảy ra khi xóa phiếu cho thuê: ' . $e->getMessage());
        }
    }

    /**
     * Gia hạn phiếu cho thuê
     */
    public function extend(Request $request, $id)
    {
        // Validation
        $request->validate([
            'extend_days' => 'required|integer|min:1',
            'extend_notes' => 'nullable|string',
        ], [
            'extend_days.required' => 'Số ngày gia hạn không được để trống',
            'extend_days.integer' => 'Số ngày gia hạn phải là số nguyên',
            'extend_days.min' => 'Số ngày gia hạn phải lớn hơn 0',
        ]);

        try {
            $rental = Rental::findOrFail($id);

            // Lưu dữ liệu cũ trước khi gia hạn
            $oldData = $rental->toArray();

            // Gia hạn thêm số ngày
            $newDueDate = date('Y-m-d', strtotime($rental->due_date . ' + ' . $request->extend_days . ' days'));

            // Cập nhật ghi chú
            $notes = $rental->notes ?? '';
            $notes .= "\n[" . date('Y-m-d H:i:s') . "] Gia hạn thêm " . $request->extend_days . " ngày. " . ($request->extend_notes ?? '');

            $rental->update([
                'due_date' => $newDueDate,
                'notes' => trim($notes),
            ]);

            // Đồng bộ thông tin warranty khi rental gia hạn (cập nhật cả ngày hết hạn)
            $this->syncWarrantiesFromRental($rental, true);

            // Ghi nhật ký gia hạn phiếu cho thuê
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'extend',
                    'rentals',
                    'Gia hạn phiếu cho thuê: ' . $rental->rental_code,
                    $oldData,
                    $rental->toArray()
                );
            }

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Phiếu cho thuê đã được gia hạn thành công. Phiếu bảo hành điện tử đã được cập nhật.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi gia hạn phiếu cho thuê: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách các thiết bị trong đơn thuê
     */
    public function getRentalItems($rentalId)
    {
        $rental = \App\Models\Rental::find($rentalId);

        if (!$rental) {
            return response()->json(['error' => 'Không tìm thấy đơn thuê'], 404);
        }

        // Lấy danh sách thiết bị từ các phiếu xuất kho của đơn thuê
        $dispatches = \App\Models\Dispatch::whereIn('dispatch_type', ['rental', 'warranty'])
            ->where('project_id', $rental->id) // Tìm theo project_id = rental_id
            ->get();

        $allItems = collect();

        foreach ($dispatches as $dispatch) {
            // Lấy danh sách products (thiết bị) - chỉ lấy category = 'contract'
            $products = $dispatch->items()
                ->with(['product'])
                ->where('item_type', 'product')
                ->where('category', 'contract')
                ->get()
                ->map(function ($item) use ($rental, $dispatch) {
                    // Xử lý serial numbers từ JSON array
                    $serialNumbers = $item->serial_numbers ?? [];
                    $serialNumbersArray = [];

                    if (!empty($serialNumbers)) {
                        if (is_array($serialNumbers)) {
                            // Lọc bỏ các giá trị rỗng
                            $serialNumbersArray = array_filter($serialNumbers, function ($serial) {
                                return !empty(trim($serial));
                            });
                            $serialNumbersArray = array_values($serialNumbersArray); // Re-index array
                        } else {
                            // Nếu là string, tách thành array
                            $serialNumbersArray = [trim($serialNumbers)];
                        }
                    }

                    // Sử dụng SerialDisplayHelper để lấy serial hiển thị
                    $displaySerials = SerialDisplayHelper::getDisplaySerials(
                        $dispatch->id,
                        $item->item_id,
                        $item->item_type,
                        $serialNumbersArray
                    );

                    return [
                        'id' => $item->product->id, // Sử dụng ID của Product thay vì DispatchItem
                        'type' => 'product',
                        'name' => $item->product->name,
                        'serial_numbers' => $displaySerials,
                        'description' => $item->product->description,
                        'rental_code' => $rental->rental_code,
                        'quantity' => $item->quantity
                    ];
                });

            // Lấy danh sách goods (hàng hóa) - chỉ lấy category = 'contract'
            $goods = $dispatch->items()
                ->with(['good'])
                ->where('item_type', 'good')
                ->where('category', 'contract')
                ->get()
                ->map(function ($item) use ($rental, $dispatch) {
                    // Xử lý serial numbers từ JSON array
                    $serialNumbers = $item->serial_numbers ?? [];
                    $serialNumbersArray = [];

                    if (!empty($serialNumbers)) {
                        if (is_array($serialNumbers)) {
                            // Lọc bỏ các giá trị rỗng
                            $serialNumbersArray = array_filter($serialNumbers, function ($serial) {
                                return !empty(trim($serial));
                            });
                            $serialNumbersArray = array_values($serialNumbersArray); // Re-index array
                        } else {
                            // Nếu là string, tách thành array
                            $serialNumbersArray = [trim($serialNumbers)];
                        }
                    }

                    // Sử dụng SerialDisplayHelper để lấy serial hiển thị
                    $displaySerials = SerialDisplayHelper::getDisplaySerials(
                        $dispatch->id,
                        $item->item_id,
                        $item->item_type,
                        $serialNumbersArray
                    );

                    return [
                        'id' => $item->good->id, // Sử dụng ID của Good thay vì DispatchItem
                        'type' => 'good',
                        'name' => $item->good->name,
                        'serial_numbers' => $displaySerials,
                        'description' => $item->good->description,
                        'rental_code' => $rental->rental_code,
                        'quantity' => $item->quantity
                    ];
                });

            // Kết hợp cả products và goods
            $allItems = $allItems->concat($products)->concat($goods);
        }

        return response()->json($allItems);
    }

    /**
     * Đếm số lượng thiết bị dự phòng/bảo hành của rental
     */
    private function getBackupItemsCount($rentalId)
    {
        // Lấy tất cả phiếu xuất kho của rental
        $rental = \App\Models\Rental::find($rentalId);
        if (!$rental) {
            return 0;
        }

        $dispatches = \App\Models\Dispatch::whereIn('dispatch_type', ['rental', 'warranty'])
            ->where('project_id', $rental->id) // Tìm theo project_id = rental_id
            ->get();

        $backupItemsCount = 0;

        foreach ($dispatches as $dispatch) {
            // Đếm thiết bị dự phòng/bảo hành (category = 'backup')
            $backupItems = $dispatch->items()
                ->where('category', 'backup')
                ->get();

            foreach ($backupItems as $item) {
                // Kiểm tra xem thiết bị đã bị thu hồi chưa
                $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)->exists();

                // Kiểm tra xem thiết bị đã được sử dụng trong bảo hành/thay thế chưa
                $isUsed = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)->exists();

                // Chỉ đếm những thiết bị chưa bị thu hồi VÀ chưa được sử dụng trong bảo hành/thay thế
                if (!$isReturned && !$isUsed) {
                    $backupItemsCount++;
                }
            }
        }

        return $backupItemsCount;
    }
}