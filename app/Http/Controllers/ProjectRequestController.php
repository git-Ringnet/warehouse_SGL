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
        // Debug logging
        Log::info('🚀 ProjectRequestController::store started', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'timestamp' => now()
        ]);
        
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
            'proposer_id' => 'required|exists:employees,id',
            'implementer_id' => 'nullable|exists:employees,id',
            'project_id' => 'required',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'nullable', // Bỏ required vì sẽ tự động điền
            'project_address' => 'required|string|max:255',
            'approval_method' => 'required|in:production,warehouse',
            'item_type' => 'required|in:equipment,material,good',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];
        
        Log::info('📋 Validation rules:', [
            'base_rules' => $baseRules,
            'item_type' => $request->input('item_type'),
            'approval_method' => $request->input('approval_method')
        ]);
        
        // Thêm rules dựa vào loại item được chọn
        $itemType = $request->input('item_type');
        $rules = $baseRules;
        
        // Validate thêm cho lắp ráp
        if ($request->approval_method === 'production') {
            $rules['implementer_id'] = 'required|exists:employees,id';
            // Khi chọn "Sản xuất lắp ráp" thì chỉ cho phép "equipment" (thành phẩm)
            $rules['item_type'] = 'required|in:equipment';
        } else {
            // Khi chọn "Xuất kho" thì cho phép cả 3 loại
            $rules['item_type'] = 'required|in:equipment,material,good';
        }
        
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
                $rules['good'] = 'required|array|min:1';
                $rules['good.*.id'] = 'required|exists:goods,id';
                $rules['good.*.quantity'] = 'required|integer|min:1';
                break;
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        Log::info('🔍 Validation result:', [
            'rules' => $rules,
            'data' => $request->all(),
            'passes' => $validator->passes(),
            'errors' => $validator->errors()->toArray()
        ]);
        
        if ($validator->fails()) {
            Log::error('❌ Validation failed:', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        Log::info('✅ Validation passed successfully');
        
        // Kiểm tra thêm xem các item có active và không bị ẩn không
        $items = [];
        switch ($itemType) {
            case 'equipment':
                $items = $request->input('equipment') ?? [];
                break;
            case 'material':
                $items = $request->input('material') ?? [];
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
                case 'material':
                    $itemExists = Material::where('status', 'active')
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
            Log::info('💾 Starting database transaction');
            DB::beginTransaction();
            
            // Xử lý project_id để phân biệt project và rental
            $projectId = $request->project_id;
            $projectType = null;
            $actualProjectId = null;
            
            Log::info('🏗️ Processing project_id:', [
                'original_project_id' => $projectId,
                'project_name' => $request->project_name
            ]);
            
            if (strpos($projectId, 'project_') === 0) {
                $projectType = 'project';
                $actualProjectId = substr($projectId, 8); // Bỏ 'project_' prefix
            } elseif (strpos($projectId, 'rental_') === 0) {
                $projectType = 'rental';
                $actualProjectId = substr($projectId, 7); // Bỏ 'rental_' prefix
            }
            
            // Lấy thông tin dự án/phiếu cho thuê từ ID
            Log::info('🔍 Looking up project/rental:', [
                'project_type' => $projectType,
                'actual_project_id' => $actualProjectId
            ]);
            
            if ($projectType === 'project') {
                $project = Project::with('customer')->findOrFail($actualProjectId);
                
                Log::info('✅ Found project:', [
                    'project_id' => $project->id,
                    'project_name' => $project->project_name,
                    'has_valid_warranty' => $project->has_valid_warranty,
                    'customer_id' => $project->customer->id ?? null
                ]);
                
                // Kiểm tra xem dự án còn hiệu lực bảo hành không
                if (!$project->has_valid_warranty) {
                    Log::warning('❌ Project warranty expired:', [
                        'project_id' => $project->id,
                        'project_name' => $project->project_name
                    ]);
                    return redirect()->back()
                        ->with('error', 'Dự án này đã hết hạn bảo hành và không thể tạo phiếu đề xuất.')
                        ->withInput();
                }
                
                $customer = $project->customer;
            } else {
                $rental = Rental::with('customer')->findOrFail($actualProjectId);
                
                Log::info('✅ Found rental:', [
                    'rental_id' => $rental->id,
                    'rental_name' => $rental->rental_name,
                    'has_valid_warranty' => $rental->has_valid_warranty,
                    'customer_id' => $rental->customer->id ?? null
                ]);
                
                // Kiểm tra xem rental còn hiệu lực bảo hành không
                if (!$rental->has_valid_warranty) {
                    Log::warning('❌ Rental warranty expired:', [
                        'rental_id' => $rental->id,
                        'rental_name' => $rental->rental_name
                    ]);
                    return redirect()->back()
                        ->with('error', 'Phiếu cho thuê này đã hết hạn bảo hành và không thể tạo phiếu đề xuất.')
                        ->withInput();
                }
                
                $customer = $rental->customer;
            }
            
            // Tạo phiếu đề xuất mới
            $requestData = [
                'request_code' => ProjectRequest::generateRequestCode(),
                'request_date' => $request->request_date,
                'proposer_id' => $request->proposer_id,
                'implementer_id' => $request->implementer_id,
                'assembly_leader_id' => $request->approval_method === 'production' ? $request->proposer_id : null,
                'tester_id' => $request->approval_method === 'production' ? $request->implementer_id : null,
                'project_name' => $request->project_name,
                'customer_id' => $customer->id,
                'project_id' => $projectType === 'project' ? $actualProjectId : null,
                'rental_id' => $projectType === 'rental' ? $actualProjectId : null,
                'project_address' => $request->project_address,
                'approval_method' => $request->approval_method,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'customer_address' => $customer->address,
                'notes' => $request->notes,
                'status' => 'pending',
            ];
            
            Log::info('📝 Creating ProjectRequest with data:', $requestData);
            
            $projectRequest = ProjectRequest::create($requestData);
            
            Log::info('✅ ProjectRequest created successfully:', [
                'id' => $projectRequest->id,
                'request_code' => $projectRequest->request_code
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
            Log::info('✅ Database transaction committed successfully');
            
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
            
            Log::info('🎉 ProjectRequest creation completed successfully', [
                'project_request_id' => $projectRequest->id,
                'request_code' => $projectRequest->request_code,
                'redirect_url' => route('requests.project.show', $projectRequest->id)
            ]);
            
            return redirect()->route('requests.project.show', $projectRequest->id)
                ->with('success', 'Phiếu đề xuất triển khai dự án đã được tạo thành công.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log lỗi chi tiết
            Log::error('❌ Error creating project request:', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
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
        
        return view('requests.project.edit', compact('projectRequest', 'customers', 'employees', 'equipments', 'materials', 'goods'));
    }

    /**
     * Cập nhật phiếu đề xuất trong database
     */
    public function update(Request $request, $id)
    {
        // Validation cơ bản cho các trường chung
        $baseRules = [
            'request_date' => 'required|date',
            'project_name' => 'required|string|max:255',
            'project_address' => 'required|string|max:255',
            'approval_method' => 'required|in:production,warehouse',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'item_type' => 'required|in:equipment,material,good',
        ];
        
        // Thêm rules dựa vào loại item được chọn
        $itemType = $request->input('item_type');
        $rules = $baseRules;
        
        // Validate thêm cho lắp ráp
        if ($request->approval_method === 'production') {
            $rules['item_type'] = 'required|in:equipment';
        } else {
            $rules['item_type'] = 'required|in:equipment,material,good';
        }
        
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
                $rules['good'] = 'required|array|min:1';
                $rules['good.*.id'] = 'required|exists:goods,id';
                $rules['good.*.quantity'] = 'required|integer|min:1';
                break;
        }
        
        $validator = Validator::make($request->all(), $rules, [
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
            
            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $projectRequest->toArray();
            
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
            
            // Xóa tất cả items cũ
            $projectRequest->items()->delete();
            
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
            
            // Chỉ cho phép xóa nếu trạng thái là pending
            if ($projectRequest->status !== 'pending') {
                return redirect()->route('requests.project.show', $id)
                    ->with('error', 'Không thể xóa phiếu đề xuất đã được duyệt hoặc đang xử lý.');
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
        try {
            DB::beginTransaction();
            
            $projectRequest = ProjectRequest::with(['proposer', 'implementer', 'customer', 'items'])->findOrFail($id);
            $oldData = $projectRequest->toArray();
            
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
                    
                    // Cập nhật dự án với thiết bị
                    $this->updateProjectWithItems($projectRequest);
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
        $request->validate([
            'reject_reason' => 'required|string|min:5',
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối',
            'reject_reason.min' => 'Lý do từ chối phải có ít nhất 5 ký tự',
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
            $notes .= "\n[" . date('Y-m-d H:i:s') . "] Từ chối: " . $request->reject_reason;
            
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
        
        $dispatch = Dispatch::create([
            'dispatch_code' => 'DISP-' . date('YmdHis'),
            'dispatch_date' => now(), // Ngày xuất = ngày duyệt
            'dispatch_type' => 'project', // Loại hình: Dự án
            'dispatch_detail' => 'contract', // Chi tiết xuất kho: Xuất theo hợp đồng
            'customer_id' => $projectRequest->customer_id,
            'project_id' => $projectId, // Có thể null nếu là rental
            'project_receiver' => $customer ? $customer->company_name : $projectRequest->project_name, // Người nhận = tên công ty
            'company_representative_id' => $companyRepresentative ? $companyRepresentative->id : ($projectRequest->implementer_id ?? $projectRequest->proposer_id), // Người đại diện = employee tương ứng
            'dispatch_note' => 'Tự động tạo từ phiếu đề xuất dự án #' . $projectRequest->id,
            'status' => 'pending', // Trạng thái: Chờ xử lý
            'created_by' => Auth::id() ?? 1, // Người tạo phiếu = người duyệt
            'warranty_period' => null,
            'rental_id' => null,
        ]);
        
        // Log kết quả tạo dispatch
        Log::info('Dispatch created:', [
            'dispatch_id' => $dispatch->id,
            'dispatch_code' => $dispatch->dispatch_code,
            'project_id' => $dispatch->project_id,
            'customer_id' => $dispatch->customer_id,
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

        // Lấy warehouse mặc định
        $defaultWarehouse = Warehouse::query()
            ->where('status', 'active')
            ->where('is_hidden', false)
            ->first();

        if (!$defaultWarehouse) {
            throw new \Exception('Không tìm thấy kho mặc định để xuất hàng.');
        }

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

            // Tìm kho có nhiều tồn kho nhất cho loại vật tư này
            $bestWarehouse = $this->findBestWarehouse($itemType, $itemId);
            $warehouseId = $bestWarehouse ? $bestWarehouse->id : $defaultWarehouse->id;

            // Tạo dispatch item với đầy đủ thông tin
            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'warehouse_id' => $warehouseId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'quantity' => $item->quantity,
                'category' => 'contract', // Mặc định là contract theo yêu cầu
                'notes' => 'Tự động tạo từ phiếu đề xuất dự án #' . $projectRequest->id,
                'serial_numbers' => null
            ]);
        }

        return $dispatch;
    }

    /**
     * Tìm kho có nhiều tồn kho nhất cho loại vật tư
     */
    private function findBestWarehouse($itemType, $itemId)
    {
        // Tìm kho có nhiều tồn kho nhất cho vật tư này
        $bestWarehouseMaterial = \App\Models\WarehouseMaterial::where('material_id', $itemId)
            ->whereHas('warehouse', function($q) {
                $q->where('status', 'active')->where('is_hidden', false);
            })
            ->orderBy('quantity', 'desc')
            ->first();
            
        if ($bestWarehouseMaterial) {
            return $bestWarehouseMaterial->warehouse;
        }
        
        // Nếu không tìm thấy trong WarehouseMaterial, trả về kho mặc định
        $defaultWarehouse = \App\Models\Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->first();
            
        return $defaultWarehouse;
    }

    /**
     * Cập nhật dự án với thiết bị từ phiếu đề xuất
     */
    private function updateProjectWithItems($projectRequest)
    {
        try {
            // Lấy project_id từ phiếu đề xuất
            $projectId = $this->getProjectIdFromRequest($projectRequest);
            
            if (!$projectId) {
                Log::warning('Không thể cập nhật dự án: project_id không tìm thấy', [
                    'project_request_id' => $projectRequest->id,
                    'original_project_id' => $projectRequest->project_id
                ]);
                return;
            }
            
            // Xác định loại (project hoặc rental)
            $originalProjectId = $projectRequest->project_id;
            $isRental = strpos($originalProjectId, 'rental_') === 0;
            
            // Lấy phiếu xuất kho mới nhất
            $latestDispatch = Dispatch::where('project_id', $projectId)
                ->where('dispatch_type', $isRental ? 'rental' : 'project')
                ->latest()
                ->first();
            
            if (!$latestDispatch) {
                Log::warning('Không tìm thấy phiếu xuất kho', [
                    'project_id' => $projectId,
                    'dispatch_type' => $isRental ? 'rental' : 'project',
                    'project_request_id' => $projectRequest->id
                ]);
                return;
            }
            
            // Lấy các items từ phiếu đề xuất
            $projectRequestItems = \App\Models\ProjectRequestItem::where('project_request_id', $projectRequest->id)
                ->where('item_type', 'equipment')
                ->get();
            
            foreach ($projectRequestItems as $item) {
                // Lấy thông tin sản phẩm (chỉ lấy active và không bị ẩn)
                $product = \App\Models\Product::where('status', 'active')
                    ->where('is_hidden', false)
                    ->find($item->item_id);
                
                if ($product) {
                    // Tìm dispatch item tương ứng
                    $dispatchItem = \App\Models\DispatchItem::where('dispatch_id', $latestDispatch->id)
                        ->where('item_type', 'product')
                        ->where('item_id', $product->id)
                        ->first();
                    
                    if ($dispatchItem) {
                        // Cập nhật số lượng trong dispatch item
                        $dispatchItem->update([
                            'quantity' => $item->quantity,
                            'notes' => 'Cập nhật từ phiếu đề xuất #' . $projectRequest->id
                        ]);
                        
                        Log::info('Đã cập nhật thiết bị', [
                            'type' => $isRental ? 'rental' : 'project',
                            'project_id' => $projectId,
                            'dispatch_id' => $latestDispatch->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $item->quantity
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật dự án với thiết bị: ' . $e->getMessage(), [
                'project_request_id' => $projectRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Lấy project_id từ phiếu đề xuất
     */
    private function getProjectIdFromRequest($projectRequest)
    {
        // Kiểm tra xem có project_id hoặc rental_id không
        if ($projectRequest->project_id) {
            Log::info('Found project_id in project_request:', [
                'project_id' => $projectRequest->project_id,
                'project_name' => $projectRequest->project_name
            ]);
            return $projectRequest->project_id;
        }
        
        if ($projectRequest->rental_id) {
            // Nếu có rental_id, lấy project_id từ rental
            $rental = Rental::find($projectRequest->rental_id);
            if ($rental && $rental->project_id) {
                Log::info('Found project_id from rental:', [
                    'rental_id' => $projectRequest->rental_id,
                    'project_id' => $rental->project_id
                ]);
                return $rental->project_id;
            }
        }
        
        // Fallback: thử tìm project theo tên
        Log::warning('No project_id or rental_id found, trying to find by project_name');
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
            'project_id' => $projectRequest->project_id,
            'rental_id' => $projectRequest->rental_id
        ]);
        
        return null;
    }
} 