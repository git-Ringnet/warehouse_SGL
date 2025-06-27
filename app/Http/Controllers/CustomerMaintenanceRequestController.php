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
        $this->checkAccess();
        
        $customers = Customer::orderBy('company_name')->get();
        $request = new CustomerMaintenanceRequest();
        $request->request_date = Carbon::now()->format('Y-m-d');
        
        // Nếu là khách hàng đăng nhập, lấy thông tin của khách hàng
        if (Auth::guard('customer')->check()) {
            $customerUser = Auth::guard('customer')->user();
            $customer = $customerUser->customer;
            
            if ($customer) {
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
                    
                return view('requests.customer-maintenance.create', compact('customers', 'request', 'customerProjects', 'customerRentals'));
            }
        }
        
        return view('requests.customer-maintenance.create', compact('customers', 'request'));
    }

    /**
     * Lưu phiếu yêu cầu bảo trì mới
     */
    public function store(Request $request)
    {
        $this->checkAccess();
        
        // Nếu là sao chép từ phiếu khác
        if ($request->has('copy_from')) {
            $originalRequest = CustomerMaintenanceRequest::findOrFail($request->copy_from);
            
            // Tạo mã phiếu mới
            $latestRequest = CustomerMaintenanceRequest::latest()->first();
            $requestNumber = $latestRequest ? intval(substr($latestRequest->request_code, -4)) + 1 : 1;
            $requestCode = 'CUST-MAINT-' . str_pad($requestNumber, 4, '0', STR_PAD_LEFT);
            
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
            $newRequest->expected_completion_date = $originalRequest->expected_completion_date;
            $newRequest->priority = $originalRequest->priority;
            $newRequest->status = 'pending';
            $newRequest->notes = $originalRequest->notes;
            
            $newRequest->save();
            
            return redirect()->route('requests.customer-maintenance.show', $newRequest->id)
                ->with('success', 'Đã sao chép phiếu yêu cầu bảo trì thành công.');
        }

        // Xử lý tạo phiếu mới bình thường
        $validatedData = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'maintenance_reason' => 'required|string',
            'maintenance_details' => 'nullable|string',
            'expected_completion_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
        ]);

        // Tạo mã phiếu mới
        $latestRequest = CustomerMaintenanceRequest::latest()->first();
        $requestNumber = $latestRequest ? intval(substr($latestRequest->request_code, -4)) + 1 : 1;
        $requestCode = 'CUST-MAINT-' . str_pad($requestNumber, 4, '0', STR_PAD_LEFT);

        // Thêm các trường bổ sung
        $validatedData['request_code'] = $requestCode;
        $validatedData['request_date'] = now();
        $validatedData['status'] = 'pending';

        // Lưu phiếu yêu cầu
        $maintenanceRequest = CustomerMaintenanceRequest::create($validatedData);

        // Gửi thông báo cho tất cả admin (nhân viên có role là admin)
        $admins = Employee::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            Notification::createNotification(
                'Phiếu khách yêu cầu bảo trì mới',
                'Khách hàng ' . $maintenanceRequest->customer_name . ' đã tạo phiếu yêu cầu bảo trì ' . $maintenanceRequest->project_name,
                'info',
                $admin->id,
                'customer_maintenance_request',
                $maintenanceRequest->id,
                route('requests.customer-maintenance.show', $maintenanceRequest->id)
            );
        }

        return redirect()->route('requests.customer-maintenance.show', $maintenanceRequest->id)
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
            return redirect()->route('requests.customer-maintenance.show', $id)
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
            return redirect()->route('requests.customer-maintenance.show', $id)
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
        $maintenanceRequest->expected_completion_date = $request->expected_completion_date;
        $maintenanceRequest->estimated_cost = $request->estimated_cost;
        $maintenanceRequest->priority = $request->priority;
        $maintenanceRequest->notes = $request->notes;
        $maintenanceRequest->save();

        return redirect()->route('requests.customer-maintenance.show', $maintenanceRequest->id)
            ->with('success', 'Cập nhật phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Xóa phiếu yêu cầu bảo trì
     */
    public function destroy(string $id)
    {
        $this->checkAccess();
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        
        // Chỉ cho phép xóa khi phiếu còn ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('requests.customer-maintenance.show', $id)
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
        
        return redirect()->route('requests.index')
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
        
        // Chỉ admin mới có quyền duyệt
        if (!Auth::guard('web')->check()) {
            abort(403, 'Unauthorized');
        }
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('requests.customer-maintenance.show', $id)
                ->with('error', 'Phiếu yêu cầu này không ở trạng thái chờ duyệt.');
        }
        
        $maintenanceRequest->status = 'approved';
        $maintenanceRequest->approved_by = Auth::id();
        $maintenanceRequest->approved_at = now();
        $maintenanceRequest->save();
        
        return redirect()->route('requests.customer-maintenance.show', $id)
            ->with('success', 'Đã duyệt phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Từ chối phiếu yêu cầu bảo trì
     */
    public function reject(Request $request, string $id)
    {
        $this->checkAccess();
        
        // Chỉ admin mới có quyền từ chối
        if (!Auth::guard('web')->check()) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'rejection_reason' => 'required',
        ]);
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->route('requests.customer-maintenance.show', $id)
                ->with('error', 'Phiếu yêu cầu này không ở trạng thái chờ duyệt.');
        }
        
        $maintenanceRequest->status = 'rejected';
        $maintenanceRequest->rejection_reason = $request->rejection_reason;
        $maintenanceRequest->save();
        
        return redirect()->route('requests.customer-maintenance.show', $id)
            ->with('success', 'Đã từ chối phiếu yêu cầu bảo trì.');
    }

    /**
     * Cập nhật trạng thái phiếu yêu cầu bảo trì
     */
    public function updateStatus(Request $request, string $id)
    {
        $this->checkAccess();
        
        // Chỉ admin mới có quyền cập nhật trạng thái
        if (!Auth::guard('web')->check()) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,in_progress,completed,canceled',
        ]);
        
        $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);
        $maintenanceRequest->status = $request->status;
        
        if ($request->status === 'rejected' && $request->filled('rejection_reason')) {
            $maintenanceRequest->rejection_reason = $request->rejection_reason;
        }
        
        $maintenanceRequest->save();
        
        return redirect()->route('requests.customer-maintenance.show', $id)
            ->with('success', 'Đã cập nhật trạng thái phiếu yêu cầu bảo trì.');
    }
}
