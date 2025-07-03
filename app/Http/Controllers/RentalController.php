<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\UserLog;
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
        
        $rentals = $query->latest()->paginate(10);
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $rentals->appends([
            'search' => $search,
            'filter' => $filter
        ]);
        
        return view('rentals.index', compact('rentals', 'search', 'filter'));
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
        
        // Lấy danh sách thiết bị dự phòng cho bảo hành/thay thế
        $backupItems = collect();
        $dispatches = \App\Models\Dispatch::where('dispatch_type', 'rental')
            ->whereIn('status', ['approved', 'completed'])
            ->where(function($query) use ($rental) {
                $query->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                    ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
            })
            ->get();
            
        foreach ($dispatches as $dispatch) {
            $items = $dispatch->items()->where('category', 'backup')->get();
            $backupItems = $backupItems->concat($items);
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
        
        return view('rentals.show', compact('rental', 'warehouses', 'backupItems'));
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
    public function destroy($id)
    {
        try {
            $rental = Rental::findOrFail($id);

            // Lưu dữ liệu cũ trước khi xóa
            $oldData = $rental->toArray();
            $rentalCode = $rental->rental_code;

            // Kiểm tra xem phiếu cho thuê có phiếu xuất kho liên quan không
            $dispatchCount = \App\Models\Dispatch::where('dispatch_type', 'rental')
                ->where(function($query) use ($rental) {
                    $query->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                        ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                })
                ->count();
            
            if ($dispatchCount > 0) {
                return redirect()->route('rentals.show', $id)
                    ->with('error', 'Không thể xóa phiếu cho thuê này vì có ' . $dispatchCount . ' phiếu xuất kho liên quan. Vui lòng xóa các phiếu xuất kho trước khi xóa phiếu cho thuê.');
            }
            
            // Nếu không có phiếu xuất kho liên quan, tiến hành xóa phiếu cho thuê
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
            ->whereIn('status', ['approved', 'completed'])
            ->where(function($query) use ($rental) {
                $query->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                    ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
            })
            ->get();
            
        $allItems = collect();
        
        foreach ($dispatches as $dispatch) {
            $items = $dispatch->items()
                ->with(['product'])
                ->where('category', 'contract')
                ->where('item_type', 'product') // Chỉ lấy products
                ->get()
                ->map(function ($item) use ($rental) {
                    return [
                        'id' => $item->id,
                        'type' => 'product',
                        'name' => $item->product->name,
                        'serial_number' => $item->serial_number,
                        'description' => $item->product->description,
                        'rental_code' => $rental->rental_code
                    ];
                });
                
            $allItems = $allItems->concat($items);
        }
        
        return response()->json($allItems);
    }
} 