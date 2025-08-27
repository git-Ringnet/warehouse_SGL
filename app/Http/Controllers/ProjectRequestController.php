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
use App\Models\Rental;
use App\Models\ProductMaterial;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Warehouse;
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
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');
        
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
        
        // Lọc theo ngày tạo phiếu
        if ($dateFrom) {
            $dateFromFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $dateFrom)->format('Y-m-d');
            $projectQuery->whereDate('created_at', '>=', $dateFromFormatted);
            $maintenanceQuery->whereDate('created_at', '>=', $dateFromFormatted);
            $customerMaintenanceQuery->whereDate('created_at', '>=', $dateFromFormatted);
        }
        
        if ($dateTo) {
            $dateToFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $dateTo)->format('Y-m-d');
            $projectQuery->whereDate('created_at', '<=', $dateToFormatted);
            $maintenanceQuery->whereDate('created_at', '<=', $dateToFormatted);
            $customerMaintenanceQuery->whereDate('created_at', '<=', $dateToFormatted);
        }
        
        // Lọc theo loại phiếu nếu có
        if ($type) {
            if ($type === 'project') {
                $maintenanceQuery->whereRaw('1=0');
                $customerMaintenanceQuery->whereRaw('1=0');
            } elseif ($type === 'maintenance') {
                $projectQuery->whereRaw('1=0');
                $customerMaintenanceQuery->whereRaw('1=0');
            } elseif ($type === 'customer_maintenance') {
                $projectQuery->whereRaw('1=0');
                $maintenanceQuery->whereRaw('1=0');
            }
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
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'type' => $type
        ]);
        
        return view('requests.index', compact('requests', 'search', 'filter', 'status', 'dateFrom', 'dateTo', 'type'));
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
        
        // Lấy danh sách dự án còn hiệu lực bảo hành
        $projects = Project::with('customer')
            ->whereHas('customer') // Đảm bảo có customer
            ->get()
            ->filter(function($project) {
                return $project->has_valid_warranty; // Chỉ lấy dự án còn bảo hành
            })
            ->sortBy('project_name');
        
        // Lấy danh sách phiếu cho thuê còn hiệu lực bảo hành
        $rentals = Rental::with('customer')
            ->whereHas('customer') // Đảm bảo có customer
            ->get()
            ->filter(function($rental) {
                return $rental->has_valid_warranty; // Chỉ lấy rental còn bảo hành
            })
            ->sortBy('rental_name');
        
        // Lấy danh sách thiết bị, vật tư, hàng hóa (chỉ lấy active và không bị ẩn)
        $equipments = Product::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get();
        $materials = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get();
        $goods = Good::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get();
        
        // Lấy danh sách vật tư từ kho (cho xuất kho)
        $warehouseMaterials = \App\Models\WarehouseMaterial::with(['material', 'warehouse'])
            ->whereHas('warehouse', function($q) {
                $q->where('status', 'active')->where('is_hidden', false);
            })
            ->where('quantity', '>', 0) // Chỉ lấy vật tư có tồn kho > 0
            ->get()
            ->groupBy('material_id')
            ->map(function($group) {
                // Lấy thông tin vật tư và kho có nhiều tồn kho nhất
                $bestWarehouse = $group->sortByDesc('quantity')->first();
                return [
                    'material' => $bestWarehouse->material,
                    'warehouse' => $bestWarehouse->warehouse,
                    'quantity' => $bestWarehouse->quantity
                ];
            })
            ->values();
        
        // Lấy thông tin nhân viên hiện tại
        $currentEmployee = Auth::user();
        
        return view('requests.project.create', compact(
            'employees', 
            'customers', 
            'projects',
            'rentals',
            'equipments', 
            'materials', 
            'goods', 
            'warehouseMaterials',
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
                
                // Ghi nhật ký tạo phiếu đề xuất từ sao chép
                if (Auth::check()) {
                    \App\Models\UserLog::logActivity(
                        Auth::id(),
                        'create',
                        'project_requests',
                        'Tạo phiếu đề xuất triển khai dự án (sao chép): ' . $newRequest->request_code,
                        null,
                        $newRequest->toArray()
                    );
                }
                
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
            'proposer_id' => 'nullable|exists:employees,id', // Bỏ required vì có thể ẩn khi chọn warehouse
            'implementer_id' => 'nullable|exists:employees,id',
            'project_id' => 'required',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'nullable', // Bỏ required vì sẽ tự động điền
            'approval_method' => 'required|in:production,warehouse',
            'item_type' => 'required|in:equipment,material,good',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
        
        // Thêm rules dựa vào loại item được chọn
        $itemType = $request->input('item_type');
        $rules = $baseRules;
        
        // Validate thêm cho lắp ráp
        if ($request->approval_method === 'production') {
            $rules['item_type'] = 'required|in:equipment';
        } else {
            $rules['item_type'] = 'required|in:equipment,good';
        }
        
        switch ($itemType) {
            case 'equipment':
                $rules['equipment'] = 'required|array|min:1';
                $rules['equipment.*.id'] = 'required|exists:products,id';
                $rules['equipment.*.quantity'] = 'required|integer|min:1';
                break;
                
            case 'good':
                $rules['good'] = 'required|array|min:1';
                $rules['good.*.id'] = 'required|exists:goods,id';
                $rules['good.*.quantity'] = 'required|integer|min:1';
                break;
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Kiểm tra thêm xem các item có active và không bị ẩn không
        $items = [];
        switch ($itemType) {
            case 'equipment':
                $items = $request->input('equipment') ?? [];
                break;
            case 'good':
                $items = $request->input('good') ?? [];
                break;
        }
        
        foreach ($items as $item) {
            if (!isset($item['id'])) continue;
            
            $itemExists = false;
            switch ($itemType) {
                case 'equipment':
                    $itemExists = Product::where('status', 'active')
                        ->where('is_hidden', false)
                        ->where('id', $item['id'])
                        ->exists();
                    break;
                case 'good':
                    $itemExists = Good::where('status', 'active')
                        ->where('is_hidden', false)
                        ->where('id', $item['id'])
                        ->exists();
                    break;
            }
            
            if (!$itemExists) {
                return redirect()->back()
                    ->with('error', 'Item đã chọn không tồn tại hoặc đã bị ẩn.')
                    ->withInput();
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Xử lý project_id để phân biệt project và rental
            $projectId = $request->project_id;
            $projectType = null;
            $actualProjectId = null;
            
            if (strpos($projectId, 'project_') === 0) {
                $projectType = 'project';
                $actualProjectId = substr($projectId, 8); // Bỏ 'project_' prefix
            } elseif (strpos($projectId, 'rental_') === 0) {
                $projectType = 'rental';
                $actualProjectId = substr($projectId, 7); // Bỏ 'rental_' prefix
            }
            
            // Lấy thông tin dự án/phiếu cho thuê từ ID
            if ($projectType === 'project') {
                $project = Project::with('customer')->findOrFail($actualProjectId);
                
                // Kiểm tra xem dự án còn hiệu lực bảo hành không
                if (!$project->has_valid_warranty) {
                    return redirect()->back()
                        ->with('error', 'Dự án này đã hết hạn bảo hành và không thể tạo phiếu đề xuất.')
                        ->withInput();
                }
                
                $customer = $project->customer;
            } else {
                $rental = Rental::with('customer')->findOrFail($actualProjectId);
                
                // Kiểm tra xem rental còn hiệu lực bảo hành không
                if (!$rental->has_valid_warranty) {
                    return redirect()->back()
                        ->with('error', 'Phiếu cho thuê này đã hết hạn bảo hành và không thể tạo phiếu đề xuất.')
                        ->withInput();
                }
                
                $customer = $rental->customer;
            }
            
            // Xác định proposer_id dựa trên phương thức xử lý
            $proposerId = null;
            if ($request->has('proposer_id') && $request->proposer_id) {
                $proposerId = $request->proposer_id;
            }
            
            if ($request->approval_method === 'warehouse' && !$proposerId) {
                // Nếu là warehouse và không có proposer_id, sử dụng tài khoản hiện tại
                $proposerId = Auth::id();
            } elseif (!$proposerId) {
                // Nếu không có proposer_id, sử dụng tài khoản hiện tại làm mặc định
                $proposerId = Auth::id();
            }
            
            // Tạo phiếu đề xuất mới
            $projectRequest = ProjectRequest::create([
                'request_code' => ProjectRequest::generateRequestCode(),
                'request_date' => $request->request_date,
                'proposer_id' => $proposerId,
                'implementer_id' => $request->implementer_id,
                'assembly_leader_id' => $request->approval_method === 'production' ? $proposerId : null,
                'tester_id' => $request->approval_method === 'production' ? $request->implementer_id : null,
                'project_name' => $request->project_name,
                'customer_id' => $customer->id,
                'project_id' => $projectType === 'project' ? $actualProjectId : null,
                'rental_id' => $projectType === 'rental' ? $actualProjectId : null,
                'project_address' => $request->project_address ?? '', // Cho phép trống
                'approval_method' => $request->approval_method,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'customer_address' => $customer->address ?? '',
                'notes' => $request->notes,
                'status' => 'pending',
            ]);
            
            // Lưu danh sách thiết bị/vật tư/hàng hóa đề xuất dựa vào loại item được chọn
            $items = [];
            
            switch ($itemType) {
                case 'equipment':
                    $items = $request->input('equipment') ?? [];
                    break;
                case 'good':
                    $items = $request->input('good') ?? [];
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
            $proposer = Employee::find($proposerId);
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
            
            // Ghi nhật ký tạo phiếu đề xuất mới
            if (Auth::check()) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'project_requests',
                    'Tạo phiếu đề xuất triển khai dự án: ' . $projectRequest->request_code,
                    null,
                    $projectRequest->toArray()
                );
            }
            
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
        $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'assembly_leader', 'tester', 'customer', 'equipments.equipment', 'materials.materialItem'])->findOrFail($id);
        
        // Tìm phiếu lắp ráp liên quan nếu có
        $assembly = \App\Models\Assembly::where('notes', 'like', '%phiếu đề xuất dự án #' . $id . '%')
            ->with(['products.product'])
            ->first();
        
        // Ghi nhật ký xem chi tiết phiếu đề xuất
        if (Auth::check()) {
            \App\Models\UserLog::logActivity(
                Auth::id(),
                'view',
                'project_requests',
                'Xem chi tiết phiếu đề xuất triển khai dự án: ' . $projectRequest->request_code,
                null,
                ['id' => $projectRequest->id, 'code' => $projectRequest->request_code]
            );
        }
        
        return view('requests.project.show', compact('projectRequest', 'assembly'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu đề xuất
     */
    public function edit($id)
    {
        $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'customer', 'items'])->findOrFail($id);
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();

        // Lấy danh sách dự án còn hiệu lực bảo hành (giống create)
        $projects = Project::with('customer')
            ->whereHas('customer')
            ->get()
            ->filter(function($project) {
                return $project->has_valid_warranty;
            })
            ->sortBy('project_name');

        // Lấy danh sách phiếu cho thuê còn hiệu lực bảo hành (giống create)
        $rentals = Rental::with('customer')
            ->whereHas('customer')
            ->get()
            ->filter(function($rental) {
                return $rental->has_valid_warranty;
            })
            ->sortBy('rental_name');
        
        // Lấy danh sách thiết bị, vật tư, hàng hóa (chỉ lấy active và không bị ẩn)
        $equipments = Product::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get();
        $materials = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get();
        $goods = Good::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get();
        
        return view('requests.project.edit', compact('projectRequest', 'customers', 'employees', 'projects', 'rentals', 'equipments', 'materials', 'goods'));
    }

    /**
     * Cập nhật phiếu đề xuất trong database
     */
    public function update(Request $request, $id)
    {
        // Validation cơ bản cho các trường chung
        $baseRules = [
            'request_date' => 'required|date',
            'project_id' => 'required',
            'project_name' => 'required|string|max:255',
            'approval_method' => 'required|in:production,warehouse',
            'notes' => 'nullable|string',
            'item_type' => 'required|in:equipment,good',
        ];
        
        // Thêm rules dựa vào loại item được chọn
        $itemType = $request->input('item_type');
        $rules = $baseRules;
        
        // Validate thêm cho lắp ráp
        if ($request->approval_method === 'production') {
            $rules['item_type'] = 'required|in:equipment';
        } else {
            $rules['item_type'] = 'required|in:equipment,good';
        }
        
        switch ($itemType) {
            case 'equipment':
                $rules['equipment'] = 'required|array|min:1';
                $rules['equipment.*.id'] = 'required|exists:products,id';
                $rules['equipment.*.quantity'] = 'required|integer|min:1';
                break;
                
            case 'good':
                $rules['good'] = 'required|array|min:1';
                $rules['good.*.id'] = 'required|exists:goods,id';
                $rules['good.*.quantity'] = 'required|integer|min:1';
                break;
        }
        
        $validator = Validator::make($request->all(), $rules, [
            'request_date.required' => 'Ngày đề xuất không được để trống',
            'project_name.required' => 'Tên dự án không được để trống',
            'approval_method.required' => 'Phương thức xử lý không được để trống',
            'customer_name.required' => 'Tên khách hàng không được để trống',
            'customer_phone.required' => 'Số điện thoại khách hàng không được để trống',
            'customer_email.email' => 'Email không đúng định dạng',
            // Địa chỉ khách hàng không bắt buộc
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
            
            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $projectRequest->toArray();
            
            // Chỉ cho phép chỉnh sửa nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return back()->withInput()
                    ->withErrors(['error' => 'Không thể chỉnh sửa phiếu đề xuất đã được duyệt hoặc đang xử lý.']);
            }
            
            // Xử lý project_id để phân biệt project và rental
            $projectIdInput = $request->project_id;
            $projectType = null;
            $actualProjectId = null;

            if (strpos($projectIdInput, 'project_') === 0) {
                $projectType = 'project';
                $actualProjectId = substr($projectIdInput, 8);
            } elseif (strpos($projectIdInput, 'rental_') === 0) {
                $projectType = 'rental';
                $actualProjectId = substr($projectIdInput, 7);
            }

            // Lấy thông tin dự án/phiếu cho thuê từ ID và map khách hàng
            if ($projectType === 'project') {
                $project = Project::with('customer')->findOrFail($actualProjectId);
                $customer = $project->customer;
            } else {
                $rental = Rental::with('customer')->findOrFail($actualProjectId);
                $customer = $rental->customer;
            }

            // Cập nhật phiếu đề xuất (bao gồm mapping đối tác theo lựa chọn)
            $projectRequest->update([
                'request_date' => $request->request_date,
                'project_name' => $request->project_name,
                'approval_method' => $request->approval_method,
                'customer_id' => $customer ? $customer->id : null,
                'project_id' => $projectType === 'project' ? $actualProjectId : null,
                'rental_id' => $projectType === 'rental' ? $actualProjectId : null,
                'customer_name' => $customer ? $customer->name : null,
                'customer_phone' => $customer ? $customer->phone : null,
                'customer_email' => $customer ? $customer->email : null,
                'customer_address' => $customer ? $customer->address : null,
                'notes' => $request->notes,
            ]);
            
            // Xóa tất cả items cũ
            $projectRequest->items()->delete();
            
            // Lưu danh sách thiết bị/vật tư/hàng hóa đề xuất dựa vào loại item được chọn
            $items = [];
            
            switch ($itemType) {
                case 'equipment':
                    $items = $request->input('equipment') ?? [];
                    break;
                case 'good':
                    $items = $request->input('good') ?? [];
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
            
            DB::commit();

            // Ghi nhật ký cập nhật phiếu đề xuất
            if (Auth::check()) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'project_requests',
                    'Cập nhật phiếu đề xuất triển khai dự án: ' . $projectRequest->request_code,
                    $oldData,
                    $projectRequest->toArray()
                );
            }

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
            $requestCode = $projectRequest->request_code;
            $requestData = $projectRequest->toArray();
            
            // Cho phép xóa nếu trạng thái là pending hoặc rejected
            if (!in_array($projectRequest->status, ['pending', 'rejected'])) {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Chỉ có thể xóa phiếu đề xuất ở trạng thái Chờ duyệt hoặc Đã từ chối.');
            }
            
            $projectRequest->delete();
            
            // Ghi nhật ký xóa phiếu đề xuất
            if (Auth::check()) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'project_requests',
                    'Xóa phiếu đề xuất triển khai dự án: ' . $requestCode,
                    $requestData,
                    null
                );
            }
            
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
        // Nếu là GET request, redirect về trang show với thông báo lỗi
        if ($request->isMethod('get')) {
            return redirect()->route('requests.project.show', $id)
                ->with('error', 'Vui lòng sử dụng nút "Duyệt phiếu" trên trang chi tiết thay vì truy cập trực tiếp URL.');
        }
        
        // Kiểm tra tồn kho trước khi duyệt (nếu là warehouse)
        $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'customer', 'items'])->findOrFail($id);
        
        if ($projectRequest->approval_method === 'warehouse') {
            // Debug log
            Log::info('Bắt đầu xử lý duyệt phiếu với phương thức warehouse', [
                'project_request_id' => $projectRequest->id,
                'items_count' => $projectRequest->items->count()
            ]);
            
            // Kiểm tra tồn kho trước khi tạo phiếu xuất kho
            $stockCheckResult = $this->checkStockForDispatch($projectRequest);
            
            // Debug log kết quả kiểm tra
            Log::info('Kết quả kiểm tra tồn kho', [
                'has_insufficient_stock' => $stockCheckResult['has_insufficient_stock'],
                'insufficient_items' => $stockCheckResult['insufficient_items']
            ]);
            
            if ($stockCheckResult['has_insufficient_stock']) {
                // Nếu không đủ tồn kho, KHÔNG cho phép duyệt
                Log::warning('Không đủ tồn kho để duyệt phiếu', [
                    'project_request_id' => $projectRequest->id,
                    'insufficient_items' => $stockCheckResult['insufficient_items']
                ]);
                
                // Debug: Kiểm tra xem có vào đây không
                $errorMessage = 'Không thể duyệt phiếu đề xuất: ' . implode(', ', $stockCheckResult['insufficient_items']);
                Log::info('Sẽ redirect với error message: ' . $errorMessage);
                
                // Sử dụng session()->flash() thay vì ->with()
                session()->flash('error', $errorMessage);
                
                return redirect()->route('requests.project.show', $projectRequest->id);
            }
        }
        
        try {
            DB::beginTransaction();
            
            // $projectRequest đã được load ở trên rồi
            $oldData = $projectRequest->toArray();
            
            // Chỉ cho phép duyệt nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Phiếu đề xuất này đã được duyệt hoặc đang xử lý.');
            }
            
            // Người thực hiện mặc định là người đề xuất
            $implementerId = $request->implementer_id ?? $projectRequest->proposer_id;
            $projectRequest->update([
                'implementer_id' => $implementerId,
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
            
            $successMessage = 'Phiếu đề xuất đã được duyệt thành công.';
            
            // Xử lý dựa trên phương thức xử lý được chọn
            if ($projectRequest->approval_method === 'production') {
                // Tạo phiếu lắp ráp tự động
                $assembly = $this->createAssemblyFromRequest($projectRequest);
                if ($assembly) {
                    $successMessage .= ' Phiếu lắp ráp ' . $assembly->code . ' đã được tạo tự động.';
                }
            } else if ($projectRequest->approval_method === 'warehouse') {
                // Tạo phiếu xuất kho tự động
                $dispatch = $this->createDispatchFromRequest($projectRequest);
                if ($dispatch) {
                    $successMessage .= ' Phiếu xuất kho ' . $dispatch->dispatch_code . ' đã được tạo tự động.';
                    
                    // Cập nhật phiếu dự án/cho thuê với các thiết bị đã được xuất
                    $this->updateProjectOrRentalWithItems($projectRequest, $dispatch);
                }
            }
            
            DB::commit();
            
            // Ghi nhật ký duyệt phiếu đề xuất
            if (Auth::check()) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'project_requests',
                    'Duyệt phiếu đề xuất triển khai dự án: ' . $projectRequest->request_code,
                    $oldData,
                    $projectRequest->toArray()
                );
            }
            
            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi duyệt phiếu đề xuất: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối phiếu đề xuất
     */
    public function reject(Request $request, $id)
    {
        // Nếu là GET request, redirect về trang show với thông báo lỗi
        if ($request->isMethod('get')) {
            return redirect()->route('requests.project.show', $id)
                ->with('error', 'Vui lòng sử dụng nút "Từ chối phiếu" trên trang chi tiết thay vì truy cập trực tiếp URL.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string',
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối',
        ]);
        
        try {
            DB::beginTransaction();
            
            $projectRequest = ProjectRequest::with(['proposer'])->findOrFail($id);
            $oldData = $projectRequest->toArray();
            
            // Chỉ cho phép từ chối nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Phiếu đề xuất này đã được duyệt hoặc đang xử lý.');
            }
            
            // Cập nhật ghi chú với lý do từ chối
            $notes = $projectRequest->notes ?? '';
            $notes .= "\n[" . date('Y-m-d H:i:s') . "] Từ chối: " . $request->rejection_reason;
            
            $projectRequest->update([
                'notes' => trim($notes),
                'status' => 'rejected',
            ]);
            
            DB::commit();
            
            // Ghi nhật ký từ chối phiếu đề xuất
            if (Auth::check()) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'reject',
                    'project_requests',
                    'Từ chối phiếu đề xuất triển khai dự án: ' . $projectRequest->request_code,
                    $oldData,
                    $projectRequest->toArray()
                );
            }
            
            return redirect()->route('requests.project.show', $id)
                ->with('success', 'Phiếu đề xuất đã được từ chối.');
                
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
                'date' => now()->format('Y-m-d'), // Ngày lắp ráp = ngày duyệt
                'warehouse_id' => $defaultWarehouse->id,
                'target_warehouse_id' => $defaultWarehouse->id,
                'assigned_employee_id' => $projectRequest->assembly_leader_id, // Người phụ trách lắp ráp
                'tester_id' => $projectRequest->tester_id, // Người tiếp nhận kiểm thử
                'purpose' => 'project', // Mục đích: xuất đi dự án
                'project_id' => null,
                'status' => 'pending', // Trạng thái: Chờ xử lý
                'notes' => 'Tự động tạo từ phiếu đề xuất dự án #' . $projectRequest->id . ' - ' . $projectRequest->project_name,
            ]);

            // Ghi nhật ký tạo phiếu lắp ráp
            if (Auth::check()) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'assemblies',
                    'Tạo phiếu lắp ráp tự động từ phiếu đề xuất dự án: ' . $assembly->code,
                    null,
                    $assembly->toArray()
                );
            }
            
            // Thêm các sản phẩm từ phiếu đề xuất vào phiếu lắp ráp
            $productsAdded = false;
            
            // Lấy các items từ phiếu đề xuất
            $projectRequestItems = \App\Models\ProjectRequestItem::where('project_request_id', $projectRequest->id)
                ->where('item_type', 'equipment')
                ->get();
            
            foreach ($projectRequestItems as $item) {
                // Lấy thông tin sản phẩm từ item_id (chỉ lấy active và không bị ẩn)
                $product = \App\Models\Product::where('status', 'active')
                    ->where('is_hidden', false)
                    ->find($item->item_id);
                    
                    if ($product) {
                        // Thêm sản phẩm vào phiếu lắp ráp
                        \App\Models\AssemblyProduct::create([
                            'assembly_id' => $assembly->id,
                            'product_id' => $product->id,
                        'quantity' => $item->quantity,
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
                            'quantity' => $material->quantity * $item->quantity, // Số lượng vật tư = số lượng cần cho 1 sản phẩm * số lượng sản phẩm
                                'serial' => null,
                                'product_id' => $product->id // Liên kết vật tư với sản phẩm
                            ]);
                        }
                        
                        // Log thông tin
                        \Illuminate\Support\Facades\Log::info('Đã thêm sản phẩm và vật tư vào phiếu lắp ráp', [
                            'assembly_code' => $assembly->code,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                        'quantity' => $item->quantity,
                            'materials_count' => $productMaterials->count()
                        ]);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Không tìm thấy sản phẩm', [
                        'item_id' => $item->item_id,
                        'item' => $item->toArray()
                        ]);
                }
            }
            
            // Nếu không có sản phẩm nào được thêm, thêm sản phẩm mặc định
            if (!$productsAdded) {
                // Tìm sản phẩm đầu tiên trong hệ thống (chỉ lấy active và không bị ẩn)
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

    /**
     * Tạo phiếu xuất kho tự động từ phiếu đề xuất dự án
     */
    private function createDispatchFromRequest($projectRequest)
    {
        // Tạo phiếu xuất kho mới
        $projectId = $this->getProjectIdFromRequest($projectRequest);
        
        // Log để debug project_id
        Log::info('Project ID from request:', [
            'original_project_id' => $projectRequest->project_id,
            'extracted_project_id' => $projectId
        ]);
        
        // Nếu không tìm thấy project_id, thử lấy từ project_name
        if (!$projectId) {
            Log::warning('Không tìm thấy project_id, thử tìm từ project_name');
            
            // Tìm project theo tên
            $project = Project::where('project_name', 'like', '%' . $projectRequest->project_name . '%')
                ->orWhere('project_code', 'like', '%' . $projectRequest->project_name . '%')
                ->first();
            
            if ($project) {
                $projectId = $project->id;
                Log::info('Tìm thấy project theo tên:', [
                    'project_name' => $projectRequest->project_name,
                    'found_project_id' => $projectId
                ]);
            }
        }
        
        // Lấy thông tin customer để mapping đúng
        $customer = Customer::find($projectRequest->customer_id);
        
        // Tìm employee tương ứng với customer (người đại diện)
        $companyRepresentative = null;
        if ($customer) {
            // Tìm employee có tên trùng với customer name hoặc company name
            $companyRepresentative = Employee::where('name', 'like', '%' . $customer->name . '%')
                ->orWhere('name', 'like', '%' . $customer->company_name . '%')
                ->first();
            
            // Nếu không tìm thấy, thử tìm từ project
            if (!$companyRepresentative && $projectId) {
                $project = Project::find($projectId);
                if ($project && $project->representative_id) {
                    $companyRepresentative = Employee::find($project->representative_id);
                }
            }
        }
        
        // Log để debug
        Log::info('Mapping dispatch data:', [
            'customer_id' => $projectRequest->customer_id,
            'customer_name' => $customer ? $customer->name : 'N/A',
            'customer_company' => $customer ? $customer->company_name : 'N/A',
            'project_id' => $projectId,
            'company_representative_id' => $companyRepresentative ? $companyRepresentative->id : 'N/A',
            'company_representative_name' => $companyRepresentative ? $companyRepresentative->name : 'N/A',
            'project_receiver' => $customer ? $customer->company_name : $projectRequest->project_name
        ]);
        
        // Xác định loại (project hoặc rental) dựa trên dữ liệu của phiếu đề xuất
        // ƯU TIÊN dựa vào cột khóa ngoại thay vì suy đoán theo tên
        $isRental = !empty($projectRequest->rental_id);
        
        // Debug log để kiểm tra
        Log::info('Debug rental logic:', [
            'project_name' => $projectRequest->project_name,
            'is_rental' => $isRental,
            'extracted_project_id' => $projectId
        ]);
        
        // Sử dụng project_id nếu có, nếu không thì null
        $actualProjectId = $isRental ? null : $projectId;

        // Tạo trường project_receiver theo đúng mã thực tế
        $projectReceiver = '';
        if ($isRental) {
            // Cho thuê: lấy theo rental thực tế nếu có
            $rental = \App\Models\Rental::with('customer')->find($projectRequest->rental_id);
            if ($rental) {
                // Với cho thuê: luôn hiển thị Tên công ty trong ngoặc
                $companyName = $rental->customer->company_name ?? 'N/A';
                $projectReceiver = ($rental->rental_code ?? ('RENTAL-' . date('YmdHis'))) . ' - ' . ($rental->rental_name ?? $projectRequest->project_name) . ' (' . $companyName . ')';
            } else {
                // Fallback
                $projectReceiver = 'RENTAL-' . date('YmdHis') . ' - ' . $projectRequest->project_name . ' (' . ($customer ? $customer->name : 'N/A') . ')';
            }
        } else {
            // Dự án: lấy theo project thực tế nếu có
            $project = $actualProjectId ? Project::with('customer')->find($actualProjectId) : null;
            if ($project) {
                $projectReceiver = ($project->project_code ?? ('PRJ-' . date('YmdHis'))) . ' - ' . ($project->project_name ?? $projectRequest->project_name) . ' (' . ($project->customer->name ?? $project->customer->company_name ?? 'N/A') . ')';
            } else {
                // Fallback
                $projectReceiver = 'PRJ-' . date('YmdHis') . ' - ' . $projectRequest->project_name . ' (' . ($customer ? $customer->name : 'N/A') . ')';
            }
        }

        // Tạo trường dispatch_note theo định dạng yêu cầu
        $dispatchNote = 'Tự động tạo từ phiếu đề xuất ' . ($isRental ? 'cho thuê' : 'dự án') . ' ' . $projectRequest->request_code;

        $dispatch = Dispatch::create([
            'dispatch_code' => 'DISP-' . date('YmdHis'),
            'dispatch_date' => now(), // Ngày xuất = ngày duyệt
            'dispatch_type' => $isRental ? 'rental' : 'project', // Loại hình
            'dispatch_detail' => 'contract', // Chi tiết xuất kho: Xuất theo hợp đồng
            'project_id' => $actualProjectId, // Lưu project_id nếu có (rental thì để null)
            'project_receiver' => $projectReceiver, // Người nhận theo định dạng yêu cầu
            'company_representative_id' => $companyRepresentative ? $companyRepresentative->id : ($projectRequest->implementer_id ?? $projectRequest->proposer_id), // Người đại diện = employee tương ứng
            'dispatch_note' => $dispatchNote, // Ghi chú theo định dạng yêu cầu
            'status' => 'pending', // Trạng thái: Chờ xử lý
            'created_by' => Auth::id() ?? 1, // Người tạo phiếu = người duyệt
            'warranty_period' => null,
        ]);
        
        // Log kết quả tạo dispatch
        Log::info('Dispatch created:', [
            'dispatch_id' => $dispatch->id,
            'dispatch_code' => $dispatch->dispatch_code,
            'project_id' => $dispatch->project_id,
            'project_receiver' => $dispatch->project_receiver
        ]);

        // Ghi nhật ký tạo phiếu xuất kho
        if (Auth::check()) {
            \App\Models\UserLog::logActivity(
                Auth::id(),
                'create',
                'dispatches',   
                'Tạo phiếu xuất kho tự động từ phiếu đề xuất dự án: ' . $dispatch->dispatch_code,
                null,
                $dispatch->toArray()
            );
        }

        // Lấy warehouse mặc định (chỉ làm fallback, ưu tiên chia theo tồn kho thực tế)
        $defaultWarehouse = Warehouse::query()
            ->where('status', 'active')
            ->where('is_hidden', false)
            ->first();

        // Lấy các items từ phiếu đề xuất
        $projectRequestItems = \App\Models\ProjectRequestItem::where('project_request_id', $projectRequest->id)->get();

        // Lặp qua các items trong phiếu đề xuất và tạo dispatch items tương ứng
            foreach ($projectRequestItems as $item) {
            // Xác định loại item và thêm thông tin tương ứng
            switch ($item->item_type) {
                case 'equipment':
                    $itemType = 'product';
                    $itemId = $item->item_id;
                    break;
                case 'material':
                    $itemType = 'material';
                    $itemId = $item->item_id;
                    break;
                case 'good':
                    $itemType = 'good';
                    $itemId = $item->item_id;
                    break;
                default:
                    throw new \Exception('Loại item không hợp lệ: ' . $item->item_type);
            }
                
                // Kiểm tra xem item có tồn tại và active không
                $itemExists = false;
                switch ($itemType) {
                    case 'product':
                        $itemExists = \App\Models\Product::where('status', 'active')
                            ->where('is_hidden', false)
                            ->where('id', $itemId)
                            ->exists();
                        break;
                    case 'material':
                        $itemExists = \App\Models\Material::where('status', 'active')
                            ->where('is_hidden', false)
                            ->where('id', $itemId)
                            ->exists();
                        break;
                    case 'good':
                        $itemExists = \App\Models\Good::where('status', 'active')
                            ->where('is_hidden', false)
                            ->where('id', $itemId)
                            ->exists();
                        break;
                }
                
                // Bỏ qua item nếu không tồn tại hoặc bị ẩn
                if (!$itemExists) {
                    continue;
                }

            // Phân bổ từ nhiều kho (chỉ trong các kho dùng để tính tồn kho của item):
            $remaining = (int) $item->quantity;

            // Lấy danh sách tồn kho theo item_type chính xác
            $stockQuery = \App\Models\WarehouseMaterial::where('material_id', $itemId)
                ->where('item_type', $itemType)
                ->whereHas('warehouse', function($q) { $q->where('status', 'active')->where('is_hidden', false); })
                ->where('quantity', '>', 0);

            // KHÔNG giới hạn theo inventory_warehouses nữa - lấy tồn kho từ TẤT CẢ các kho
            // Đây là thay đổi chính: bỏ giới hạn kho để có thể tạo phiếu xuất từ nhiều kho
            // $allowedWarehouseIds = [];
            // if ($itemType === 'product') {
            //     $obj = \App\Models\Product::find($itemId);
            //     $allowedWarehouseIds = is_array($obj?->inventory_warehouses) ? $obj->inventory_warehouses : [];
            // } elseif ($itemType === 'material') {
            //     $obj = \App\Models\Material::find($itemId);
            //     $allowedWarehouseIds = is_array($obj?->inventory_warehouses) ? $obj->inventory_warehouses : [];
            // } elseif ($itemType === 'good') {
            //     $obj = \App\Models\Good::find($itemId);
            //     $allowedWarehouseIds = is_array($obj?->inventory_warehouses) ? $obj->inventory_warehouses : [];
            // }
            // if (!empty($allowedWarehouseIds) && !in_array('all', $allowedWarehouseIds)) {
            //     $stockQuery->whereIn('warehouse_id', $allowedWarehouseIds);
            // }

            $stockRows = $stockQuery->orderByDesc('quantity')->get();

            // Nếu không có theo item_type chính xác, lấy tất cả (fallback)
            if ($stockRows->isEmpty()) {
                $fallbackQuery = \App\Models\WarehouseMaterial::where('material_id', $itemId)
                    ->whereHas('warehouse', function($q) { $q->where('status', 'active')->where('is_hidden', false); })
                    ->where('quantity', '>', 0);
                // KHÔNG giới hạn theo inventory_warehouses nữa
                // if (!empty($allowedWarehouseIds) && !in_array('all', $allowedWarehouseIds)) {
                //     $fallbackQuery->whereIn('warehouse_id', $allowedWarehouseIds);
                // }
                $stockRows = $fallbackQuery->orderByDesc('quantity')->get();
            }

            // Log để debug phân bổ từ nhiều kho
            Log::info('Phân bổ item từ nhiều kho (từ TẤT CẢ các kho):', [
                'item_id' => $itemId,
                'item_type' => $itemType,
                'quantity_requested' => $remaining,
                'stock_rows_count' => $stockRows->count(),
                'note' => 'Đã bỏ giới hạn inventory_warehouses - lấy từ tất cả kho',
                'stock_details' => $stockRows->map(function($wm) {
                    return [
                        'warehouse_id' => $wm->warehouse_id,
                        'warehouse_name' => $wm->warehouse->name ?? 'N/A',
                        'quantity' => $wm->quantity,
                        'item_type' => $wm->item_type
                    ];
                })->toArray()
            ]);

            // Đảm bảo $remaining không vượt quá số lượng yêu cầu
            $originalQuantity = $item->quantity;
            $remaining = min($remaining, $originalQuantity);
            
            Log::info('Bắt đầu phân bổ với số lượng đã điều chỉnh:', [
                'item_id' => $itemId,
                'original_quantity' => $originalQuantity,
                'remaining_adjusted' => $remaining
            ]);

            foreach ($stockRows as $wm) {
                if ($remaining <= 0) {
                    Log::info('Đã đủ số lượng, dừng phân bổ:', [
                        'item_id' => $itemId,
                        'remaining' => $remaining
                    ]);
                    break;
                }
                
                $takeQty = min($remaining, (int) $wm->quantity);
                if ($takeQty <= 0) continue;

                // Log trước khi tạo DispatchItem
                Log::info('Tạo DispatchItem:', [
                    'warehouse_id' => $wm->warehouse_id,
                    'warehouse_name' => $wm->warehouse->name ?? 'N/A',
                    'take_qty' => $takeQty,
                    'remaining_before' => $remaining,
                    'item_id' => $itemId,
                    'item_type' => $itemType,
                    'warehouse_stock' => $wm->quantity
                ]);

            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                    'warehouse_id' => $wm->warehouse_id,
                'item_type' => $itemType,
                'item_id' => $itemId,
                    'quantity' => $takeQty,
                    'category' => 'contract',
                'notes' => 'Tự động tạo từ phiếu đề xuất dự án #' . $projectRequest->id,
                'serial_numbers' => null
                ]);

                $remaining -= $takeQty;
                
                // Log sau khi tạo DispatchItem
                Log::info('Đã tạo DispatchItem, còn lại:', [
                    'warehouse_id' => $wm->warehouse_id,
                    'take_qty' => $takeQty,
                    'remaining_after' => $remaining,
                    'dispatch_item_created' => true
                ]);
            }

            // Nếu vẫn còn thiếu (trường hợp chênh lệch nhỏ), gán vào kho mặc định để người dùng xử lý sau
            if ($remaining > 0 && $defaultWarehouse) {
                Log::warning('Vẫn còn thiếu số lượng, gán vào kho mặc định:', [
                    'item_id' => $itemId,
                    'remaining_quantity' => $remaining,
                    'default_warehouse_id' => $defaultWarehouse->id,
                    'default_warehouse_name' => $defaultWarehouse->name
                ]);
                
                DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'warehouse_id' => $defaultWarehouse->id,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'quantity' => $remaining,
                    'category' => 'contract',
                    'notes' => 'Phần còn thiếu cần xử lý tồn kho thủ công - tạo từ phiếu đề xuất #' . $projectRequest->id,
                    'serial_numbers' => null
                ]);
                $remaining = 0;
            }
            
            // Log kết quả cuối cùng cho item này
            Log::info('Hoàn thành phân bổ item:', [
                'item_id' => $itemId,
                'item_type' => $itemType,
                'original_quantity' => $originalQuantity,
                'remaining_after_allocation' => $remaining,
                'total_allocated' => $originalQuantity - $remaining
            ]);
        }

        return $dispatch;
    }

    /**
     * Kiểm tra tồn kho trước khi tạo phiếu xuất kho
     */
    private function checkStockForDispatch($projectRequest)
    {
        $insufficientItems = [];
        $projectRequestItems = $projectRequest->items;

        // Debug log
        Log::info('Bắt đầu kiểm tra tồn kho cho phiếu đề xuất', [
            'project_request_id' => $projectRequest->id,
            'items_count' => $projectRequestItems->count(),
            'approval_method' => $projectRequest->approval_method
        ]);

        foreach ($projectRequestItems as $item) {
            $itemId = $item->item_id;
            $quantityRequested = $item->quantity;
            $itemType = $item->item_type;

            // Debug log cho từng item
            Log::info('Kiểm tra item', [
                'item_id' => $itemId,
                'item_name' => $item->name,
                'item_type' => $itemType,
                'quantity_requested' => $quantityRequested
            ]);

            // Xác định loại item để kiểm tra tồn kho
            $stockItemType = null;
            switch ($itemType) {
                case 'equipment':
                    $stockItemType = 'product';
                    break;
                case 'material':
                    $stockItemType = 'material';
                    break;
                case 'good':
                    $stockItemType = 'good';
                    break;
            }

            if (!$stockItemType) {
                $insufficientItems[] = "{$item->name} (ID: {$itemId}) - Loại item không hợp lệ";
                Log::warning('Loại item không hợp lệ', ['item_type' => $itemType]);
                continue;
            }

            // Tính tổng tồn kho từ TẤT CẢ các kho (không chỉ giới hạn trong inventory_warehouses)
            // Đây là thay đổi chính: bỏ giới hạn kho để tính tổng tồn kho
            $sumQuery = \App\Models\WarehouseMaterial::where('material_id', $itemId)
                ->where(function($q) use ($stockItemType) {
                    $q->where('item_type', $stockItemType)
                      ->orWhereNull('item_type');
                })
                    ->whereHas('warehouse', function($q) {
                        $q->where('status', 'active')->where('is_hidden', false);
                });
            
            // KHÔNG giới hạn theo inventory_warehouses nữa - tính từ tất cả kho
            // if (!empty($allowedWarehouseIds) && !in_array('all', $allowedWarehouseIds)) {
            //     $sumQuery->whereIn('warehouse_id', $allowedWarehouseIds);
            // }
            $quantityInStock = (int) $sumQuery->sum('quantity');

            // Debug log kết quả tìm kiếm (tổng theo tất cả kho)
            Log::info('Kết quả kiểm tra tổng tồn kho', [
                'item_id' => $itemId,
                'stock_item_type' => $stockItemType,
                'quantity_in_stock' => $quantityInStock
            ]);

            if ($quantityInStock == 0) {
                $insufficientItems[] = "{$item->name} (ID: {$itemId}) - Không tồn tại trong kho";
                continue;
            }

            if ($quantityInStock < $quantityRequested) {
                $insufficientItems[] = "{$item->name} - Yêu cầu: {$quantityRequested}, Tồn kho: {$quantityInStock}";
            }
        }

        // Debug log kết quả cuối cùng
        Log::info('Kết quả kiểm tra tồn kho', [
            'has_insufficient_stock' => count($insufficientItems) > 0,
            'insufficient_items' => $insufficientItems
        ]);

        return [
            'has_insufficient_stock' => count($insufficientItems) > 0,
            'insufficient_items' => $insufficientItems
        ];
    }

    /**
     * Tìm kho có nhiều tồn kho nhất cho loại vật tư
     */
    private function findBestWarehouse($itemType, $itemId)
    {
        // Tìm kho có nhiều tồn kho nhất cho vật tư này với item_type chính xác
        $bestWarehouseMaterial = \App\Models\WarehouseMaterial::where('material_id', $itemId)
            ->where('item_type', $itemType)
            ->whereHas('warehouse', function($q) {
                $q->where('status', 'active')->where('is_hidden', false);
            })
            ->where('quantity', '>', 0) // Chỉ lấy kho có tồn kho > 0
            ->orderBy('quantity', 'desc')
            ->first();
            
        if ($bestWarehouseMaterial) {
            return $bestWarehouseMaterial->warehouse;
        }
        
        // Nếu không tìm thấy với item_type chính xác, thử tìm không có điều kiện item_type
        $fallbackWarehouseMaterial = \App\Models\WarehouseMaterial::where('material_id', $itemId)
            ->whereHas('warehouse', function($q) {
                $q->where('status', 'active')->where('is_hidden', false);
            })
            ->where('quantity', '>', 0) // Chỉ lấy kho có tồn kho > 0
            ->orderBy('quantity', 'desc')
            ->first();
            
        if ($fallbackWarehouseMaterial) {
            Log::info('Found fallback warehouse for item (without item_type filter)', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $fallbackWarehouseMaterial->warehouse->id,
                'warehouse_name' => $fallbackWarehouseMaterial->warehouse->name,
                'quantity' => $fallbackWarehouseMaterial->quantity,
                'actual_item_type' => $fallbackWarehouseMaterial->item_type
            ]);
            return $fallbackWarehouseMaterial->warehouse;
        }
        
        // Nếu không tìm thấy trong WarehouseMaterial, trả về kho mặc định
        $defaultWarehouse = \App\Models\Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->first();
            
        return $defaultWarehouse;
    }

    // Method cũ đã được thay thế bằng updateProjectOrRentalWithItems

    /**
     * Lấy project_id từ phiếu đề xuất
     */
    private function getProjectIdFromRequest($projectRequest)
    {
        // Kiểm tra xem có project_id không
        if ($projectRequest->project_id) {
            // Nếu có project_id, trả về project_id
            Log::info('Found project_id in project_request:', [
                'project_id' => $projectRequest->project_id,
                'project_name' => $projectRequest->project_name
            ]);
            return $projectRequest->project_id;
        }
        
        // Fallback: thử tìm project theo tên
        Log::warning('No project_id found, trying to find by project_name');
        $project = Project::where('project_name', 'like', '%' . $projectRequest->project_name . '%')
            ->orWhere('project_code', 'like', '%' . $projectRequest->project_name . '%')
            ->first();
        
        if ($project) {
            Log::info('Found project by name:', [
                'project_name' => $projectRequest->project_name,
                'found_project_id' => $project->id
            ]);
            return $project->id;
        }
        
        Log::warning('No project found for project_request:', [
            'project_request_id' => $projectRequest->id,
            'project_name' => $projectRequest->project_name,
            'project_id' => $projectRequest->project_id
        ]);
        
        return null;
    }

    /**
     * Cập nhật phiếu dự án hoặc phiếu cho thuê với các thiết bị đã được xuất
     */
    private function updateProjectOrRentalWithItems($projectRequest, $dispatch)
    {
        try {
            // Xác định loại (project hoặc rental) dựa trên dữ liệu của phiếu đề xuất
            $isRental = !empty($projectRequest->rental_id);
            
            Log::info('Bắt đầu cập nhật phiếu dự án/cho thuê với thiết bị:', [
                'project_request_id' => $projectRequest->id,
                'is_rental' => $isRental,
                'dispatch_id' => $dispatch->id,
                'dispatch_code' => $dispatch->dispatch_code
            ]);

            if ($isRental) {
                // Cập nhật phiếu cho thuê
                $this->updateRentalWithItems($projectRequest, $dispatch);
            } else {
                // Cập nhật phiếu dự án
                $this->updateProjectWithItems($projectRequest, $dispatch);
            }
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật phiếu dự án/cho thuê với thiết bị: ' . $e->getMessage(), [
                'project_request_id' => $projectRequest->id,
                'dispatch_id' => $dispatch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Cập nhật phiếu cho thuê với các thiết bị đã được xuất
     */
    private function updateRentalWithItems($projectRequest, $dispatch)
    {
        try {
            $rentalId = $projectRequest->rental_id;
            
            if (!$rentalId) {
                Log::warning('Không tìm thấy rental_id trong phiếu đề xuất', [
                    'project_request_id' => $projectRequest->id
                ]);
                return;
            }

            // Lấy các items từ phiếu đề xuất (chỉ lấy equipment)
            $projectRequestItems = \App\Models\ProjectRequestItem::where('project_request_id', $projectRequest->id)
                ->where('item_type', 'equipment')
                ->get();

            Log::info('Cập nhật phiếu cho thuê với thiết bị:', [
                'rental_id' => $rentalId,
                'items_count' => $projectRequestItems->count()
            ]);

            foreach ($projectRequestItems as $item) {
                // Lấy thông tin sản phẩm
                $product = \App\Models\Product::where('status', 'active')
                    ->where('is_hidden', false)
                    ->find($item->item_id);
                
                if ($product) {
                    // Tìm dispatch item tương ứng để lấy thông tin xuất kho
                    $dispatchItems = \App\Models\DispatchItem::where('dispatch_id', $dispatch->id)
                        ->where('item_type', 'product')
                        ->where('item_id', $product->id)
                        ->get();

                    foreach ($dispatchItems as $dispatchItem) {
                        // Tạo rental item cho từng kho xuất
                        \App\Models\RentalItem::create([
                            'rental_id' => $rentalId,
                            'item_type' => 'product',
                            'item_id' => $product->id,
                            'quantity' => $dispatchItem->quantity,
                            'warehouse_id' => $dispatchItem->warehouse_id,
                            'dispatch_id' => $dispatch->id,
                            'dispatch_item_id' => $dispatchItem->id,
                            'status' => 'active',
                            'notes' => 'Tự động tạo từ phiếu đề xuất dự án #' . $projectRequest->id
                        ]);

                        Log::info('Đã tạo rental item:', [
                            'rental_id' => $rentalId,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $dispatchItem->quantity,
                            'warehouse_id' => $dispatchItem->warehouse_id,
                            'dispatch_item_id' => $dispatchItem->id
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật phiếu cho thuê với thiết bị: ' . $e->getMessage(), [
                'project_request_id' => $projectRequest->id,
                'rental_id' => $rentalId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Cập nhật phiếu dự án với các thiết bị đã được xuất
     * Lưu ý: Thiết bị của dự án được lưu trực tiếp trong dispatch_items với project_id
     * Không cần tạo ProjectItem riêng biệt
     */
    private function updateProjectWithItems($projectRequest, $dispatch)
    {
        try {
            // Lấy project_id thực tế
            $actualId = $this->getProjectIdFromRequest($projectRequest);
            
            if (!$actualId) {
                Log::warning('Không thể cập nhật dự án: ID không tìm thấy', [
                    'project_request_id' => $projectRequest->id,
                    'project_name' => $projectRequest->project_name
                ]);
                return;
            }

            Log::info('Cập nhật phiếu dự án với thiết bị:', [
                'project_id' => $actualId,
                'dispatch_id' => $dispatch->id,
                'note' => 'Thiết bị đã được lưu trong dispatch_items với project_id'
            ]);
            
            // Thiết bị của dự án đã được lưu trong dispatch_items với project_id
            // Không cần tạo ProjectItem riêng biệt
            // Có thể xem thiết bị trong phiếu dự án thông qua dispatch_items
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật phiếu dự án với thiết bị: ' . $e->getMessage(), [
                'project_request_id' => $projectRequest->id,
                'project_id' => $actualId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Test method để kiểm tra logic kiểm tra tồn kho
     */
    public function testStockCheck($id)
    {
        $projectRequest = ProjectRequest::with(['items'])->findOrFail($id);
        
        $stockCheckResult = $this->checkStockForDispatch($projectRequest);
        
        return response()->json([
            'project_request_id' => $projectRequest->id,
            'approval_method' => $projectRequest->approval_method,
            'items_count' => $projectRequest->items->count(),
            'stock_check_result' => $stockCheckResult,
            'items' => $projectRequest->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'name' => $item->name,
                    'item_type' => $item->item_type,
                    'quantity' => $item->quantity
                ];
            })
        ]);
    }
} 