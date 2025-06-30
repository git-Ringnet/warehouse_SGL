<?php

namespace App\Http\Controllers;

use App\Models\ProjectRequest;
use App\Models\ProjectRequestItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Material;
use App\Models\Good;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProductMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProjectRequestController extends Controller
{
    /**
     * Hiển thị danh sách phiếu đề xuất triển khai dự án
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $status = $request->input('status');
        
        // Query cho phiếu đề xuất triển khai dự án
        $projectQuery = ProjectRequest::with(['proposer', 'customer']);
        
        // Query cho phiếu bảo trì dự án
        $maintenanceQuery = \App\Models\MaintenanceRequest::with(['proposer', 'customer']);
        
        // Query cho phiếu khách yêu cầu bảo trì
        $customerMaintenanceQuery = \App\Models\CustomerMaintenanceRequest::with(['customer']);
        
        // Xử lý tìm kiếm cho phiếu đề xuất triển khai dự án
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'request_code':
                        $projectQuery->where('request_code', 'like', "%{$search}%");
                        $maintenanceQuery->where('request_code', 'like', "%{$search}%");
                        $customerMaintenanceQuery->where('request_code', 'like', "%{$search}%");
                        break;
                    case 'project_name':
                        $projectQuery->where('project_name', 'like', "%{$search}%");
                        $maintenanceQuery->where('project_name', 'like', "%{$search}%");
                        $customerMaintenanceQuery->where('project_name', 'like', "%{$search}%");
                        break;
                    case 'customer':
                        $projectQuery->where('customer_name', 'like', "%{$search}%")
                              ->orWhereHas('customer', function($q) use ($search) {
                                  $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('company_name', 'like', "%{$search}%");
                              });
                        $maintenanceQuery->where('customer_name', 'like', "%{$search}%")
                              ->orWhereHas('customer', function($q) use ($search) {
                                  $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('company_name', 'like', "%{$search}%");
                              });
                        $customerMaintenanceQuery->where('customer_name', 'like', "%{$search}%")
                              ->orWhereHas('customer', function($q) use ($search) {
                                  $q->where('company_name', 'like', "%{$search}%");
                              });
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $projectQuery->where(function ($q) use ($search) {
                    $q->where('request_code', 'like', "%{$search}%")
                      ->orWhere('project_name', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($subq) use ($search) {
                          $subq->where('name', 'like', "%{$search}%")
                               ->orWhere('company_name', 'like', "%{$search}%");
                      });
                });
                $maintenanceQuery->where(function ($q) use ($search) {
                    $q->where('request_code', 'like', "%{$search}%")
                      ->orWhere('project_name', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($subq) use ($search) {
                          $subq->where('name', 'like', "%{$search}%")
                               ->orWhere('company_name', 'like', "%{$search}%");
                      });
                });
                $customerMaintenanceQuery->where(function ($q) use ($search) {
                    $q->where('request_code', 'like', "%{$search}%")
                      ->orWhere('project_name', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($subq) use ($search) {
                          $subq->where('company_name', 'like', "%{$search}%");
                      });
                });
            }
        }
        
        // Lọc theo trạng thái
        if ($status) {
            $projectQuery->where('status', $status);
            $maintenanceQuery->where('status', $status);
            $customerMaintenanceQuery->where('status', $status);
        }
        
        // Lấy dữ liệu phiếu đề xuất triển khai dự án
        $projectRequests = $projectQuery->latest()->get();
        
        // Lấy dữ liệu phiếu bảo trì dự án
        $maintenanceRequests = $maintenanceQuery->latest()->get();
        
        // Lấy dữ liệu phiếu khách yêu cầu bảo trì
        $customerMaintenanceRequests = $customerMaintenanceQuery->latest()->get();
        
        // Kết hợp hai loại phiếu và thêm trường type để phân biệt
        $projectRequests = $projectRequests->map(function ($item) {
            $item->type = 'project';
            return $item;
        });
        
        $maintenanceRequests = $maintenanceRequests->map(function ($item) {
            $item->type = 'maintenance';
            return $item;
        });
        
        $customerMaintenanceRequests = $customerMaintenanceRequests->map(function ($item) {
            $item->type = 'customer_maintenance';
            return $item;
        });
        
        // Gộp tất cả loại phiếu và sắp xếp theo ngày tạo mới nhất
        $allRequests = $projectRequests->concat($maintenanceRequests)
                                      ->concat($customerMaintenanceRequests)
                                      ->sortByDesc('created_at');
        
        // Phân trang thủ công
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $currentItems = $allRequests->forPage($currentPage, $perPage);
        
        $requests = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $allRequests->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $requests->appends([
            'search' => $search,
            'filter' => $filter,
            'status' => $status
        ]);
        
        return view('requests.index', compact('requests', 'search', 'filter', 'status'));
    }

    /**
     * Hiển thị form tạo mới phiếu đề xuất triển khai dự án
     */
    public function create()
    {
        // Lấy danh sách nhân viên
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        
        // Lấy danh sách khách hàng
        $customers = Customer::orderBy('company_name')->get();
        
        // Lấy danh sách dự án
        $projects = Project::with('customer')->orderBy('project_name')->get();
        
        // Lấy danh sách thiết bị, vật tư, hàng hóa
        $equipments = Product::where('status', 'active')->orderBy('name')->get();
        $materials = Material::where('status', 'active')->orderBy('name')->get();
        $goods = Good::where('status', 'active')->orderBy('name')->get();
        
        // Lấy thông tin nhân viên hiện tại
        $currentEmployee = Auth::user();
        
        return view('requests.project.create', compact(
            'employees', 
            'customers', 
            'projects',
            'equipments', 
            'materials', 
            'goods', 
            'currentEmployee'
        ));
    }

    /**
     * Lưu phiếu đề xuất triển khai dự án mới vào database
     */
    public function store(Request $request)
    {
        // Bật hiển thị lỗi chi tiết
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Kiểm tra nếu là sao chép từ phiếu đã tồn tại
        if ($request->has('copy_from')) {
            $sourceRequest = ProjectRequest::with(['items'])->findOrFail($request->copy_from);
            
            try {
                DB::beginTransaction();
                
                // Tạo phiếu đề xuất mới từ phiếu nguồn
                $newRequest = $sourceRequest->replicate();
                $newRequest->request_code = ProjectRequest::generateRequestCode();
                $newRequest->request_date = now();
                $newRequest->status = 'pending';
                $newRequest->save();
                
                // Sao chép các items từ phiếu nguồn
                foreach ($sourceRequest->items as $item) {
                    $newItem = $item->replicate();
                    $newItem->project_request_id = $newRequest->id;
                    $newItem->save();
                }
                
                DB::commit();
                
                return redirect()->route('requests.project.show', $newRequest->id)
                    ->with('success', 'Phiếu đề xuất đã được sao chép thành công.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Log lỗi chi tiết
                Log::error('Lỗi khi sao chép phiếu đề xuất: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                
                return redirect()->back()
                    ->with('error', 'Có lỗi xảy ra khi sao chép phiếu: ' . $e->getMessage())
                    ->withInput();
            }
        }
        
        // Validation cơ bản cho các trường chung
        $baseRules = [
            'request_date' => 'required|date',
            'proposer_id' => 'required|exists:employees,id',
            'implementer_id' => 'nullable|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'project_address' => 'required|string|max:255',
            'approval_method' => 'required|in:production,warehouse',
            'item_type' => 'required|in:equipment,material,good',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];
        
        // Thêm rules dựa vào loại item được chọn
        $itemType = $request->input('item_type');
        $rules = $baseRules;
        
        switch ($itemType) {
            case 'equipment':
                $rules['equipment'] = 'required|array|min:1';
                $rules['equipment.*.id'] = 'required|exists:products,id';
                $rules['equipment.*.quantity'] = 'required|integer|min:1';
                break;
                
            case 'material':
                $rules['material'] = 'required|array|min:1';
                $rules['material.*.id'] = 'required|exists:materials,id';
                $rules['material.*.quantity'] = 'required|integer|min:1';
                break;
                
            case 'good':
                $rules['goods'] = 'required|array|min:1';
                $rules['goods.*.id'] = 'required|exists:goods,id';
                $rules['goods.*.quantity'] = 'required|integer|min:1';
                break;
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Lấy thông tin dự án từ ID
            $project = Project::with('customer')->findOrFail($request->project_id);
            
            // Tạo phiếu đề xuất mới
            $projectRequest = ProjectRequest::create([
                'request_code' => ProjectRequest::generateRequestCode(),
                'request_date' => $request->request_date,
                'proposer_id' => $request->proposer_id,
                'implementer_id' => $request->implementer_id,
                'project_name' => $request->project_name,
                'customer_id' => $project->customer->id,
                'project_address' => $request->project_address,
                'approval_method' => $request->approval_method,
                'customer_name' => $project->customer->name,
                'customer_phone' => $project->customer->phone,
                'customer_email' => $project->customer->email,
                'customer_address' => $project->customer->address,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);
            
            // Lưu danh sách thiết bị/vật tư/hàng hóa đề xuất dựa vào loại item được chọn
            $items = [];
            
            switch ($itemType) {
                case 'equipment':
                    $items = $request->input('equipment') ?? [];
                    break;
                case 'material':
                    $items = $request->input('material') ?? [];
                    break;
                case 'good':
                    $items = $request->input('goods') ?? [];
                    break;
            }
            
            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['quantity'])) {
                    continue;
                }
                
                // Lấy thông tin chi tiết của item dựa vào loại
                $itemModel = null;
                $itemData = [
                    'project_request_id' => $projectRequest->id,
                    'item_type' => $itemType,
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                ];
                
                switch ($itemType) {
                    case 'equipment':
                        $itemModel = Product::find($item['id']);
                        break;
                    case 'material':
                        $itemModel = Material::find($item['id']);
                        break;
                    case 'good':
                        $itemModel = Good::find($item['id']);
                        break;
                }
                
                if ($itemModel) {
                    $itemData['name'] = $itemModel->name;
                    $itemData['code'] = $itemModel->code;
                    $itemData['unit'] = $itemModel->unit ?? 'N/A';
                    $itemData['description'] = $itemModel->description;
                }
                
                ProjectRequestItem::create($itemData);
            }
            
            // Gửi thông báo cho người đề xuất và người thực hiện
            $proposer = Employee::find($request->proposer_id);
            if ($proposer) {
                Notification::createNotification(
                    'Phiếu đề xuất triển khai dự án mới',
                    'Bạn đã tạo phiếu đề xuất triển khai dự án ' . $projectRequest->project_name,
                    'info',
                    $proposer->id,
                    'project_request',
                    $projectRequest->id,
                    route('requests.project.show', $projectRequest->id)
                );
            }

            if ($request->implementer_id) {
                $implementer = Employee::find($request->implementer_id);
                if ($implementer) {
                    Notification::createNotification(
                        'Được phân công thực hiện dự án mới',
                        'Bạn được phân công thực hiện dự án ' . $projectRequest->project_name,
                        'info',
                        $implementer->id,
                        'project_request',
                        $projectRequest->id,
                        route('requests.project.show', $projectRequest->id)
                    );
                }
            }
            
            DB::commit();
            
            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', 'Phiếu đề xuất triển khai dự án đã được tạo thành công.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log lỗi chi tiết
            Log::error('Lỗi khi tạo phiếu đề xuất: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết phiếu đề xuất
     */
    public function show($id)
    {
        $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'customer', 'equipments.equipment', 'materials.materialItem'])->findOrFail($id);
        
        // Tìm phiếu lắp ráp liên quan nếu có
        $assembly = \App\Models\Assembly::where('notes', 'like', '%phiếu đề xuất dự án #' . $id . '%')
            ->with(['products.product'])
            ->first();
        
        return view('requests.project.show', compact('projectRequest', 'assembly'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu đề xuất
     */
    public function edit($id)
    {
        $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'customer', 'equipments', 'materials'])->findOrFail($id);
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();
        
        return view('requests.project.edit', compact('projectRequest', 'customers', 'employees'));
    }

    /**
     * Cập nhật phiếu đề xuất trong database
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'project_name' => 'required|string|max:255',
            'project_address' => 'required|string|max:255',
            'approval_method' => 'required|in:production,warehouse',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'request_date.required' => 'Ngày đề xuất không được để trống',
            'project_name.required' => 'Tên dự án không được để trống',
            'project_address.required' => 'Địa chỉ dự án không được để trống',
            'approval_method.required' => 'Phương thức xử lý không được để trống',
            'customer_name.required' => 'Tên khách hàng không được để trống',
            'customer_phone.required' => 'Số điện thoại khách hàng không được để trống',
            'customer_email.email' => 'Email không đúng định dạng',
            'customer_address.required' => 'Địa chỉ khách hàng không được để trống',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();
            
            $projectRequest = ProjectRequest::findOrFail($id);
            
            // Chỉ cho phép chỉnh sửa nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return back()->withInput()
                    ->withErrors(['error' => 'Không thể chỉnh sửa phiếu đề xuất đã được duyệt hoặc đang xử lý.']);
            }
            
            // Cập nhật thông tin khách hàng
            if ($request->filled('partner') && $projectRequest->customer_id) {
                $customer = Customer::find($projectRequest->customer_id);
                if ($customer) {
                    $customer->update([
                        'name' => $request->customer_name,
                        'phone' => $request->customer_phone,
                        'email' => $request->customer_email,
                        'address' => $request->customer_address,
                    ]);
                }
            }
            
            // Cập nhật phiếu đề xuất (chỉ các thông tin cơ bản)
            $projectRequest->update([
                'request_date' => $request->request_date,
                'project_name' => $request->project_name,
                'project_address' => $request->project_address,
                'approval_method' => $request->approval_method,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'notes' => $request->notes,
            ]);
            
            DB::commit();

            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', 'Phiếu đề xuất triển khai dự án đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xóa phiếu đề xuất khỏi database
     */
    public function destroy($id)
    {
        try {
            $projectRequest = ProjectRequest::findOrFail($id);
            
            // Chỉ cho phép xóa nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Không thể xóa phiếu đề xuất đã được duyệt hoặc đang xử lý.');
            }
            
            $projectRequest->delete();
            
            return redirect()->route('requests.index')
                ->with('success', 'Phiếu đề xuất triển khai dự án đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('requests.index')
                ->with('error', 'Có lỗi xảy ra khi xóa phiếu đề xuất: ' . $e->getMessage());
        }
    }

    /**
     * Duyệt phiếu đề xuất
     */
    public function approve(Request $request, $id)
    {
        try {
            $projectRequest = ProjectRequest::findOrFail($id);
            
            // Chỉ cho phép duyệt nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Phiếu đề xuất này đã được duyệt hoặc đang xử lý.');
            }
            
            // Người thực hiện mặc định là người đề xuất
            $projectRequest->update([
                'implementer_id' => $request->implementer_id, // Giá trị này được gửi từ form dưới dạng hidden field
                'status' => 'approved',
            ]);
            
            // Gửi thông báo duyệt phiếu cho người đề xuất
            if ($projectRequest->proposer_id) {
                Notification::createNotification(
                    'Phiếu đề xuất đã được duyệt',
                    'Phiếu đề xuất triển khai dự án ' . $projectRequest->project_name . ' đã được duyệt',
                    'success',
                    $projectRequest->proposer_id,
                    'project_request',
                    $projectRequest->id,
                    route('requests.project.show', $projectRequest->id)
                );
            }
            
            // Xử lý dựa trên phương thức xử lý được chọn
            if ($projectRequest->approval_method === 'production') {
                // Tự động tạo phiếu lắp ráp
                $assembly = $this->createAssemblyFromRequest($projectRequest);
                
                // Gửi thông báo về việc cần tạo phiếu lắp ráp
                if ($projectRequest->implementer_id) {
                    Notification::createNotification(
                        'Phiếu lắp ráp đã được tạo',
                        'Phiếu lắp ráp đã được tạo tự động cho phiếu đề xuất dự án ' . $projectRequest->project_name,
                        'info',
                        $projectRequest->implementer_id,
                        'project_request',
                        $projectRequest->id,
                        route('assemblies.index')
                    );
                }
            } elseif ($projectRequest->approval_method === 'warehouse') {
                // Gửi thông báo về việc cần tạo phiếu xuất kho
                if ($projectRequest->implementer_id) {
                    Notification::createNotification(
                        'Yêu cầu tạo phiếu xuất kho',
                        'Bạn cần tạo phiếu xuất kho cho phiếu đề xuất dự án ' . $projectRequest->project_name,
                        'info',
                        $projectRequest->implementer_id,
                        'project_request',
                        $projectRequest->id,
                        route('inventory.index')
                    );
                }
            }
            
            $successMessage = 'Phiếu đề xuất đã được duyệt thành công.';
            
            // Thêm thông báo về phiếu lắp ráp nếu đã tạo thành công
            if ($projectRequest->approval_method === 'production' && isset($assembly) && $assembly) {
                $successMessage .= ' Phiếu lắp ráp ' . $assembly->code . ' đã được tạo tự động.';
            }
            
            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi duyệt phiếu đề xuất: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối phiếu đề xuất
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reject_reason' => 'required|string',
        ], [
            'reject_reason.required' => 'Lý do từ chối không được để trống',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $projectRequest = ProjectRequest::findOrFail($id);
            
            // Chỉ cho phép từ chối nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Phiếu đề xuất này đã được duyệt hoặc đang xử lý.');
            }
            
            // Cập nhật ghi chú với lý do từ chối
            $notes = $projectRequest->notes ?? '';
            $notes .= "\n[" . date('Y-m-d H:i:s') . "] Từ chối: " . $request->reject_reason;
            
            $projectRequest->update([
                'notes' => trim($notes),
                'status' => 'rejected',
            ]);
            
            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', 'Phiếu đề xuất đã bị từ chối.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi từ chối phiếu đề xuất: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật trạng thái tiến độ
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_progress,completed,canceled',
            'status_note' => 'nullable|string',
        ], [
            'status.required' => 'Trạng thái không được để trống',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $projectRequest = ProjectRequest::findOrFail($id);
            
            // Chỉ cho phép cập nhật nếu đã được duyệt
            if ($projectRequest->status === 'pending' || $projectRequest->status === 'rejected') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Phiếu đề xuất chưa được duyệt hoặc đã bị từ chối.');
            }
            
            // Cập nhật ghi chú với thông tin trạng thái
            $notes = $projectRequest->notes ?? '';
            $notes .= "\n[" . date('Y-m-d H:i:s') . "] Cập nhật trạng thái: " . $this->getStatusText($request->status);
            
            if ($request->filled('status_note')) {
                $notes .= " - " . $request->status_note;
            }
            
            $projectRequest->update([
                'notes' => trim($notes),
                'status' => $request->status,
            ]);
            
            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', 'Trạng thái phiếu đề xuất đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage());
        }
    }
    
    /**
     * Lấy text hiển thị cho trạng thái
     */
    private function getStatusText($status)
    {
        switch ($status) {
            case 'pending':
                return 'Chờ duyệt';
            case 'approved':
                return 'Đã duyệt';
            case 'rejected':
                return 'Từ chối';
            case 'in_progress':
                return 'Đang thực hiện';
            case 'completed':
                return 'Hoàn thành';
            case 'canceled':
                return 'Đã hủy';
            default:
                return 'Không xác định';
        }
    }
    
    /**
     * Hiển thị trang xem trước phiếu đề xuất
     */
    public function preview($id)
    {
        $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'customer', 'equipments', 'materials'])->findOrFail($id);
        return view('requests.project.preview', compact('projectRequest'));
    }

    /**
     * Tạo phiếu lắp ráp tự động từ phiếu đề xuất dự án
     */
    private function createAssemblyFromRequest($projectRequest)
    {
        try {
            // Tải đầy đủ dữ liệu phiếu đề xuất nếu chưa có
            if (!$projectRequest->relationLoaded('equipments')) {
                $projectRequest->load(['equipments.equipment', 'materials.materialItem']);
            }
            
            // Kiểm tra xem có thiết bị/sản phẩm nào không
            if ($projectRequest->item_type === 'equipment' && $projectRequest->equipments->count() === 0) {
                throw new \Exception('Phiếu đề xuất không có thiết bị nào để lắp ráp');
            }
            
            // Debug: Log thông tin phiếu đề xuất
            \Illuminate\Support\Facades\Log::info('Thông tin phiếu đề xuất trước khi tạo phiếu lắp ráp', [
                'project_request_id' => $projectRequest->id,
                'item_type' => $projectRequest->item_type,
                'equipments_count' => $projectRequest->equipments->count(),
                'equipments' => $projectRequest->equipments->toArray()
            ]);
            
            // Tạo mã phiếu lắp ráp
            $prefix = 'ASM';
            $date = now()->format('ymd');
            
            // Tìm mã phiếu lắp ráp mới nhất trong ngày
            $latestAssembly = \App\Models\Assembly::where('code', 'like', $prefix . $date . '%')
                ->orderBy('code', 'desc')
                ->first();
                
            if ($latestAssembly) {
                // Trích xuất số thứ tự từ mã
                $code = $latestAssembly->code;
                
                if (preg_match('/^' . preg_quote($prefix . $date) . '(\d{3})$/', $code, $matches)) {
                    $sequence = intval($matches[1]) + 1;
                } else {
                    $sequence = intval(substr($code, -3)) + 1;
                }
            } else {
                $sequence = 1;
            }
            
            $assemblyCode = $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
            // Lấy kho mặc định
            $defaultWarehouse = \App\Models\Warehouse::where('status', 'active')
                ->where('is_hidden', false)
                ->orderBy('id')
                ->first();
                
            if (!$defaultWarehouse) {
                throw new \Exception('Không tìm thấy kho mặc định');
            }
            
            // Tạo phiếu lắp ráp
            $assembly = \App\Models\Assembly::create([
                'code' => $assemblyCode,
                'date' => now()->format('Y-m-d'),
                'warehouse_id' => $defaultWarehouse->id,
                'target_warehouse_id' => $defaultWarehouse->id,
                'assigned_employee_id' => $projectRequest->implementer_id,
                'tester_id' => $projectRequest->implementer_id,
                'purpose' => 'project',
                'project_id' => null,
                'status' => 'pending',
                'notes' => 'Tự động tạo từ phiếu đề xuất dự án #' . $projectRequest->id . ' - ' . $projectRequest->project_name,
            ]);
            
            // Thêm các sản phẩm từ phiếu đề xuất vào phiếu lắp ráp
            $productsAdded = false;
            
            if ($projectRequest->item_type === 'equipment' && $projectRequest->equipments->count() > 0) {
                foreach ($projectRequest->equipments as $equipment) {
                    // Lấy thông tin sản phẩm
                    $product = null;
                    
                    // Kiểm tra nếu có quan hệ equipment được tải
                    if ($equipment->equipment) {
                        $product = $equipment->equipment;
                    } else {
                        // Nếu không, tìm sản phẩm theo item_id
                        $product = \App\Models\Product::find($equipment->item_id);
                    }
                    
                    if ($product) {
                        // Thêm sản phẩm vào phiếu lắp ráp
                        \App\Models\AssemblyProduct::create([
                            'assembly_id' => $assembly->id,
                            'product_id' => $product->id,
                            'quantity' => $equipment->quantity,
                            'serials' => null,
                        ]);
                        
                        $productsAdded = true;
                        
                        // Lấy danh sách vật tư của sản phẩm
                        $productMaterials = \App\Models\ProductMaterial::where('product_id', $product->id)->get();
                        
                        // Thêm các vật tư vào phiếu lắp ráp
                        foreach ($productMaterials as $material) {
                            \App\Models\AssemblyMaterial::create([
                                'assembly_id' => $assembly->id,
                                'material_id' => $material->material_id,
                                'quantity' => $material->quantity * $equipment->quantity, // Số lượng vật tư = số lượng cần cho 1 sản phẩm * số lượng sản phẩm
                                'serial' => null,
                                'product_id' => $product->id // Liên kết vật tư với sản phẩm
                            ]);
                        }
                        
                        // Log thông tin
                        \Illuminate\Support\Facades\Log::info('Đã thêm sản phẩm và vật tư vào phiếu lắp ráp', [
                            'assembly_code' => $assembly->code,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $equipment->quantity,
                            'materials_count' => $productMaterials->count()
                        ]);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Không tìm thấy sản phẩm', [
                            'item_id' => $equipment->item_id,
                            'equipment' => $equipment->toArray()
                        ]);
                    }
                }
            }
            
            // Nếu không có sản phẩm nào được thêm, thêm sản phẩm mặc định
            if (!$productsAdded) {
                // Tìm sản phẩm đầu tiên trong hệ thống
                $defaultProduct = \App\Models\Product::where('status', 'active')
                    ->where('is_hidden', false)
                    ->first();
                
                if ($defaultProduct) {
                    \App\Models\AssemblyProduct::create([
                        'assembly_id' => $assembly->id,
                        'product_id' => $defaultProduct->id,
                        'quantity' => 1,
                        'serials' => null,
                    ]);
                    
                    // Lấy và thêm vật tư của sản phẩm mặc định
                    $defaultProductMaterials = \App\Models\ProductMaterial::where('product_id', $defaultProduct->id)->get();
                    foreach ($defaultProductMaterials as $material) {
                        \App\Models\AssemblyMaterial::create([
                            'assembly_id' => $assembly->id,
                            'material_id' => $material->material_id,
                            'quantity' => $material->quantity,
                            'serial' => null,
                            'product_id' => $defaultProduct->id
                        ]);
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Đã thêm sản phẩm mặc định và vật tư vào phiếu lắp ráp', [
                        'assembly_code' => $assembly->code,
                        'product_id' => $defaultProduct->id,
                        'product_name' => $defaultProduct->name,
                        'materials_count' => $defaultProductMaterials->count()
                    ]);
                }
            }
            
            return $assembly;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi tạo phiếu lắp ráp tự động: ' . $e->getMessage(), [
                'project_request_id' => $projectRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
} 