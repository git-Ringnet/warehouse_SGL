<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerMaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class CustomerMaintenanceRequestController extends Controller
{
    /**
     * Khởi tạo controller
     */
    public function __construct()
    {
        // Auth middleware được áp dụng trong web.php
    }

    /**
     * Kiểm tra xem người dùng có quyền truy cập
     */
    private function checkAccess()
    {
        if (Auth::guard('web')->check()) {
            // Kiểm tra nếu là người dùng admin
            return true;
        } elseif (Auth::guard('customer')->check()) {
            return true;
        }
        abort(403, 'Unauthorized');
    }

    /**
     * Hiển thị danh sách phiếu yêu cầu bảo trì của khách hàng
     */
    public function index()
    {
        $this->checkAccess();
        
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer) {
                $requests = CustomerMaintenanceRequest::forCustomer($customer->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                    
                return view('requests.customer-maintenance.index', compact('requests'));
            }
        }
        
        $requests = CustomerMaintenanceRequest::orderBy('created_at', 'desc')
            ->paginate(10);

        return view('requests.customer-maintenance.index', compact('requests'));
    }

    /**
     * Hiển thị form tạo phiếu yêu cầu bảo trì mới
     */
    public function create()
    {
        // Kiểm tra quyền truy cập
        if (Auth::guard('web')->check()) {
            // Nếu là nhân viên, chuyển hướng về trang index với thông báo
            return redirect()->route('requests.index')
                ->with('warning', 'Tính năng tạo phiếu yêu cầu bảo trì chỉ dành cho khách hàng.');
        }
        
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập với tài khoản khách hàng để sử dụng chức năng này.');
        }
        
        $request = new CustomerMaintenanceRequest();
        $request->request_date = Carbon::now()->format('Y-m-d');
        
        // Lấy thông tin của khách hàng
        $customerUser = Auth::guard('customer')->user();
        $customer = $customerUser->customer;
        
        if (!$customer) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Không tìm thấy thông tin khách hàng của bạn.');
        }
        
        $request->customer_id = $customer->id;
        $request->customer_name = $customer->company_name ?? $customer->name;
        $request->customer_phone = $customer->phone;
        $request->customer_email = $customer->email;
        $request->customer_address = $customer->address;
        
        // Lấy danh sách dự án của khách hàng
        $customerProjects = \App\Models\Project::where('customer_id', $customer->id)
            ->orderBy('project_name')
            ->get();
            
        // Lấy danh sách thuê thiết bị của khách hàng
        $customerRentals = \App\Models\Rental::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('requests.customer-maintenance.create', compact('request', 'customerProjects', 'customerRentals'));
    }

    /**
     * Lưu phiếu yêu cầu bảo trì mới
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền truy cập
        if (Auth::guard('web')->check()) {
            // Nếu là nhân viên, chuyển hướng về trang index với thông báo
            return redirect()->route('requests.index')
                ->with('warning', 'Tính năng tạo phiếu yêu cầu bảo trì chỉ dành cho khách hàng.');
        }
        
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập với tài khoản khách hàng để sử dụng chức năng này.');
        }
        
        $customerUser = Auth::guard('customer')->user();
        $customer = $customerUser->customer;
        
        if (!$customer) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Không tìm thấy thông tin khách hàng của bạn.');
        }
        // Nếu là sao chép từ phiếu khác
        if ($request->has('copy_from')) {
            $originalRequest = CustomerMaintenanceRequest::findOrFail($request->copy_from);
            
            // Sử dụng transaction để tránh race condition
            $newRequest = null;
            DB::transaction(function () use ($originalRequest, &$newRequest) {
                // Tạo mã phiếu mới an toàn
                $requestCode = $this->generateUniqueRequestCode();
                
                // Tạo phiếu mới với thông tin từ phiếu cũ
                $newRequest = new CustomerMaintenanceRequest();
                $newRequest->request_code = $requestCode;
                $newRequest->customer_id = $originalRequest->customer_id;
                $newRequest->customer_name = $originalRequest->customer_name;
                $newRequest->customer_phone = $originalRequest->customer_phone;
                $newRequest->customer_email = $originalRequest->customer_email;
                $newRequest->customer_address = $originalRequest->customer_address;
                $newRequest->project_name = $originalRequest->project_name;
                $newRequest->project_description = $originalRequest->project_description;
                $newRequest->request_date = now();
                $newRequest->maintenance_reason = $originalRequest->maintenance_reason;
                $newRequest->maintenance_details = $originalRequest->maintenance_details;
                $newRequest->priority = $originalRequest->priority;
                $newRequest->status = 'pending';
                $newRequest->notes = $originalRequest->notes;
                
                $newRequest->save();
            });
            
            // Kiểm tra xem phiếu đã được tạo thành công chưa
            if (!$newRequest) {
                return redirect()->back()
                    ->with('error', 'Có lỗi xảy ra khi sao chép phiếu yêu cầu bảo trì.')
                    ->withInput();
            }
            
            // Ghi nhật ký tạo phiếu yêu cầu bảo trì từ sao chép
            if (Auth::guard('customer')->check()) {
                $userId = Auth::guard('customer')->user()->id;
                \App\Models\UserLog::logActivity(
                    $userId,
                    'create',
                    'customer_maintenance_requests',
                    'Tạo phiếu khách yêu cầu bảo trì (sao chép): ' . $newRequest->request_code,
                    null,
                    $newRequest->toArray()
                );
            }
            
            return redirect()->route('customer-maintenance.show', $newRequest->id)
                ->with('success', 'Đã sao chép phiếu yêu cầu bảo trì thành công.');
        }
        
        // Xử lý tạo phiếu mới
        $rules = [
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'maintenance_reason' => 'required|string',
            'maintenance_details' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
            'item_source' => 'required|in:project,rental',
        ];

        // Thêm validation cho project_id hoặc rental_id tùy theo item_source
        if ($request->item_source === 'project') {
            $rules['project_id'] = 'required|exists:projects,id';
        } elseif ($request->item_source === 'rental') {
            $rules['rental_id'] = 'required|exists:rentals,id';
        }

        $validatedData = $request->validate($rules);

        // Sử dụng transaction để tránh race condition
        $maintenanceRequest = null;
        DB::transaction(function () use ($validatedData, $customer, &$maintenanceRequest) {
            // Tạo mã phiếu mới an toàn trong transaction
            $requestCode = $this->generateUniqueRequestCode();

            // Thêm thông tin khách hàng và các trường bổ sung
            $validatedData['request_code'] = $requestCode;
            $validatedData['request_date'] = now();
            $validatedData['status'] = 'pending';
            $validatedData['customer_id'] = $customer->id;
            $validatedData['customer_name'] = $customer->company_name ?? $customer->name;
            $validatedData['customer_phone'] = $customer->phone;
            $validatedData['customer_email'] = $customer->email;
            $validatedData['customer_address'] = $customer->address;

            // Lưu phiếu yêu cầu
            $maintenanceRequest = CustomerMaintenanceRequest::create($validatedData);
        });

        // Kiểm tra xem phiếu đã được tạo thành công chưa
        if (!$maintenanceRequest) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo phiếu yêu cầu bảo trì.')
                ->withInput();
        }

        // Gửi thông báo cho tất cả admin
        $admins = Employee::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            Notification::createNotification(
                'Phiếu khách yêu cầu bảo trì mới',
                'Khách hàng ' . $maintenanceRequest->customer_name . ' đã tạo phiếu yêu cầu bảo trì ' . $maintenanceRequest->project_name,
                'info',
                $admin->id,
                'customer_maintenance_request',
                $maintenanceRequest->id,
                route('customer-maintenance.show', $maintenanceRequest->id)
            );
        }

        // Ghi nhật ký tạo phiếu yêu cầu bảo trì mới
        if (Auth::guard('customer')->check()) {
            $userId = Auth::guard('customer')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'create',
                'customer_maintenance_requests',
                'Tạo phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                null,
                $maintenanceRequest->toArray()
            );
        }

        return redirect()->route('customer-maintenance.show', $maintenanceRequest->id)
            ->with('success', 'Đã tạo phiếu yêu cầu bảo trì thành công.');
    }

    /**
     * Hiển thị chi tiết phiếu yêu cầu bảo trì
     */
    public function show(string $id)
    {
        $this->checkAccess();
        
        $request = CustomerMaintenanceRequest::with(['customer', 'approvedByUser'])->findOrFail($id);
        
        // Kiểm tra quyền truy cập
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer && $request->customer_id != $customer->id) {
                abort(403, 'Unauthorized');
            }
        }
        
        // Ghi nhật ký xem chi tiết phiếu yêu cầu bảo trì
        if (Auth::guard('customer')->check()) {
            $userId = Auth::guard('customer')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'view',
                'customer_maintenance_requests',
                'Xem chi tiết phiếu khách yêu cầu bảo trì: ' . $request->request_code,
                null,
                ['id' => $request->id, 'code' => $request->request_code]
            );
        } elseif (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'view',
                'customer_maintenance_requests',
                'Xem chi tiết phiếu khách yêu cầu bảo trì: ' . $request->request_code,
                null,
                ['id' => $request->id, 'code' => $request->request_code]
            );
        }
        
        return view('requests.customer-maintenance.show', compact('request'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu yêu cầu bảo trì
     */
    public function edit(string $id)
    {
        $this->checkAccess();
        
        $request = CustomerMaintenanceRequest::findOrFail($id);
        
        // Chỉ cho phép chỉnh sửa khi phiếu còn ở trạng thái chờ duyệt
        if ($request->status !== 'pending') {
            return redirect()->route('customer-maintenance.show', $id)
                ->with('error', 'Không thể chỉnh sửa phiếu yêu cầu đã được duyệt.');
        }
        
        // Kiểm tra quyền truy cập
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer && $request->customer_id != $customer->id) {
                abort(403, 'Unauthorized');
            }
        }
        
        $customers = Customer::orderBy('company_name')->get();
        
        return view('requests.customer-maintenance.edit', compact('request', 'customers'));
    }

    /**
     * Cập nhật phiếu yêu cầu bảo trì
     */
    public function update(Request $request, string $id)
    {
        $this->checkAccess();
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        
        // Chỉ cho phép cập nhật khi phiếu còn ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('customer-maintenance.show', $id)
                ->with('error', 'Không thể cập nhật phiếu yêu cầu đã được duyệt.');
        }
        
        // Kiểm tra quyền truy cập
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer && $maintenanceRequest->customer_id != $customer->id) {
                abort(403, 'Unauthorized');
            }
        }

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $maintenanceRequest->toArray();
        
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_if:customer_id,null|max:255',
            'customer_phone' => 'nullable|max:20',
            'customer_email' => 'nullable|email|max:255',
            'project_name' => 'required|max:255',
            'request_date' => 'required|date',
            'maintenance_reason' => 'required',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $maintenanceRequest->customer_id = $request->customer_id;
        $maintenanceRequest->customer_name = $request->customer_name;
        $maintenanceRequest->customer_phone = $request->customer_phone;
        $maintenanceRequest->customer_email = $request->customer_email;
        $maintenanceRequest->customer_address = $request->customer_address;
        $maintenanceRequest->project_name = $request->project_name;
        $maintenanceRequest->project_description = $request->project_description;
        $maintenanceRequest->request_date = $request->request_date;
        $maintenanceRequest->maintenance_reason = $request->maintenance_reason;
        $maintenanceRequest->maintenance_details = $request->maintenance_details;
        $maintenanceRequest->priority = $request->priority;
        $maintenanceRequest->notes = $request->notes;
        $maintenanceRequest->save();

        // Ghi nhật ký cập nhật phiếu yêu cầu bảo trì
        if (Auth::guard('customer')->check()) {
            $userId = Auth::guard('customer')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'update',
                'customer_maintenance_requests',
                'Cập nhật phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                $oldData,
                $maintenanceRequest->toArray()
            );
        } elseif (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'update',
                'customer_maintenance_requests',
                'Cập nhật phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                $oldData,
                $maintenanceRequest->toArray()
            );
        }

        return redirect()->route('customer-maintenance.show', $maintenanceRequest->id)
            ->with('success', 'Cập nhật phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Xóa phiếu yêu cầu bảo trì
     */
    public function destroy(string $id)
    {
        $this->checkAccess();
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        $requestCode = $maintenanceRequest->request_code;
        $requestData = $maintenanceRequest->toArray();
        
        // Chỉ cho phép xóa khi phiếu còn ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('customer-maintenance.show', $id)
                ->with('error', 'Không thể xóa phiếu yêu cầu đã được duyệt.');
        }
        
        // Kiểm tra quyền truy cập
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer && $maintenanceRequest->customer_id != $customer->id) {
                abort(403, 'Unauthorized');
            }
        }
        
        $maintenanceRequest->delete();
        
        // Ghi nhật ký xóa phiếu yêu cầu bảo trì
        if (Auth::guard('customer')->check()) {
            $userId = Auth::guard('customer')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'delete',
                'customer_maintenance_requests',
                'Xóa phiếu khách yêu cầu bảo trì: ' . $requestCode,
                $requestData,
                null
            );
        } elseif (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'delete',
                'customer_maintenance_requests',
                'Xóa phiếu khách yêu cầu bảo trì: ' . $requestCode,
                $requestData,
                null
            );
        }
        
        return redirect()->route('customer.dashboard')
            ->with('success', 'Xóa phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Hiển thị xem trước phiếu yêu cầu bảo trì
     */
    public function preview(string $id)
    {
        $this->checkAccess();
        
        $request = CustomerMaintenanceRequest::with(['customer', 'approvedByUser'])->findOrFail($id);
        
        // Kiểm tra quyền truy cập
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer && $request->customer_id != $customer->id) {
                abort(403, 'Unauthorized');
            }
        }
        
        return view('requests.customer-maintenance.preview', compact('request'));
    }

    /**
     * Duyệt phiếu yêu cầu bảo trì
     */
    public function approve(Request $request, string $id)
    {
        $this->checkAccess();
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        $oldData = $maintenanceRequest->toArray();
        
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('customer-maintenance.show', $id)
                ->with('error', 'Phiếu yêu cầu này không ở trạng thái chờ duyệt.');
        }
        
        $maintenanceRequest->status = 'approved';
        
        // Sửa: Chỉ lấy ID từ guard web (nhân viên), không lấy từ guard customer
        if (Auth::guard('web')->check()) {
            $maintenanceRequest->approved_by = Auth::guard('web')->id();
        } else {
            // Trường hợp không phải nhân viên (không nên xảy ra do middleware)
            return redirect()->route('customer-maintenance.show', $id)
                ->with('error', 'Bạn không có quyền duyệt phiếu yêu cầu này.');
        }
        
        $maintenanceRequest->approved_at = now();
        $maintenanceRequest->save();

        // Ghi nhật ký duyệt phiếu yêu cầu bảo trì
        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'approve',
                'customer_maintenance_requests',
                'Duyệt phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                $oldData,
                $maintenanceRequest->toArray()
            );
        }
        
        return redirect()->route('customer-maintenance.show', $id)
            ->with('success', 'Đã duyệt phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Từ chối phiếu yêu cầu bảo trì
     */
    public function reject(Request $request, string $id)
    {
        $this->checkAccess();
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        $oldData = $maintenanceRequest->toArray();
        
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('customer-maintenance.show', $id)
                ->with('error', 'Phiếu yêu cầu này không ở trạng thái chờ duyệt.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);
        
        $maintenanceRequest->status = 'rejected';
        $maintenanceRequest->rejection_reason = $request->rejection_reason;
        $maintenanceRequest->save();

        // Ghi nhật ký từ chối phiếu yêu cầu bảo trì
        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            \App\Models\UserLog::logActivity(
                $userId,
                'reject',
                'customer_maintenance_requests',
                'Từ chối phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                $oldData,
                $maintenanceRequest->toArray()
            );
        }
        
        return redirect()->route('customer-maintenance.show', $id)
            ->with('success', 'Đã từ chối phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Hỗ trợ tạo mã phiếu yêu cầu bảo trì duy nhất
     */
    private function generateUniqueRequestCode()
    {
        do {
            // Lấy phiếu cuối cùng để tạo số tiếp theo
            $latestRequest = CustomerMaintenanceRequest::orderBy('id', 'desc')->first();
            $requestNumber = $latestRequest ? intval(substr($latestRequest->request_code, -4)) + 1 : 1;
            $requestCode = 'CUST-MAINT-' . str_pad($requestNumber, 4, '0', STR_PAD_LEFT);
            
            // Kiểm tra xem mã này đã tồn tại chưa
            $exists = CustomerMaintenanceRequest::where('request_code', $requestCode)->exists();
            
            if (!$exists) {
                return $requestCode;
            }
            
            // Nếu mã đã tồn tại, tăng số lên và thử lại
            $requestNumber++;
            
        } while (true);
    }
}
