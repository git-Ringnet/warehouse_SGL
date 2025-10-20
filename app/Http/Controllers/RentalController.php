<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\UserLog;
use App\Models\Notification;
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
                        $query->whereHas('customer', function($q) use ($search) {
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
                      ->orWhereHas('customer', function($subq) use ($search) {
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
        
        // Biến đếm virtual serial chung cho toàn bộ rental
        $virtualSerialCounter = 0;
        
        // Lấy danh sách thiết bị theo hợp đồng với chi tiết từng thiết bị (ƯU TIÊN TRƯỚC)
        $contractItems = collect();
        $dispatches = \App\Models\Dispatch::where('dispatch_type', 'rental')
            ->whereIn('status', ['approved', 'completed'])
            ->where('project_id', $rental->id) // Tìm theo project_id = rental_id
            ->get();
            
        foreach ($dispatches as $dispatch) {
            $items = $dispatch->items()->where('category', 'contract')->get();
            
            foreach ($items as $item) {
                $serialNumbers = $item->serial_numbers ?? [];
                $quantity = (int)$item->quantity;
                
                // Tạo bản ghi cho TẤT CẢ serial (bao gồm cả virtual serial đã lưu trong DB)
                foreach ($serialNumbers as $i => $serial) {
                    $serial = trim($serial);
                    if (!empty($serial)) {
                        $isVirtual = strpos($serial, 'N/A-') === 0;
                        
                        $contractItems->push([
                            'dispatch_item' => $item,
                            'dispatch' => $dispatch,
                            'serial_index' => $i,
                            'serial_number' => $serial,
                            'has_serial' => !$isVirtual
                        ]);
                        
                        // Cập nhật counter nếu là virtual serial
                        if ($isVirtual) {
                            $counter = (int)str_replace('N/A-', '', $serial);
                            $virtualSerialCounter = max($virtualSerialCounter, $counter + 1);
                        }
                    }
                }
                
                // Nếu quantity > số serial trong DB → tạo virtual serial mới
                $currentSerialCount = count($serialNumbers);
                if ($quantity > $currentSerialCount) {
                    $needNewVirtuals = $quantity - $currentSerialCount;
                    
                    for ($i = 0; $i < $needNewVirtuals; $i++) {
                        $contractItems->push([
                            'dispatch_item' => $item,
                            'dispatch' => $dispatch,
                            'serial_index' => $currentSerialCount + $i,
                            'serial_number' => "N/A-{$virtualSerialCounter}",
                            'has_serial' => false
                        ]);
                        $virtualSerialCounter++;
                    }
                }
            }
        }

        // Lấy danh sách thiết bị dự phòng cho bảo hành/thay thế (SAU HỢP ĐỒNG)
        $backupItems = collect();
        foreach ($dispatches as $dispatch) {
            $items = $dispatch->items()->where('category', 'backup')->get();
            
            foreach ($items as $item) {
                $serialNumbers = $item->serial_numbers ?? [];
                $quantity = (int)$item->quantity;
                
                // Tạo bản ghi cho TẤT CẢ serial (bao gồm cả virtual serial đã lưu trong DB)
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
                        
                        // Cập nhật counter nếu là virtual serial
                        if ($isVirtual) {
                            $counter = (int)str_replace('N/A-', '', $serial);
                            $virtualSerialCounter = max($virtualSerialCounter, $counter + 1);
                        }
                    }
                }
                
                // Nếu quantity > số serial trong DB → tạo virtual serial mới
                $currentSerialCount = count($serialNumbers);
                if ($quantity > $currentSerialCount) {
                    $needNewVirtuals = $quantity - $currentSerialCount;
                    
                    for ($i = 0; $i < $needNewVirtuals; $i++) {
                        $backupItems->push([
                            'dispatch_item' => $item,
                            'dispatch' => $dispatch,
                            'serial_index' => $currentSerialCount + $i,
                            'serial_number' => "N/A-{$virtualSerialCounter}",
                            'has_serial' => false
                        ]);
                        $virtualSerialCounter++;
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
            'rental_code' => 'required|string|max:255|unique:rentals,rental_code,'.$id,
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

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Phiếu cho thuê đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa phiếu cho thuê khỏi database
     */
    /**
     * Đồng bộ bảo hành điện tử khi cập nhật phiếu cho thuê
     */
    private function syncWarrantiesFromRental(Rental $rental)
    {
        $warranties = \App\Models\Warranty::whereIn('item_type', ['rental','project'])
            ->where('item_id', $rental->id)
            ->get();

        if ($warranties->isEmpty()) {
            return;
        }

        $customerName = optional($rental->customer)->name;
        $startDate = \Carbon\Carbon::parse($rental->rental_date);
        $endDate   = \Carbon\Carbon::parse($rental->due_date);
        $periodMonths = max(1, $startDate->diffInMonths($endDate) ?: 1);

        foreach ($warranties as $warranty) {
            $warranty->update([
                'project_name'           => $rental->rental_name,
                'customer_name'          => $customerName,
                'warranty_start_date'    => $startDate->toDateString(),
                'warranty_end_date'      => $endDate->toDateString(),
                'warranty_period_months' => $periodMonths,
            ]);
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
                ->with('success', 'Phiếu cho thuê đã được gia hạn thành công.');
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
        $dispatches = \App\Models\Dispatch::where('dispatch_type', 'rental')
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
                            $serialNumbersArray = array_filter($serialNumbers, function($serial) {
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
                            $serialNumbersArray = array_filter($serialNumbers, function($serial) {
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
        
        $dispatches = \App\Models\Dispatch::where('dispatch_type', 'rental')
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