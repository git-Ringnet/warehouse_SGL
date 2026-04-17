<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\UserLog;
use Illuminate\Support\Facades\DB;
use App\Helpers\DateHelper;
use App\Helpers\SerialDisplayHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Hiển thị danh sách dự án
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $warranty_status = $request->input('warranty_status');

        $query = Project::with('customer');

        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'project_code':
                        $query->where('project_code', 'like', "%{$search}%");
                        break;
                    case 'project_name':
                        $query->where('project_name', 'like', "%{$search}%");
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
                    $q->where('project_code', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
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
                    // Còn bảo hành: remaining_warranty_days > 0
                    $query->whereRaw('DATE_ADD(start_date, INTERVAL warranty_period MONTH) > CURDATE()');
                    break;
                case 'expired':
                    // Hết bảo hành: remaining_warranty_days <= 0
                    $query->whereRaw('DATE_ADD(start_date, INTERVAL warranty_period MONTH) <= CURDATE()');
                    break;
            }
        }

        $projects = $query->latest()->paginate(10);

        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $projects->appends([
            'search' => $search,
            'filter' => $filter,
            'warranty_status' => $warranty_status
        ]);

        return view('projects.index', compact('projects', 'search', 'filter', 'warranty_status'));
    }

    /**
     * Hiển thị form tạo dự án mới
     */
    public function create()
    {
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();
        return view('projects.create', compact('customers', 'employees'));
    }

    /**
     * Lưu dự án mới vào database
     */
    public function store(Request $request)
    {
        // Chuyển đổi định dạng ngày từ dd/mm/yyyy sang yyyy-mm-dd
        $request->merge([
            'start_date' => DateHelper::convertToDatabaseFormat($request->start_date),
            'end_date' => DateHelper::convertToDatabaseFormat($request->end_date)
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'project_code' => 'required|string|max:255|unique:projects',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'warranty_period' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'project_code.required' => 'Mã dự án không được để trống',
            'project_code.unique' => 'Mã dự án đã tồn tại',
            'project_name.required' => 'Tên dự án không được để trống',
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'employee_id.exists' => 'Nhân viên phụ trách không tồn tại',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'warranty_period.required' => 'Thời gian bảo hành không được để trống',
            'warranty_period.integer' => 'Thời gian bảo hành phải là số nguyên',
            'warranty_period.min' => 'Thời gian bảo hành phải lớn hơn hoặc bằng 1',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tạo dự án mới
        $project = Project::create([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'customer_id' => $request->customer_id,
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'warranty_period' => $request->warranty_period,
            'description' => $request->description,
        ]);

        // Ghi nhật ký tạo mới dự án
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'projects',
                'Tạo mới dự án: ' . $project->project_code,
                null,
                $project->toArray()
            );
        }

        // Tạo thông báo khi tạo dự án mới
        if ($project->employee_id) {
            Notification::createNotification(
                'Dự án mới được tạo',
                "Dự án #{$project->project_code} - {$project->project_name} đã được tạo và phân công cho bạn.",
                'info',
                $project->employee_id,
                'project',
                $project->id,
                route('projects.show', $project->id)
            );

            // Kiểm tra và gửi thông báo về trạng thái bảo hành
            $observer = new \App\Observers\ProjectObserver();

            // Gọi phương thức protected thông qua Reflection API
            $reflection = new \ReflectionClass(get_class($observer));
            $method = $reflection->getMethod('checkWarrantyStatus');
            $method->setAccessible(true);
            $method->invokeArgs($observer, [$project]);
        }

        // Gửi thông báo cho khách hàng
        $customerUsers = \App\Models\User::where('customer_id', $project->customer_id)->where('active', true)->get();
        foreach ($customerUsers as $user) {
            Notification::createNotification(
                'Dự án mới: ' . $project->project_name,
                'Dự án #' . $project->project_code . ' đã được tạo cho đơn vị của bạn. Ngày bắt đầu: ' . \Carbon\Carbon::parse($project->start_date)->format('d/m/Y'),
                'info',
                $user->id,
                'project',
                $project->id,
                route('customer.dashboard'),
                null,
                'customer'
            );
        }

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được thêm thành công');
    }

    /**
     * Hiển thị chi tiết dự án
     */
    public function show($id)
    {
        $project = Project::with('customer')->findOrFail($id);
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();

        // Lấy danh sách thiết bị theo hợp đồng với chi tiết từng thiết bị
        $contractItems = collect();

        // Lấy dispatches của dự án, nhưng loại trừ:
        // - Phiếu xuất vật tư từ lắp ráp (chỉ để trừ tồn kho vật tư)
        // - Phiếu xuất trung gian từ lắp ráp
        // Chỉ lấy:
        // - Phiếu xuất thành phẩm từ kiểm thử (Sinh từ phiếu kiểm thử)
        // - Phiếu xuất trực tiếp đi dự án (không qua lắp ráp)
        // Lấy TẤT CẢ các phiếu xuất liên quan đến dự án (để dùng cho phần dự phòng & thu hồi)
        $allDispatches = \App\Models\Dispatch::whereIn('dispatch_type', ['project', 'warranty'])
            ->where('project_id', $project->id)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        // Lọc riêng các phiếu xuất dùng cho danh sách 'Hợp đồng' (tránh hiện các phiếu lắp ráp/kiểm thử trùng lặp)
        $contractDispatches = $allDispatches->filter(function($d) {
            // Phiếu xuất từ kiểm thử (ưu tiên)
            if (strpos($d->dispatch_note ?? '', 'Sinh từ phiếu kiểm thử') !== false) {
                return true;
            }
            // Phiếu xuất trực tiếp (không qua lắp ráp/kiểm thử)
            if (strpos($d->dispatch_note ?? '', 'Sinh từ phiếu lắp ráp') === false && 
                strpos($d->dispatch_note ?? '', 'Sinh từ phiếu kiểm thử') === false) {
                return true;
            }
            // Phiếu xuất thủ công
            if (is_null($d->dispatch_note)) {
                return true;
            }
            return false;
        });

        // Lấy tất cả Item IDs liên quan trực tiếp đến các phiếu xuất của dự án (dùng bản gốc ALL)
        $allItemIdsRaw = $allDispatches->map(function($d) {
            return $d->items->pluck('id');
        })->flatten()->unique()->toArray();


        // Tìm tất cả các record thay đổi (replacements) liên quan đến các item này
        // (Bao gồm cả việc item này đi thay cho máy khác hoặc bị máy khác thay)
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

        // Danh sách các Item ID đang đóng vai trò là "Thiết bị thay thế" 
        // Chúng ta sẽ ẨN các dòng này vì số lượng của chúng đã được tính vào dòng Hợp đồng gốc (Phương án B)
        $replacementDispatchItemIds = $replacements->pluck('replacement_dispatch_item_id')->unique()->toArray();

        // Lấy danh sách tất cả Item IDs (bao gồm cả hàng gốc và hàng thay thế) để tính toán thu hồi
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

        $processedDispatchItemIds = [];


        foreach ($contractDispatches as $dispatch) {
            // Lấy cả items có category 'contract' và 'general' (loại trừ 'backup')
            $items = $dispatch->items()->where('category', '!=', 'backup')->get();


            foreach ($items as $item) {
                // Kiểm tra xem dispatch_item_id đã được xử lý chưa
                if (in_array($item->id, $processedDispatchItemIds)) {
                    continue;
                }
                
                // Skip if this item is actually a replacement (added in the second loop)
                if (in_array($item->id, $replacementDispatchItemIds)) {
                    continue;
                }
                
                $processedDispatchItemIds[] = $item->id;

                $serialNumbers = $item->serial_numbers ?? [];
                $quantity = (int) ($item->quantity ?? 1);

                // Fallback: lấy serial từ device_codes nếu dispatch_items.serial_numbers rỗng
                // HOẶC tất cả serial đều là virtual (N/A-xxx) — trường hợp xuất kho không serial
                // rồi cập nhật serial thực qua Excel import (chỉ lưu vào device_codes)
                $allVirtual = !empty($serialNumbers) && collect($serialNumbers)->every(fn($s) => \App\Helpers\SerialHelper::isVirtualSerial((string)$s));
                if (($allVirtual || empty($serialNumbers)) && $quantity > 0) {
                    $deviceCodeSerials = \App\Models\DeviceCode::where('dispatch_id', $dispatch->id)
                        ->where('item_id', $item->item_id)
                        ->where('item_type', $item->item_type)
                        ->pluck('serial_main')
                        ->filter()
                        ->values()
                        ->toArray();
                    if (!empty($deviceCodeSerials)) {
                        $serialNumbers = $deviceCodeSerials;
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
                $isBulkOrMeasure = $isMeasurementUnit || $isImplementationMaterial || (empty($serialNumbers) && $quantity > 1);


                if ($isBulkOrMeasure) {
                    // Skip if quantity is 0 (removed or fully recalled)
                    if ($item->quantity <= 0) {
                        continue;
                    }

                    $replacementQty = $replacementTotals->get($item->id, 0);
                    $returnQty = $returnTotals->get($item->id, 0);
                    
                    // Hiển thị theo hợp đồng: tổng còn gắn với phiếu (trừ thu hồi), KHÔNG trừ phần đã thay thế — trạng thái thay thế hiển thị riêng.
                    $displayQty = max(0.0, (float)$item->quantity - (float)$returnQty);
                    // Phần còn có thể thao tác (thay thế tiếp / thu hồi phần chưa thay) — dùng cho data-max-qty
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

                // Nếu có serial numbers, tạo bản ghi cho từng serial
                if (!empty($serialNumbers) && is_array($serialNumbers)) {
                    foreach ($serialNumbers as $i => $serial) {
                        $serial = trim($serial);
                        if (!empty($serial)) {
                            // Bỏ qua nếu serial này đã bị thu hồi (để hỗ trợ Lựa chọn B: mất dòng khi thu hồi hết)
                            $isAlreadyReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                ->where(function($q) use ($serial, $dispatch, $item) {
                                    $q->where('serial_number', $serial);
                                    
                                    // Cũng check virtual serial tương ứng (trường hợp serial thực từ device_codes)
                                    if (!\App\Helpers\SerialHelper::isVirtualSerial($serial)) {
                                        $dc = DB::table('device_codes')
                                            ->where('dispatch_id', $dispatch->id)
                                            ->where('item_id', $item->item_id)
                                            ->where('item_type', $item->item_type)
                                            ->where('serial_main', $serial)
                                            ->first();
                                        if ($dc && isset($dc->old_serial) && !empty($dc->old_serial)) {
                                            $q->orWhere('serial_number', $dc->old_serial);
                                        }
                                    }

                                    // Ngược lại: nếu serial hiện tại là virtual nhưng trong DB có thể đã lưu serial thực
                                    if (\App\Helpers\SerialHelper::isVirtualSerial($serial)) {
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
                            
                            // Nếu serial đã bị thu hồi trước đó nhưng VẪN CÒN trong serial_numbers hiện tại,
                            // nghĩa là nó đã được thêm lại qua thay thế (replacement) → không ẩn
                            if ($isAlreadyReturned && in_array($serial, $item->serial_numbers ?? [], true)) {
                                // Serial vẫn còn trong dispatch_items.serial_numbers → đã được thay thế/thêm lại
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


                    // Nếu quantity > số serial, thêm các bản ghi N/A cho phần còn lại
                    $serialCount = count(array_filter($serialNumbers, fn($s) => !empty(trim($s))));
                    if ($quantity > $serialCount) {
                        for ($i = 0; $i < ($quantity - $serialCount); $i++) {
                            $contractItems->push([
                                'dispatch_item' => $item,
                                'dispatch' => $dispatch,
                                'serial_index' => $serialCount + $i,
                                'serial_number' => 'N/A',
                                'has_serial' => false
                            ]);
                        }
                    }
                } else {
                    // Nếu không có serial numbers, tạo bản ghi dựa trên quantity
                    for ($i = 0; $i < $quantity; $i++) {
                        $contractItems->push([
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

        // Lấy danh sách thiết bị dự phòng cho bảo hành/thay thế
        $backupItems = collect();
        $processedBackupItemIds = [];

        foreach ($allDispatches as $dispatch) {
            $items = $dispatch->items()->where('category', 'backup')->get();


            foreach ($items as $item) {
                // Kiểm tra xem dispatch_item_id đã được xử lý chưa
                if (in_array($item->id, $processedBackupItemIds)) {
                    continue;
                }
                $processedBackupItemIds[] = $item->id;

                $serialNumbers = $item->serial_numbers ?? [];
                $quantity = (int) ($item->quantity ?? 1);

                // Fallback: lấy serial từ device_codes nếu serial rỗng hoặc tất cả virtual
                $allVirtual = !empty($serialNumbers) && collect($serialNumbers)->every(fn($s) => \App\Helpers\SerialHelper::isVirtualSerial((string)$s));
                if (($allVirtual || empty($serialNumbers)) && $quantity > 0) {
                    $deviceCodeSerials = \App\Models\DeviceCode::where('dispatch_id', $dispatch->id)
                        ->where('item_id', $item->item_id)
                        ->where('item_type', $item->item_type)
                        ->pluck('serial_main')
                        ->filter()
                        ->values()
                        ->toArray();
                    if (!empty($deviceCodeSerials)) {
                        $serialNumbers = $deviceCodeSerials;
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
                $isBulkOrMeasure = $isMeasurementUnit || $isImplementationMaterial || (empty($serialNumbers) && $quantity > 1);

                if ($isBulkOrMeasure) {
                    // Tính số lượng đã sử dụng để thay thế (incoming), trừ phần đã thu hồi của hàng thay thế
                    $usedQtyIn = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                        ->get()
                        ->sum(function ($r) {
                            return (float) $r->quantity - (float) ($r->replacement_returned_quantity ?? 0);
                        });
                    
                    $returnQty = $returnTotals->get($item->id, 0);
                    
                    // Số lượng khả dụng (Idle) = Tổng - Đã dùng thay thế - Đã thu hồi
                    $availableQty = max(0.0, (float)$item->quantity - (float)$usedQtyIn - (float)$returnQty);
                    // Cột "Số lượng" = phần còn idle trên dòng dự phòng (khớp "Còn lại"); phần đã xuất thay hiển thị ở "Đã dùng" và hàng lỗi có dòng "Đã sử dụng" riêng — không nhân đôi 500 khi đã tách 100

                    // Không skip nữa, để hiện cả hàng đã dùng hết để thu hồi hàng cũ
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


                // Nếu có serial numbers, tạo bản ghi cho từng serial
                if (!empty($serialNumbers) && is_array($serialNumbers)) {
                    foreach ($serialNumbers as $i => $serial) {
                        $serial = trim($serial);
                        if (!empty($serial)) {
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

                    // Nếu quantity > số serial, thêm các bản ghi N/A cho phần còn lại
                    $serialCount = count(array_filter($serialNumbers, fn($s) => !empty(trim($s))));
                    if ($quantity > $serialCount) {
                        for ($i = 0; $i < ($quantity - $serialCount); $i++) {
                            $backupItems->push([
                                'dispatch_item' => $item,
                                'dispatch' => $dispatch,
                                'serial_index' => $serialCount + $i,
                                'serial_number' => 'N/A',
                                'has_serial' => false
                            ]);
                        }
                    }
                } else {
                    // Nếu không có serial numbers, tạo bản ghi dựa trên quantity
                    for ($i = 0; $i < $quantity; $i++) {
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

        // Ghi nhật ký xem chi tiết dự án
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'projects',
                'Xem chi tiết dự án: ' . $project->project_code,
                null,
                $project->toArray()
            );
        }

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
            
            $isImplementationMaterial = false;
            if ($replacementItem->item_type === 'good' && $replacementItem->good) {
                $isImplementationMaterial = (trim($replacementItem->good->category) === 'Vật tư triển khai');
            }
            
            $itemOutgoingQty = $remainingOutgoing[$replacement->replacement_dispatch_item_id] ?? 0;
            $deduction = min((float)$replacement->quantity, (float)$itemOutgoingQty);
            $displayReplacementQty = (float)$replacement->quantity - $deduction;
            
            // Update remaining outgoing for this item
            if (isset($remainingOutgoing[$replacement->replacement_dispatch_item_id])) {
                $remainingOutgoing[$replacement->replacement_dispatch_item_id] -= $deduction;
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
            
            // Hiển thị các bản ghi "Đã sử dụng" (hàng cũ bị thay thế) — hàng lỗi còn tại khu vực dự phòng, độc lập với việc thu hồi hàng thay thế trên hợp đồng (không tự giảm khi replacement_returned tăng).
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
        // --- Kết thúc phần Gộp hàng hóa đo lường trùng lặp ---

        return view('projects.show', compact('project', 'warehouses', 'backupItems', 'contractItems'));
    }

    /**
     * Hiển thị form chỉnh sửa dự án
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();
        return view('projects.edit', compact('project', 'customers', 'employees'));
    }

    /**
     * Cập nhật dự án trong database
     */
    public function update(Request $request, $id)
    {
        // Chuyển đổi định dạng ngày từ dd/mm/yyyy sang yyyy-mm-dd
        $request->merge([
            'start_date' => DateHelper::convertToDatabaseFormat($request->start_date),
            'end_date' => DateHelper::convertToDatabaseFormat($request->end_date)
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'project_code' => 'required|string|max:255|unique:projects,project_code,' . $id,
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'warranty_period' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'project_code.required' => 'Mã dự án không được để trống',
            'project_code.unique' => 'Mã dự án đã tồn tại',
            'project_name.required' => 'Tên dự án không được để trống',
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'employee_id.exists' => 'Nhân viên phụ trách không tồn tại',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'warranty_period.required' => 'Thời gian bảo hành không được để trống',
            'warranty_period.integer' => 'Thời gian bảo hành phải là số nguyên',
            'warranty_period.min' => 'Thời gian bảo hành phải lớn hơn hoặc bằng 1',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cập nhật dự án
        $project = Project::findOrFail($id);

        // Lưu thông tin cũ trước khi cập nhật
        $oldData = $project->toArray();
        $oldEmployeeId = $project->employee_id;
        $startDateChanged = $project->start_date != $request->start_date;
        $warrantyPeriodChanged = $project->warranty_period != $request->warranty_period;

        $project->update([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'customer_id' => $request->customer_id,
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'warranty_period' => $request->warranty_period,
            'description' => $request->description,
        ]);

        // Ghi nhật ký cập nhật dự án
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'projects',
                'Cập nhật dự án: ' . $project->project_code,
                $oldData,
                $project->toArray()
            );
        }

        // Tạo thông báo khi cập nhật dự án
        if ($project->employee_id) {
            // Nếu nhân viên phụ trách đã thay đổi, gửi thông báo cho nhân viên mới
            if ($oldEmployeeId != $project->employee_id) {
                Notification::createNotification(
                    'Dự án được phân công cho bạn',
                    "Dự án #{$project->project_code} - {$project->project_name} đã được phân công cho bạn.",
                    'info',
                    $project->employee_id,
                    'project',
                    $project->id,
                    route('projects.show', $project->id)
                );
            } else {
                Notification::createNotification(
                    'Dự án được cập nhật',
                    "Dự án #{$project->project_code} - {$project->project_name} đã được cập nhật thông tin.",
                    'info',
                    $project->employee_id,
                    'project',
                    $project->id,
                    route('projects.show', $project->id)
                );
            }

            // Kiểm tra và gửi thông báo về bảo hành nếu thông tin bảo hành đã thay đổi
            if ($startDateChanged || $warrantyPeriodChanged) {
                // Đồng bộ thông tin warranty khi project thay đổi
                $this->syncWarrantiesFromProject($project);

                // Sử dụng ProjectObserver để kiểm tra và gửi thông báo
                $observer = new \App\Observers\ProjectObserver();

                // Gọi phương thức protected thông qua Reflection API
                $reflection = new \ReflectionClass(get_class($observer));
                $method = $reflection->getMethod('checkWarrantyStatus');
                $method->setAccessible(true);
                $method->invokeArgs($observer, [$project]);
            }
        }

        // Gửi thông báo cho khách hàng
        $customerUsers = \App\Models\User::where('customer_id', $project->customer_id)->where('active', true)->get();
        foreach ($customerUsers as $user) {
            Notification::createNotification(
                'Dự án được cập nhật',
                'Thông tin dự án #' . $project->project_code . ' đã được cập nhật.',
                'info',
                $user->id,
                'project',
                $project->id,
                route('customer.dashboard'),
                null,
                'customer'
            );
        }

        return redirect()->route('projects.show', $id)
            ->with('success', 'Thông tin dự án đã được cập nhật thành công');
    }

    /**
     * Xóa dự án khỏi database
     */
    public function destroy($id)
    {
        try {
            $project = Project::findOrFail($id);

            // Lưu dữ liệu cũ trước khi xóa
            $oldData = $project->toArray();
            $projectCode = $project->project_code;

            // Kiểm tra điều kiện 1: Thời gian bảo hành còn lại <= 0 ngày (đã hết hạn)
            $remainingWarrantyDays = $project->remaining_warranty_days;
            if ($remainingWarrantyDays > 0) {
                return redirect()->route('projects.show', $id)
                    ->with('error', 'Không thể xóa dự án này vì thời gian bảo hành còn lại: ' . $remainingWarrantyDays . ' ngày. Chỉ có thể xóa khi thời gian bảo hành đã hết.');
            }

            // Kiểm tra điều kiện 2: Không còn thiết bị dự phòng/bảo hành nào
            $backupItemsCount = $this->getBackupItemsCount($id);
            if ($backupItemsCount > 0) {
                return redirect()->route('projects.show', $id)
                    ->with('error', 'Không thể xóa dự án này vì còn ' . $backupItemsCount . ' thiết bị dự phòng/bảo hành. Vui lòng thu hồi tất cả thiết bị dự phòng trước khi xóa dự án.');
            }

            // Nếu thỏa mãn cả 2 điều kiện, tiến hành xóa dự án
            $project->delete();

            // Ghi nhật ký xóa dự án
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'projects',
                    'Xóa dự án: ' . $projectCode,
                    $oldData,
                    null
                );
            }

            // Tạo thông báo khi xóa dự án
            if ($project->employee_id) {
                Notification::createNotification(
                    'Dự án đã bị xóa',
                    "Dự án #{$project->project_code} - {$project->project_name} đã bị xóa.",
                    'error',
                    $project->employee_id,
                    'project',
                    null,
                    route('projects.index')
                );
            }

            // Gửi thông báo cho khách hàng
            $customerUsers = \App\Models\User::where('customer_id', $project->customer_id)->where('active', true)->get();
            foreach ($customerUsers as $user) {
                Notification::createNotification(
                    'Dự án đã bị xóa',
                    'Dự án #' . $project->project_code . ' - ' . $project->project_name . ' đã bị xóa khỏi hệ thống.',
                    'error',
                    $user->id,
                    'project',
                    null,
                    route('customer.dashboard'),
                    null,
                    'customer'
                );
            }

            return redirect()->route('projects.index')
                ->with('success', 'Dự án đã được xóa thành công');
        } catch (\Exception $e) {
            return redirect()->route('projects.index')
                ->with('error', 'Có lỗi xảy ra khi xóa dự án: ' . $e->getMessage());
        }
    }

    /**
     * Đếm số lượng thiết bị dự phòng/bảo hành của dự án
     */
    private function getBackupItemsCount($projectId)
    {
        // Lấy tất cả phiếu xuất kho của dự án
        $dispatches = \App\Models\Dispatch::where('project_id', $projectId)->get();

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

    /**
     * Lấy danh sách các thiết bị trong dự án
     */
    public function getProjectItems($projectId)
    {
        $project = \App\Models\Project::find($projectId);

        if (!$project) {
            return response()->json(['error' => 'Không tìm thấy dự án'], 404);
        }

        // Lấy danh sách thiết bị từ các phiếu xuất kho của dự án
        $dispatches = \App\Models\Dispatch::whereIn('dispatch_type', ['project', 'warranty'])
            ->where('project_id', $projectId)
            ->get();

        $allItems = collect();

        foreach ($dispatches as $dispatch) {
            // Lấy danh sách products (thiết bị) - chỉ lấy category = 'contract'
            $products = $dispatch->items()
                ->with(['product'])
                ->where('item_type', 'product')
                ->where('category', 'contract')
                ->get()
                ->map(function ($item) use ($dispatch) {
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
                        'project_name' => $dispatch->project_name,
                        'dispatch_code' => $dispatch->dispatch_code,
                        'quantity' => $item->quantity
                    ];
                });

            // Lấy danh sách goods (hàng hóa) - chỉ lấy category = 'contract'
            $goods = $dispatch->items()
                ->with(['good'])
                ->where('item_type', 'good')
                ->where('category', 'contract')
                ->get()
                ->map(function ($item) use ($dispatch) {
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
                        'project_name' => $dispatch->project_name,
                        'dispatch_code' => $dispatch->dispatch_code,
                        'quantity' => $item->quantity
                    ];
                });

            // Kết hợp cả products và goods
            $allItems = $allItems->concat($products)->concat($goods);
        }

        return response()->json($allItems);
    }

    /**
     * Lấy thông tin chi tiết của dự án qua API
     */
    public function getProjectDetails($projectId)
    {
        $project = Project::with('customer')->findOrFail($projectId);

        return response()->json([
            'success' => true,
            'project' => [
                'id' => $project->id,
                'project_code' => $project->project_code,
                'project_name' => $project->project_name,
                'customer' => [
                    'id' => $project->customer->id,
                    'name' => $project->customer->name,
                    'phone' => $project->customer->phone,
                    'email' => $project->customer->email,
                    'address' => $project->customer->address
                ]
            ]
        ]);
    }

    /**
     * Đồng bộ thông tin bảo hành từ dự án
     */
    private function syncWarrantiesFromProject(Project $project)
    {
        // Cast warranty_period thành integer để tránh lỗi Carbon
        $warrantyPeriod = (int) $project->warranty_period;
        $startDate = \Carbon\Carbon::parse($project->start_date);
        $endDate = $startDate->copy()->addMonths($warrantyPeriod);

        // Cập nhật tất cả warranty liên quan đến project này
        $warranties = \App\Models\Warranty::where('item_type', 'project')
            ->where(function($q) use ($project) {
                $q->where('project_name', 'like', '%' . $project->project_name . '%')
                  ->orWhereHas('dispatch', function ($subQ) use ($project) {
                      $subQ->where('project_id', $project->id)
                           ->where('dispatch_type', '!=', 'rental');
                  });
            })
            ->get();

        $customerDisplay = optional($project->customer)->name ?? '';
        $standardProjectName = \App\Models\Warranty::formatProjectName(
            $project->project_code,
            $project->project_name,
            $customerDisplay
        );

        foreach ($warranties as $warranty) {
            $warranty->update([
                'warranty_start_date' => $startDate->toDateString(),
                'warranty_end_date' => $endDate->toDateString(),
                'warranty_period_months' => $warrantyPeriod,
                'project_name' => $standardProjectName,
            ]);
        }

        // Lấy danh sách thiết bị dự phòng/bảo hành từ dự án
        $dispatches = \App\Models\Dispatch::where('project_id', $project->id)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        foreach ($dispatches as $dispatch) {
            $items = $dispatch->items()
                ->where('category', 'backup')
                ->get();

            foreach ($items as $item) {
                // Tìm hoặc tạo Warranty dựa trên dispatch_item_id
                $warranty = \App\Models\Warranty::firstOrCreate(
                    [
                        'dispatch_item_id' => $item->id,
                    ],
                    [
                        'warranty_code' => 'BH' . date('Ymd') . str_pad($item->id, 4, '0', STR_PAD_LEFT),
                        'dispatch_id' => $dispatch->id,
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'serial_number' => $item->serial_numbers ? json_encode($item->serial_numbers) : null,
                        'customer_name' => $project->customer->name ?? '',
                        'customer_phone' => $project->customer->phone ?? '',
                        'customer_email' => $project->customer->email ?? '',
                        'customer_address' => $project->customer->address ?? '',
                        'project_name' => $standardProjectName,
                        'purchase_date' => $dispatch->dispatch_date,
                        'warranty_start_date' => $startDate->toDateString(),
                        'warranty_end_date' => $endDate->toDateString(),
                        'warranty_period_months' => $warrantyPeriod,
                        'warranty_type' => 'standard',
                        'status' => 'active',
                        'created_by' => Auth::id(),
                    ]
                );

                // Cập nhật thông tin bảo hành nếu cần
                if ($warranty->warranty_start_date != $startDate->toDateString() || $warranty->warranty_end_date != $endDate->toDateString()) {
                    $warranty->update([
                        'warranty_start_date' => $startDate->toDateString(),
                        'warranty_end_date' => $endDate->toDateString(),
                        'warranty_period_months' => $warrantyPeriod,
                        'project_name' => $standardProjectName,
                        'customer_name' => $project->customer->name ?? '',
                        'customer_phone' => $project->customer->phone ?? '',
                        'customer_email' => $project->customer->email ?? '',
                        'customer_address' => $project->customer->address ?? '',
                    ]);
                }
            }
        }
    }
}
