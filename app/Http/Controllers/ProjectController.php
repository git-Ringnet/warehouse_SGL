<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\UserLog;
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

        // Lấy danh sách thiết bị dự phòng cho bảo hành/thay thế
        $backupItems = collect();
        $dispatches = \App\Models\Dispatch::where('dispatch_type', 'project')
            ->where('project_id', $project->id)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        foreach ($dispatches as $dispatch) {
            $items = $dispatch->items()->where('category', 'backup')->get();
            $backupItems = $backupItems->concat($items);
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

        return view('projects.show', compact('project', 'warehouses', 'backupItems'));
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
                // Sử dụng ProjectObserver để kiểm tra và gửi thông báo
                $observer = new \App\Observers\ProjectObserver();

                // Gọi phương thức protected thông qua Reflection API
                $reflection = new \ReflectionClass(get_class($observer));
                $method = $reflection->getMethod('checkWarrantyStatus');
                $method->setAccessible(true);
                $method->invokeArgs($observer, [$project]);
            }
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
                
                // Chỉ đếm những thiết bị chưa bị thu hồi
                if (!$isReturned) {
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
        $dispatches = \App\Models\Dispatch::where('project_id', $projectId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        $allItems = collect();

        foreach ($dispatches as $dispatch) {
            // Lấy danh sách products (thiết bị)
            $products = $dispatch->items()
                ->with(['product'])
                ->where('category', 'contract')
                ->where('item_type', 'product')
                ->get()
                ->map(function ($item) use ($dispatch) {
                    return [
                        'id' => $item->id,
                        'type' => 'product',
                        'name' => $item->product->name,
                        'serial_number' => $item->serial_number,
                        'description' => $item->product->description,
                        'project_name' => $dispatch->project_name,
                        'dispatch_code' => $dispatch->dispatch_code
                    ];
                });
            
            // Lấy danh sách goods (hàng hóa)
            $goods = $dispatch->items()
                ->with(['good'])
                ->where('category', 'contract')
                ->where('item_type', 'good')
                ->get()
                ->map(function ($item) use ($dispatch) {
                    return [
                        'id' => $item->id,
                        'type' => 'good',
                        'name' => $item->good->name,
                        'serial_number' => $item->serial_number,
                        'description' => $item->good->description,
                        'project_name' => $dispatch->project_name,
                        'dispatch_code' => $dispatch->dispatch_code
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
}
