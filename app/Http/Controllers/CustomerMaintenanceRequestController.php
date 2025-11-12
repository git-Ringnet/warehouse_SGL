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
use Illuminate\Support\Facades\Log;
use App\Helpers\DateHelper;

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
     * API: Lấy TẤT CẢ phiếu khách yêu cầu bảo trì (không lọc, không phân trang)
     */
    public function apiGetAll()
    {
        try {
            $customerMaintenanceRequests = CustomerMaintenanceRequest::with(['customer', 'project', 'rental', 'approvedByUser'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format dữ liệu trả về
            $data = $customerMaintenanceRequests->map(function ($item) {
                return [
                    'id' => $item->id,
                    'request_code' => $item->request_code,
                    'request_date' => $item->request_date ? $item->request_date->format('Y-m-d') : null,
                    'maintenance_reason' => $item->maintenance_reason,
                    'maintenance_details' => $item->maintenance_details,
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'item_source' => $item->item_source,
                    'project_id' => $item->project_id,
                    'rental_id' => $item->rental_id,
                    'project_name' => $item->project_name,
                    'project_description' => $item->project_description,
                    'selected_item' => $item->selected_item,
                    'estimated_cost' => $item->estimated_cost ? (float)$item->estimated_cost : null,
                    'customer_id' => $item->customer_id,
                    'customer_name' => $item->customer_name,
                    'customer_phone' => $item->customer_phone,
                    'customer_email' => $item->customer_email,
                    'customer_address' => $item->customer_address,
                    'notes' => $item->notes,
                    'rejection_reason' => $item->rejection_reason,
                    'approved_by' => $item->approved_by,
                    'approved_at' => $item->approved_at ? $item->approved_at->format('Y-m-d H:i:s') : null,
                    'customer' => $item->customer ? [
                        'id' => $item->customer->id,
                        'name' => $item->customer->name,
                        'company_name' => $item->customer->company_name,
                        'phone' => $item->customer->phone,
                        'email' => $item->customer->email,
                    ] : null,
                    'project' => $item->project ? [
                        'id' => $item->project->id,
                        'project_code' => $item->project->project_code,
                        'project_name' => $item->project->project_name,
                    ] : null,
                    'rental' => $item->rental ? [
                        'id' => $item->rental->id,
                        'rental_code' => $item->rental->rental_code,
                        'rental_name' => $item->rental->rental_name,
                    ] : null,
                    'approved_by_user' => $item->approvedByUser ? [
                        'id' => $item->approvedByUser->id,
                        'name' => $item->approvedByUser->name,
                        'username' => $item->approvedByUser->username,
                        'email' => $item->approvedByUser->email,
                    ] : null,
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $data->count()
            ]);
        } catch (\Exception $e) {
            Log::error('API get all customer maintenance requests error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách phiếu khách yêu cầu bảo trì: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy danh sách phiếu khách yêu cầu bảo trì - có lọc và phân trang
     */
    public function apiIndex(Request $request)
    {
        try {
            $query = CustomerMaintenanceRequest::with(['customer', 'project', 'rental', 'approvedByUser']);

            // Tìm kiếm
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('request_code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('project_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customer_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('maintenance_reason', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                            $customerQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('company_name', 'LIKE', "%{$searchTerm}%");
                        });
                });
            }

            // Lọc theo trạng thái
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Lọc theo priority
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Lọc theo item_source
            if ($request->filled('item_source')) {
                $query->where('item_source', $request->item_source);
            }

            // Lọc theo project_id
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // Lọc theo rental_id
            if ($request->filled('rental_id')) {
                $query->where('rental_id', $request->rental_id);
            }

            // Lọc theo customer_id
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Lọc theo approved_by
            if ($request->filled('approved_by')) {
                $query->where('approved_by', $request->approved_by);
            }

            // Lọc theo khoảng thời gian request_date
            if ($request->filled('request_date_from')) {
                $dateFrom = DateHelper::convertToDatabaseFormat($request->request_date_from);
                $query->whereDate('request_date', '>=', $dateFrom);
            }
            if ($request->filled('request_date_to')) {
                $dateTo = DateHelper::convertToDatabaseFormat($request->request_date_to);
                $query->whereDate('request_date', '<=', $dateTo);
            }

            // Lọc theo khoảng thời gian approved_at
            if ($request->filled('approved_at_from')) {
                $dateFrom = DateHelper::convertToDatabaseFormat($request->approved_at_from);
                $query->whereDate('approved_at', '>=', $dateFrom);
            }
            if ($request->filled('approved_at_to')) {
                $dateTo = DateHelper::convertToDatabaseFormat($request->approved_at_to);
                $query->whereDate('approved_at', '<=', $dateTo);
            }

            // Sắp xếp
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSortFields = ['id', 'request_code', 'request_date', 'priority', 'status', 'created_at', 'updated_at', 'approved_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Phân trang
            $perPage = $request->get('per_page', 15);
            $perPage = min(max(1, (int)$perPage), 100); // Giới hạn từ 1 đến 100

            $customerMaintenanceRequests = $query->paginate($perPage);

            // Format dữ liệu trả về
            $data = $customerMaintenanceRequests->map(function ($item) {
                return [
                    'id' => $item->id,
                    'request_code' => $item->request_code,
                    'request_date' => $item->request_date ? $item->request_date->format('Y-m-d') : null,
                    'maintenance_reason' => $item->maintenance_reason,
                    'maintenance_details' => $item->maintenance_details,
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'item_source' => $item->item_source,
                    'project_id' => $item->project_id,
                    'rental_id' => $item->rental_id,
                    'project_name' => $item->project_name,
                    'project_description' => $item->project_description,
                    'selected_item' => $item->selected_item,
                    'estimated_cost' => $item->estimated_cost ? (float)$item->estimated_cost : null,
                    'customer_id' => $item->customer_id,
                    'customer_name' => $item->customer_name,
                    'customer_phone' => $item->customer_phone,
                    'customer_email' => $item->customer_email,
                    'customer_address' => $item->customer_address,
                    'notes' => $item->notes,
                    'rejection_reason' => $item->rejection_reason,
                    'approved_by' => $item->approved_by,
                    'approved_at' => $item->approved_at ? $item->approved_at->format('Y-m-d H:i:s') : null,
                    'customer' => $item->customer ? [
                        'id' => $item->customer->id,
                        'name' => $item->customer->name,
                        'company_name' => $item->customer->company_name,
                        'phone' => $item->customer->phone,
                        'email' => $item->customer->email,
                    ] : null,
                    'project' => $item->project ? [
                        'id' => $item->project->id,
                        'project_code' => $item->project->project_code,
                        'project_name' => $item->project->project_name,
                    ] : null,
                    'rental' => $item->rental ? [
                        'id' => $item->rental->id,
                        'rental_code' => $item->rental->rental_code,
                        'rental_name' => $item->rental->rental_name,
                    ] : null,
                    'approved_by_user' => $item->approvedByUser ? [
                        'id' => $item->approvedByUser->id,
                        'name' => $item->approvedByUser->name,
                        'username' => $item->approvedByUser->username,
                        'email' => $item->approvedByUser->email,
                    ] : null,
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $customerMaintenanceRequests->currentPage(),
                    'per_page' => $customerMaintenanceRequests->perPage(),
                    'total' => $customerMaintenanceRequests->total(),
                    'last_page' => $customerMaintenanceRequests->lastPage(),
                    'from' => $customerMaintenanceRequests->firstItem(),
                    'to' => $customerMaintenanceRequests->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API list customer maintenance requests error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách phiếu khách yêu cầu bảo trì: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Tạo phiếu khách yêu cầu bảo trì
     */
    public function apiStore(Request $request)
    {
        try {
            // Validation
            $rules = [
                'project_name' => 'required|string|max:255',
                'project_description' => 'nullable|string',
                'maintenance_reason' => 'required|string',
                'maintenance_details' => 'nullable|string',
                'priority' => 'required|in:low,medium,high,urgent',
                'notes' => 'nullable|string',
                'item_source' => 'required|in:project,rental',
                'customer_id' => 'required|exists:customers,id',
                'request_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric|min:0',
                'selected_item' => 'nullable|string',
            ];

            // Thêm validation cho project_id hoặc rental_id tùy theo item_source
            if ($request->item_source === 'project') {
                $rules['project_id'] = 'required|exists:projects,id';
            } elseif ($request->item_source === 'rental') {
                $rules['rental_id'] = 'required|exists:rentals,id';
            }

            // Custom messages tiếng Việt
            $messages = [
                'project_name.required' => 'Trường tên dự án là bắt buộc.',
                'maintenance_reason.required' => 'Trường lý do bảo trì là bắt buộc.',
                'priority.required' => 'Trường mức độ ưu tiên là bắt buộc.',
                'priority.in' => 'Mức độ ưu tiên phải là: low, medium, high, hoặc urgent.',
                'item_source.required' => 'Trường nguồn thiết bị là bắt buộc.',
                'item_source.in' => 'Nguồn thiết bị phải là: project hoặc rental.',
                'customer_id.required' => 'Trường ID khách hàng là bắt buộc.',
                'customer_id.exists' => 'Khách hàng không tồn tại.',
                'project_id.required' => 'Trường ID dự án là bắt buộc khi item_source là project.',
                'project_id.exists' => 'Dự án không tồn tại.',
                'rental_id.required' => 'Trường ID phiếu cho thuê là bắt buộc khi item_source là rental.',
                'rental_id.exists' => 'Phiếu cho thuê không tồn tại.',
                'request_date.date' => 'Ngày yêu cầu không hợp lệ.',
                'estimated_cost.numeric' => 'Chi phí ước tính phải là số.',
                'estimated_cost.min' => 'Chi phí ước tính phải lớn hơn hoặc bằng 0.',
            ];

            $validatedData = $request->validate($rules, $messages);

            // Lấy thông tin khách hàng
            $customer = Customer::findOrFail($request->customer_id);
            
            // Kiểm tra trạng thái khách hàng
            if ($customer->is_locked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản khách hàng đã bị khóa.'
                ], 403);
            }

            // Sử dụng transaction
            $maintenanceRequest = null;
            $requestDate = $request->filled('request_date') 
                ? DateHelper::convertToDatabaseFormat($request->request_date) 
                : now()->format('Y-m-d');
            
            DB::transaction(function () use ($validatedData, $customer, $requestDate, &$maintenanceRequest) {
                // Tạo mã phiếu mới
                $requestCode = $this->generateUniqueRequestCode();

                // Thêm thông tin khách hàng và các trường bổ sung
                $validatedData['request_code'] = $requestCode;
                $validatedData['request_date'] = $requestDate;
                $validatedData['status'] = 'pending';
                $validatedData['customer_id'] = $customer->id;
                $validatedData['customer_name'] = $customer->company_name ?? $customer->name;
                $validatedData['customer_phone'] = $customer->phone;
                $validatedData['customer_email'] = $customer->email;
                $validatedData['customer_address'] = $customer->address ?? '';

                // Lưu phiếu yêu cầu
                $maintenanceRequest = CustomerMaintenanceRequest::create($validatedData);
            });

            if (!$maintenanceRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo phiếu yêu cầu bảo trì.'
                ], 500);
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
                    route('requests.customer-maintenance.show', $maintenanceRequest->id)
                );
            }

            // Load relationships
            $maintenanceRequest->load(['customer', 'project', 'rental']);

            return response()->json([
                'success' => true,
                'message' => 'Phiếu khách yêu cầu bảo trì đã được tạo thành công.',
                'data' => [
                    'customer_maintenance_request' => [
                        'id' => $maintenanceRequest->id,
                        'request_code' => $maintenanceRequest->request_code,
                        'status' => $maintenanceRequest->status,
                        'priority' => $maintenanceRequest->priority,
                        'project_name' => $maintenanceRequest->project_name,
                        'customer_name' => $maintenanceRequest->customer_name,
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API create customer maintenance request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo phiếu yêu cầu bảo trì: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Cập nhật phiếu khách yêu cầu bảo trì
     */
    public function apiUpdate(Request $request, $id)
    {
        try {
            $maintenanceRequest = CustomerMaintenanceRequest::findOrFail($id);

            // Chỉ cho phép cập nhật khi phiếu còn ở trạng thái chờ duyệt
            if ($maintenanceRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật phiếu yêu cầu đã được duyệt hoặc đã xử lý.'
                ], 403);
            }

            // Validation
            $rules = [
                'project_name' => 'sometimes|required|string|max:255',
                'project_description' => 'nullable|string',
                'maintenance_reason' => 'sometimes|required|string',
                'maintenance_details' => 'nullable|string',
                'priority' => 'sometimes|required|in:low,medium,high,urgent',
                'notes' => 'nullable|string',
                'item_source' => 'sometimes|required|in:project,rental',
                'request_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric|min:0',
                'selected_item' => 'nullable|string',
            ];

            // Thêm validation cho project_id hoặc rental_id nếu item_source được cập nhật
            if ($request->filled('item_source')) {
                if ($request->item_source === 'project') {
                    $rules['project_id'] = 'required|exists:projects,id';
                } elseif ($request->item_source === 'rental') {
                    $rules['rental_id'] = 'required|exists:rentals,id';
                }
            }

            // Custom messages tiếng Việt
            $messages = [
                'project_name.required' => 'Trường tên dự án là bắt buộc.',
                'maintenance_reason.required' => 'Trường lý do bảo trì là bắt buộc.',
                'priority.required' => 'Trường mức độ ưu tiên là bắt buộc.',
                'priority.in' => 'Mức độ ưu tiên phải là: low, medium, high, hoặc urgent.',
                'item_source.required' => 'Trường nguồn thiết bị là bắt buộc.',
                'item_source.in' => 'Nguồn thiết bị phải là: project hoặc rental.',
                'project_id.required' => 'Trường ID dự án là bắt buộc khi item_source là project.',
                'project_id.exists' => 'Dự án không tồn tại.',
                'rental_id.required' => 'Trường ID phiếu cho thuê là bắt buộc khi item_source là rental.',
                'rental_id.exists' => 'Phiếu cho thuê không tồn tại.',
                'request_date.date' => 'Ngày yêu cầu không hợp lệ.',
                'estimated_cost.numeric' => 'Chi phí ước tính phải là số.',
                'estimated_cost.min' => 'Chi phí ước tính phải lớn hơn hoặc bằng 0.',
            ];

            $validatedData = $request->validate($rules, $messages);

            // Chuẩn hóa ngày tháng nếu có
            if ($request->filled('request_date')) {
                $validatedData['request_date'] = DateHelper::convertToDatabaseFormat($request->request_date);
            }

            // Cập nhật phiếu
            $maintenanceRequest->update($validatedData);

            // Load relationships
            $maintenanceRequest->load(['customer', 'project', 'rental']);

            return response()->json([
                'success' => true,
                'message' => 'Phiếu khách yêu cầu bảo trì đã được cập nhật thành công.',
                'data' => [
                    'customer_maintenance_request' => [
                        'id' => $maintenanceRequest->id,
                        'request_code' => $maintenanceRequest->request_code,
                        'status' => $maintenanceRequest->status,
                        'priority' => $maintenanceRequest->priority,
                        'project_name' => $maintenanceRequest->project_name,
                        'customer_name' => $maintenanceRequest->customer_name,
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu yêu cầu với ID: ' . $id
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API update customer maintenance request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật phiếu yêu cầu bảo trì: ' . $e->getMessage()
            ], 500);
        }
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
        try {
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
                    if (Employee::find($userId)) {
                        \App\Models\UserLog::logActivity(
                            $userId,
                            'create',
                            'customer_maintenance_requests',
                            'Tạo phiếu khách yêu cầu bảo trì (sao chép): ' . $newRequest->request_code,
                            null,
                            $newRequest->toArray()
                        );
                    }
                }
                
                return redirect()->route('customer.dashboard')
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

            // Thêm thông tin item được chọn
            if ($request->has('item_id')) {
                $validatedData['selected_item'] = $request->item_id;
            }

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
                $validatedData['customer_address'] = $customer->address ?? '';

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
                    route('requests.customer-maintenance.show', $maintenanceRequest->id)
                );
            }

            // Ghi nhật ký tạo phiếu yêu cầu bảo trì mới
            if (Auth::guard('customer')->check()) {
                $userId = Auth::guard('customer')->user()->id;
                if (Employee::find($userId)) {
                    \App\Models\UserLog::logActivity(
                        $userId,
                        'create',
                        'customer_maintenance_requests',
                        'Tạo phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                        null,
                        $maintenanceRequest->toArray()
                    );
                }
            }

            return redirect()->route('customer.dashboard')
                ->with('success', 'Đã tạo phiếu yêu cầu bảo trì thành công.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CustomerMaintenanceRequest store error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
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
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'view',
                    'customer_maintenance_requests',
                    'Xem chi tiết phiếu khách yêu cầu bảo trì: ' . $request->request_code,
                    null,
                    ['id' => $request->id, 'code' => $request->request_code]
                );
            }
        } elseif (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'view',
                    'customer_maintenance_requests',
                    'Xem chi tiết phiếu khách yêu cầu bảo trì: ' . $request->request_code,
                    null,
                    ['id' => $request->id, 'code' => $request->request_code]
                );
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

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $maintenanceRequest->toArray();
        
        // Chuẩn hoá định dạng ngày tháng trước khi validate
        $request->merge([
            'request_date' => DateHelper::convertToDatabaseFormat($request->request_date),
        ]);

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_if:customer_id,null|max:255',
            'customer_phone' => 'nullable|max:20',
            'customer_email' => 'nullable|email|max:255',
            'project_name' => 'required|max:255',
            'request_date' => 'required|date_format:Y-m-d',
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
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'update',
                    'customer_maintenance_requests',
                    'Cập nhật phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
        } elseif (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'update',
                    'customer_maintenance_requests',
                    'Cập nhật phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
        }

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
        $requestCode = $maintenanceRequest->request_code;
        $requestData = $maintenanceRequest->toArray();
        
        // Cho phép xóa khi phiếu ở trạng thái chờ duyệt hoặc đã từ chối
        if (!in_array($maintenanceRequest->status, ['pending', 'rejected'])) {
            return redirect()->route('requests.customer-maintenance.show', $id)
                ->with('error', 'Chỉ có thể xóa phiếu yêu cầu ở trạng thái Chờ duyệt hoặc Đã từ chối.');
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
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'delete',
                    'customer_maintenance_requests',
                    'Xóa phiếu khách yêu cầu bảo trì: ' . $requestCode,
                    $requestData,
                    null
                );
            }
        } elseif (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'delete',
                    'customer_maintenance_requests',
                    'Xóa phiếu khách yêu cầu bảo trì: ' . $requestCode,
                    $requestData,
                    null
                );
            }
        }
        
        // Redirect về đúng trang tùy theo user type
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard')
                ->with('success', 'Xóa phiếu yêu cầu bảo trì thành công!');
        } else {
            // Nếu là nhân viên, quay về trang index
            return redirect()->route('requests.index')
                ->with('success', 'Xóa phiếu yêu cầu bảo trì thành công!');
        }
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
            return redirect()->route('requests.customer-maintenance.show', $id)
                ->with('error', 'Phiếu yêu cầu này không ở trạng thái chờ duyệt.');
        }
        
        $maintenanceRequest->status = 'approved';
        
        // Sửa: Chỉ lấy ID từ guard web (nhân viên), không lấy từ guard customer
        if (Auth::guard('web')->check()) {
            $maintenanceRequest->approved_by = Auth::guard('web')->id();
        } else {
            // Trường hợp không phải nhân viên (không nên xảy ra do middleware)
            return redirect()->route('requests.customer-maintenance.show', $id)
                ->with('error', 'Bạn không có quyền duyệt phiếu yêu cầu này.');
        }
        
        $maintenanceRequest->approved_at = now();
        $maintenanceRequest->save();

        // Ghi nhật ký duyệt phiếu yêu cầu bảo trì
        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->user()->id;
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'approve',
                    'customer_maintenance_requests',
                    'Duyệt phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
        }
        
        return redirect()->route('requests.customer-maintenance.show', $id)
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
            return redirect()->route('requests.customer-maintenance.show', $id)
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
            if (Employee::find($userId)) {
                \App\Models\UserLog::logActivity(
                    $userId,
                    'reject',
                    'customer_maintenance_requests',
                    'Từ chối phiếu khách yêu cầu bảo trì: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
        }
        
        return redirect()->route('requests.customer-maintenance.show', $id)
            ->with('success', 'Đã từ chối phiếu yêu cầu bảo trì thành công!');
    }

    /**
     * Hỗ trợ tạo mã phiếu yêu cầu bảo trì duy nhất
     */
    private function generateUniqueRequestCode()
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $attempt++;
            
            // Sử dụng timestamp để tránh race condition
            $timestamp = now()->format('ymdHis');
            $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
            $requestCode = 'CUST-MAINT-' . $timestamp . '-' . $random;
            
            // Kiểm tra xem mã này đã tồn tại chưa
            $exists = CustomerMaintenanceRequest::where('request_code', $requestCode)->exists();
            
            if (!$exists) {
                return $requestCode;
            }
            
            // Nếu đã thử quá nhiều lần, sử dụng fallback method
            if ($attempt >= $maxAttempts) {
                return $this->generateFallbackRequestCode();
            }
            
        } while (true);
    }
    
    /**
     * Fallback method để tạo mã khi timestamp method thất bại
     */
    private function generateFallbackRequestCode()
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
