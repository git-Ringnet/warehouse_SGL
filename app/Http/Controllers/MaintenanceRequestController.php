<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestProduct;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Notification;
use App\Models\Warranty;
use App\Models\Repair;
use App\Models\RepairItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Schema;

class MaintenanceRequestController extends Controller
{
    /**
     * API: Tạo yêu cầu hỗ trợ bảo hành/sửa chữa (Maintenance Request)
     */
    public function apiStore(Request $request)
    {
        try {
            // Chuẩn hóa ngày (cho phép dd/mm/YYYY)
            if ($request->filled('request_date')) {
                $request->merge(['request_date' => DateHelper::convertToDatabaseFormat($request->request_date)]);
            } else {
                $request->merge(['request_date' => now()->format('Y-m-d')]);
            }
            if ($request->filled('maintenance_date')) {
                $request->merge(['maintenance_date' => DateHelper::convertToDatabaseFormat($request->maintenance_date)]);
            } else {
                $request->merge(['maintenance_date' => now()->format('Y-m-d')]);
            }

            $request->validate([
                'project_type' => 'required|in:project,rental',
                'project_id' => 'required|integer',
                'maintenance_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
                'proposer_id' => 'nullable|integer',
                'proposer_code' => 'nullable|string',
                'proposer_username' => 'nullable|string',
                'proposer_email' => 'nullable|email',
                'notes' => 'nullable|string',
                'selected_devices' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Resolve proposer (accept id or employee code)
            $proposerId = null;
            if ($request->filled('proposer_id')) {
                $proposerId = (int) $request->proposer_id;
            } elseif ($request->filled('proposer_username')) {
                $proposerId = Employee::where('username', $request->proposer_username)->value('id');
            } elseif ($request->filled('proposer_email')) {
                $proposerId = Employee::where('email', $request->proposer_email)->value('id');
            } elseif ($request->filled('proposer_code')) {
                // Try flexible mapping for legacy fields if present
                if (Schema::hasColumn('employees', 'code')) {
                    $proposerId = Employee::where('code', $request->proposer_code)->value('id');
                } elseif (Schema::hasColumn('employees', 'employee_code')) {
                    $proposerId = Employee::where('employee_code', $request->proposer_code)->value('id');
                }
            }
            if (!$proposerId) {
                $proposerId = Auth::id() ?: Employee::where('is_active', true)->value('id');
            }

            // Map project/rental to customer info
            $projectName = '';
            $projectCode = '';
            $customerId = null;
            $customerName = '';
            $customerPhone = '';
            $customerEmail = '';
            $customerAddress = '';

            if ($request->project_type === 'project') {
                $project = \App\Models\Project::with('customer')->findOrFail($request->project_id);
                $projectName = $project->project_name;
                $projectCode = $project->project_code;
                $customerId = $project->customer_id;
                if ($project->customer) {
                    $customerName = $project->customer->company_name ?: $project->customer->name;
                    $customerPhone = $project->customer->phone;
                    $customerEmail = $project->customer->email;
                    $customerAddress = $project->customer->address ?: '';
                }
            } else {
                $rental = \App\Models\Rental::with('customer')->findOrFail($request->project_id);
                $projectName = $rental->rental_name;
                $projectCode = $rental->rental_code;
                $customerId = $rental->customer_id;
                if ($rental->customer) {
                    $customerName = $rental->customer->company_name ?: $rental->customer->name;
                    $customerPhone = $rental->customer->phone;
                    $customerEmail = $rental->customer->email;
                    $customerAddress = $rental->customer->address ?: '';
                }
            }

            $maintenanceRequest = MaintenanceRequest::create([
                'request_code' => MaintenanceRequest::generateRequestCode(),
                'request_date' => $request->request_date,
                'proposer_id' => $proposerId,
                'project_type' => $request->project_type,
                'project_id' => $request->project_id,
                'project_name' => $projectName,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail,
                'customer_address' => $customerAddress,
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type,
                'maintenance_reason' => $request->notes ?? '',
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Save selected devices if provided (reuse logic: expect JSON string array of "dispatchItemId_index")
            if ($request->filled('selected_devices')) {
                $selected = json_decode($request->selected_devices, true);
                if (is_array($selected)) {
                    foreach ($selected as $deviceKey) {
                        $parts = explode('_', $deviceKey);
                        $itemId = $parts[0] ?? null;
                        $index = $parts[1] ?? 0;
                        if (!$itemId) { continue; }
                        $dispatchItem = \App\Models\DispatchItem::with(['product','good'])->find($itemId);
                        if ($dispatchItem) {
                            $deviceCode = '';
                            $deviceName = '';
                            $deviceType = '';
                            if ($dispatchItem->item_type === 'product' && $dispatchItem->product) {
                                $deviceCode = $dispatchItem->product->code;
                                $deviceName = $dispatchItem->product->name;
                                $deviceType = 'Thành phẩm';
                            } elseif ($dispatchItem->item_type === 'good' && $dispatchItem->good) {
                                $deviceCode = $dispatchItem->good->code;
                                $deviceName = $dispatchItem->good->name;
                                $deviceType = 'Hàng hoá';
                            }
                            $serial = 'N/A';
                            if (!empty($dispatchItem->serial_numbers) && is_array($dispatchItem->serial_numbers)) {
                                $serial = $dispatchItem->serial_numbers[$index] ?? 'N/A';
                            }
                            MaintenanceRequestProduct::create([
                                'maintenance_request_id' => $maintenanceRequest->id,
                                'product_id' => $dispatchItem->item_id,
                                'product_code' => $deviceCode,
                                'product_name' => $deviceName,
                                'serial_number' => $serial,
                                'type' => $deviceType,
                                'quantity' => 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Lấy proposer_username
            $proposerUsername = null;
            if ($proposerId) {
                $proposer = Employee::find($proposerId);
                $proposerUsername = $proposer ? $proposer->username : null;
            }

            // Parse selected_devices từ JSON string
            $selectedDevicesArray = [];
            if ($request->filled('selected_devices')) {
                $selected = json_decode($request->selected_devices, true);
                if (is_array($selected)) {
                    $selectedDevicesArray = $selected;
                }
            }

            // Format request_date về dd/mm/YYYY
            $formattedRequestDate = $maintenanceRequest->request_date 
                ? \Carbon\Carbon::parse($maintenanceRequest->request_date)->format('d/m/Y')
                : null;

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu hỗ trợ đã được tạo thành công (pending).',
                'data' => [
                    'maintenance_request' => [
                        'request_date' => $formattedRequestDate,
                        'proposer_username' => $proposerUsername,
                        'request_code' => $maintenanceRequest->request_code,
                        'status' => $maintenanceRequest->status,
                        'project_code' => $projectCode,
                        'maintenance_type' => $maintenanceRequest->maintenance_type,
                        'maintenance_reason' => $maintenanceRequest->maintenance_reason ?? '',
                        'selected_devices' => $selectedDevicesArray
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API create maintenance request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Cập nhật yêu cầu hỗ trợ bảo hành/sửa chữa
     */
    public function apiUpdate(Request $request, $id)
    {
        try {
            $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu với ID: ' . $id
            ], 404);
        }

        try {
            $request->validate([
                'maintenance_type' => 'sometimes|in:maintenance,repair,replacement,upgrade,other',
                'notes' => 'nullable|string',
                'status' => 'sometimes|in:pending,approved,rejected,in_progress,completed,canceled',
                'selected_devices' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $update = [];
            if ($request->filled('maintenance_type')) $update['maintenance_type'] = $request->maintenance_type;
            if ($request->filled('notes')) $update['notes'] = $request->notes;
            if ($request->filled('status')) $update['status'] = $request->status;

            if (!empty($update)) {
                $maintenanceRequest->update($update);
            }

            // Optional: replace devices if provided
            if ($request->filled('selected_devices')) {
                $maintenanceRequest->products()->delete();
                $selected = json_decode($request->selected_devices, true);
                if (is_array($selected)) {
                    foreach ($selected as $deviceKey) {
                        $parts = explode('_', $deviceKey);
                        $itemId = $parts[0] ?? null;
                        $index = $parts[1] ?? 0;
                        if (!$itemId) { continue; }
                        $dispatchItem = \App\Models\DispatchItem::with(['product','good'])->find($itemId);
                        if ($dispatchItem) {
                            $deviceCode = '';
                            $deviceName = '';
                            $deviceType = '';
                            if ($dispatchItem->item_type === 'product' && $dispatchItem->product) {
                                $deviceCode = $dispatchItem->product->code;
                                $deviceName = $dispatchItem->product->name;
                                $deviceType = 'Thành phẩm';
                            } elseif ($dispatchItem->item_type === 'good' && $dispatchItem->good) {
                                $deviceCode = $dispatchItem->good->code;
                                $deviceName = $dispatchItem->good->name;
                                $deviceType = 'Hàng hoá';
                            }
                            $serial = 'N/A';
                            if (!empty($dispatchItem->serial_numbers) && is_array($dispatchItem->serial_numbers)) {
                                $serial = $dispatchItem->serial_numbers[$index] ?? 'N/A';
                            }
                            MaintenanceRequestProduct::create([
                                'maintenance_request_id' => $maintenanceRequest->id,
                                'product_id' => $dispatchItem->item_id,
                                'product_code' => $deviceCode,
                                'product_name' => $deviceName,
                                'serial_number' => $serial,
                                'type' => $deviceType,
                                'quantity' => 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu hỗ trợ đã được cập nhật.',
                'data' => [
                    'maintenance_request' => [
                        'id' => $maintenanceRequest->id,
                        'request_code' => $maintenanceRequest->request_code,
                        'status' => $maintenanceRequest->status,
                        'maintenance_type' => $maintenanceRequest->maintenance_type
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API update maintenance request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy TẤT CẢ phiếu bảo trì dự án (không lọc, không phân trang)
     */
    public function apiGetAllProject()
    {
        try {
            $maintenanceRequests = MaintenanceRequest::with(['proposer', 'customer', 'products', 'warranty'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format dữ liệu trả về
            $data = $maintenanceRequests->map(function ($item) {
                return [
                    'id' => $item->id,
                    'request_code' => $item->request_code,
                    'request_date' => $item->request_date ? $item->request_date->format('Y-m-d') : null,
                    'maintenance_date' => $item->maintenance_date ? $item->maintenance_date->format('Y-m-d') : null,
                    'maintenance_type' => $item->maintenance_type,
                    'status' => $item->status,
                    'project_type' => $item->project_type,
                    'project_id' => $item->project_id,
                    'project_name' => $item->project_name,
                    'customer_id' => $item->customer_id,
                    'customer_name' => $item->customer_name,
                    'customer_phone' => $item->customer_phone,
                    'customer_email' => $item->customer_email,
                    'customer_address' => $item->customer_address,
                    'notes' => $item->notes,
                    'maintenance_reason' => $item->maintenance_reason,
                    'reject_reason' => $item->reject_reason,
                    'proposer' => $item->proposer ? [
                        'id' => $item->proposer->id,
                        'name' => $item->proposer->name,
                        'username' => $item->proposer->username,
                        'email' => $item->proposer->email,
                    ] : null,
                    'customer' => $item->customer ? [
                        'id' => $item->customer->id,
                        'name' => $item->customer->name,
                        'company_name' => $item->customer->company_name,
                        'phone' => $item->customer->phone,
                        'email' => $item->customer->email,
                    ] : null,
                    'products_count' => $item->products->count(),
                    'products' => $item->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'product_id' => $product->product_id,
                            'product_code' => $product->product_code,
                            'product_name' => $product->product_name,
                            'serial_number' => $product->serial_number,
                            'type' => $product->type,
                            'quantity' => $product->quantity,
                        ];
                    }),
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
            Log::error('API get all maintenance requests error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách phiếu bảo trì dự án: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy chi tiết một phiếu bảo trì dự án theo ID
     */
    public function apiShow($id)
    {
        try {
            $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products', 'warranty', 'project', 'rental'])
                ->findOrFail($id);

            // Format dữ liệu trả về
            $data = [
                'id' => $maintenanceRequest->id,
                'request_code' => $maintenanceRequest->request_code,
                'request_date' => $maintenanceRequest->request_date ? $maintenanceRequest->request_date->format('Y-m-d') : null,
                'maintenance_date' => $maintenanceRequest->maintenance_date ? $maintenanceRequest->maintenance_date->format('Y-m-d') : null,
                'maintenance_type' => $maintenanceRequest->maintenance_type,
                'status' => $maintenanceRequest->status,
                'project_type' => $maintenanceRequest->project_type,
                'project_id' => $maintenanceRequest->project_id,
                'project_code' => null,
                'project_name' => $maintenanceRequest->project_name,
                'customer_id' => $maintenanceRequest->customer_id,
                'customer_name' => $maintenanceRequest->customer_name,
                'customer_phone' => $maintenanceRequest->customer_phone,
                'customer_email' => $maintenanceRequest->customer_email,
                'customer_address' => $maintenanceRequest->customer_address,
                'notes' => $maintenanceRequest->notes,
                'maintenance_reason' => $maintenanceRequest->maintenance_reason,
                'reject_reason' => $maintenanceRequest->reject_reason,
                'proposer' => $maintenanceRequest->proposer ? [
                    'id' => $maintenanceRequest->proposer->id,
                    'name' => $maintenanceRequest->proposer->name,
                    'username' => $maintenanceRequest->proposer->username,
                    'email' => $maintenanceRequest->proposer->email,
                ] : null,
                'customer' => $maintenanceRequest->customer ? [
                    'id' => $maintenanceRequest->customer->id,
                    'name' => $maintenanceRequest->customer->name,
                    'company_name' => $maintenanceRequest->customer->company_name,
                    'phone' => $maintenanceRequest->customer->phone,
                    'email' => $maintenanceRequest->customer->email,
                ] : null,
                'products_count' => $maintenanceRequest->products->count(),
                'products' => $maintenanceRequest->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_id' => $product->product_id,
                        'product_code' => $product->product_code,
                        'product_name' => $product->product_name,
                        'serial_number' => $product->serial_number,
                        'type' => $product->type,
                        'quantity' => $product->quantity,
                    ];
                }),
                'created_at' => $maintenanceRequest->created_at ? $maintenanceRequest->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $maintenanceRequest->updated_at ? $maintenanceRequest->updated_at->format('Y-m-d H:i:s') : null,
            ];

            // Lấy project_code từ project hoặc rental
            if ($maintenanceRequest->project_type === 'project' && $maintenanceRequest->project) {
                $data['project_code'] = $maintenanceRequest->project->project_code;
            } elseif ($maintenanceRequest->project_type === 'rental' && $maintenanceRequest->rental) {
                $data['project_code'] = $maintenanceRequest->rental->rental_code;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu bảo trì với ID: ' . $id
            ], 404);
        } catch (\Exception $e) {
            Log::error('API get maintenance request by ID error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin phiếu bảo trì: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy danh sách phiếu bảo trì dự án (MaintenanceRequest) - có lọc và phân trang
     */
    public function apiIndexProject(Request $request)
    {
        try {
            $query = MaintenanceRequest::with(['proposer', 'customer', 'products', 'warranty']);

            // Tìm kiếm
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('request_code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('project_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customer_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('proposer', function ($proposerQuery) use ($searchTerm) {
                            $proposerQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('username', 'LIKE', "%{$searchTerm}%");
                        })
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

            // Lọc theo loại bảo trì
            if ($request->filled('maintenance_type')) {
                $query->where('maintenance_type', $request->maintenance_type);
            }

            // Lọc theo loại dự án
            if ($request->filled('project_type')) {
                $query->where('project_type', $request->project_type);
            }

            // Lọc theo project_id
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // Lọc theo customer_id
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Lọc theo proposer_id
            if ($request->filled('proposer_id')) {
                $query->where('proposer_id', $request->proposer_id);
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

            // Lọc theo khoảng thời gian maintenance_date
            if ($request->filled('maintenance_date_from')) {
                $dateFrom = DateHelper::convertToDatabaseFormat($request->maintenance_date_from);
                $query->whereDate('maintenance_date', '>=', $dateFrom);
            }
            if ($request->filled('maintenance_date_to')) {
                $dateTo = DateHelper::convertToDatabaseFormat($request->maintenance_date_to);
                $query->whereDate('maintenance_date', '<=', $dateTo);
            }

            // Sắp xếp
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSortFields = ['id', 'request_code', 'request_date', 'maintenance_date', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Phân trang
            $perPage = $request->get('per_page', 15);
            $perPage = min(max(1, (int)$perPage), 100); // Giới hạn từ 1 đến 100

            $maintenanceRequests = $query->paginate($perPage);

            // Tối ưu: Load tất cả project và rental cần thiết trước
            $projectIds = $maintenanceRequests->where('project_type', 'project')->pluck('project_id')->unique()->filter();
            $rentalIds = $maintenanceRequests->where('project_type', 'rental')->pluck('project_id')->unique()->filter();
            
            $projects = \App\Models\Project::whereIn('id', $projectIds)->pluck('project_code', 'id');
            $rentals = \App\Models\Rental::whereIn('id', $rentalIds)->pluck('rental_code', 'id');

            // Format dữ liệu trả về - format đơn giản
            $data = $maintenanceRequests->map(function ($item) use ($projects, $rentals) {
                // Lấy project_code từ project/rental
                $projectCode = '';
                if ($item->project_type === 'project' && $item->project_id && isset($projects[$item->project_id])) {
                    $projectCode = $projects[$item->project_id];
                } elseif ($item->project_type === 'rental' && $item->project_id && isset($rentals[$item->project_id])) {
                    $projectCode = $rentals[$item->project_id];
                }

                // Map status: approved -> accepted, các status khác giữ nguyên
                $status = $item->status;
                if ($status === 'approved') {
                    $status = 'accepted';
                }

                return [
                    'request_code' => $item->request_code,
                    'request_date' => $item->request_date ? $item->request_date->format('Y-m-d') : null,
                    'project_type' => $item->project_type,
                    'project_code' => $projectCode,
                    'project_name' => $item->project_name,
                    'status' => $status,
                ];
            });

            return response()->json([
                'data' => $data,
                'total' => $maintenanceRequests->total()
            ]);
        } catch (\Exception $e) {
            Log::error('API list maintenance requests error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách phiếu yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Lấy danh sách phiếu yêu cầu sửa chữa & bảo trì (tất cả - giữ lại để tương thích)
     */
    public function apiIndex(Request $request)
    {
        // Gọi lại apiIndexProject để giữ tương thích
        return $this->apiIndexProject($request);
    }

    /**
     * Hiển thị form tạo mới phiếu bảo trì dự án
     */
    public function create()
    {
        // Lấy danh sách nhân viên
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        
        // Lấy danh sách khách hàng
        $customers = Customer::orderBy('company_name')->get();
        
        // Lấy danh sách thành phẩm
        $products = Product::where('status', 'active')->orderBy('name')->get();
        
        // Lấy danh sách dự án (tất cả, bao gồm cả đã quá hạn)
        $projects = \App\Models\Project::with(['customer'])
            ->select('*', DB::raw('DATE_ADD(end_date, INTERVAL warranty_period MONTH) as warranty_end_date'))
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Lấy danh sách phiếu cho thuê (tất cả, bao gồm cả đã quá hạn)
        $rentals = \App\Models\Rental::with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('requests.maintenance.create', compact('employees', 'customers', 'products', 'projects', 'rentals'));
    }

    /**
     * Lưu phiếu bảo trì dự án mới vào database
     */
    public function store(Request $request)
    {
        try {
            // Chuẩn hoá định dạng ngày tháng trước khi validate
            $request->merge([
                'request_date' => DateHelper::convertToDatabaseFormat($request->request_date),
                'maintenance_date' => DateHelper::convertToDatabaseFormat($request->maintenance_date),
            ]);
            // Kiểm tra nếu là sao chép từ phiếu đã tồn tại
            if ($request->has('copy_from')) {
                $sourceRequest = MaintenanceRequest::with(['products'])->findOrFail($request->copy_from);
                
                try {
                    DB::beginTransaction();
                    
                    // Tạo phiếu bảo trì mới từ phiếu nguồn
                    $newRequest = $sourceRequest->replicate();
                    $newRequest->request_code = MaintenanceRequest::generateRequestCode();
                    $newRequest->request_date = now();
                    $newRequest->status = 'pending';
                    $newRequest->save();
                    
                    // Sao chép các thành phẩm từ phiếu nguồn
                    foreach ($sourceRequest->products as $product) {
                        $newProduct = $product->replicate();
                        $newProduct->maintenance_request_id = $newRequest->id;
                        $newProduct->save();
                    }
                    
                    DB::commit();
                    
                    // Ghi nhật ký tạo phiếu bảo trì từ sao chép
                    if (Auth::check() && Employee::find(Auth::id())) {
                        \App\Models\UserLog::logActivity(
                            Auth::id(),
                            'create',
                            'maintenance_requests',
                            'Tạo phiếu bảo trì dự án (sao chép): ' . $newRequest->request_code,
                            null,
                            $newRequest->toArray()
                        );
                    }
                    
                    return redirect()->route('requests.maintenance.show', $newRequest->id)
                        ->with('success', 'Phiếu bảo trì đã được sao chép thành công.');
                        
                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    // Log lỗi chi tiết
                    Log::error('Lỗi khi sao chép phiếu bảo trì: ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                    
                    return redirect()->back()
                        ->with('error', 'Có lỗi xảy ra khi sao chép phiếu: ' . $e->getMessage())
                        ->withInput();
                }
            }
            
            // Validation cơ bản - cập nhật validation rule cho products nếu sử dụng thiết bị từ bảo hành
            $validationRules = [
                'request_date' => 'required|date_format:Y-m-d',
                'proposer_id' => 'required|exists:employees,id',
                'project_name' => 'nullable|string|max:255', // Bỏ required vì tự động điền
                'customer_id' => 'required|exists:customers,id',
                'maintenance_date' => 'required|date_format:Y-m-d',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'customer_address' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'selected_devices' => 'required|string', // Thêm validation cho selected_devices
            ];
            
            // Validation cho loại dự án và dự án được chọn
            $validationRules['project_type'] = 'required|in:project,rental';
            $validationRules['project_id'] = 'required|integer';
            
            // Validation khác nhau phụ thuộc vào loại dự án
            if ($request->project_type === 'project') {
                $validationRules['project_id'] = 'required|integer|exists:projects,id';
            } else {
                $validationRules['project_id'] = 'required|integer|exists:rentals,id';
            }
            
            $validator = Validator::make($request->all(), $validationRules);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            DB::beginTransaction();
            
            // Lấy thông tin dự án/phiếu cho thuê và điền thông tin khách hàng nếu thiếu từ form
            $projectName = '';
            $customerId = null;
            $customerName = $request->customer_name;
            $customerPhone = $request->customer_phone;
            $customerEmail = $request->customer_email;
            $customerAddress = $request->customer_address; // có thể rỗng

            if ($request->project_type === 'project') {
                $project = \App\Models\Project::with('customer')->findOrFail($request->project_id);
                $projectName = $project->project_name;
                $customerId = $project->customer_id;
                if ($project->customer) {
                    $customerName = $customerName ?: ($project->customer->company_name ?: $project->customer->name);
                    $customerPhone = $customerPhone ?: $project->customer->phone;
                    $customerEmail = $customerEmail ?: $project->customer->email;
                    $customerAddress = $customerAddress ?: ($project->customer->address ?: '');
                }
            } else {
                $rental = \App\Models\Rental::with('customer')->findOrFail($request->project_id);
                $projectName = $rental->rental_name;
                $customerId = $rental->customer_id;
                if ($rental->customer) {
                    $customerName = $customerName ?: ($rental->customer->company_name ?: $rental->customer->name);
                    $customerPhone = $customerPhone ?: $rental->customer->phone;
                    $customerEmail = $customerEmail ?: $rental->customer->email;
                    $customerAddress = $customerAddress ?: ($rental->customer->address ?: '');
                }
            }
            
            // Tạo phiếu bảo trì mới
            $maintenanceRequest = MaintenanceRequest::create([
                'request_code' => MaintenanceRequest::generateRequestCode(),
                'request_date' => $request->request_date, // Sử dụng request_date
                'proposer_id' => $request->proposer_id,
                'project_type' => $request->project_type, // Thêm project_type
                'project_id' => $request->project_id, // Thêm project_id
                'project_name' => $projectName,
                'customer_id' => $customerId,
                'warranty_id' => null, // Không còn sử dụng warranty_id
                'project_address' => $request->customer_address ?? '',
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type ?? 'maintenance', // Mặc định là maintenance
                'maintenance_reason' => $request->notes ?? '', // Sử dụng notes làm maintenance_reason
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail,
                'customer_address' => $customerAddress ?? '',
                'notes' => $request->notes,
                'status' => 'pending',
            ]);
            
            // Xử lý thiết bị đã chọn
            $selectedDevices = json_decode($request->selected_devices, true);
            if ($selectedDevices && is_array($selectedDevices)) {
                foreach ($selectedDevices as $deviceId) {
                    // Tách deviceId để lấy item_id và index
                    $parts = explode('_', $deviceId);
                    $itemId = $parts[0];
                    $index = isset($parts[1]) ? $parts[1] : 0;
                    
                    // Lấy thông tin item từ dispatch_items
                    $dispatchItem = \App\Models\DispatchItem::with(['product', 'good'])->find($itemId);
                    if ($dispatchItem) {
                        $deviceCode = '';
                        $deviceName = '';
                        $deviceType = '';
                        
                        if ($dispatchItem->item_type === 'product' && $dispatchItem->product) {
                            $deviceCode = $dispatchItem->product->code;
                            $deviceName = $dispatchItem->product->name;
                            $deviceType = 'Thành phẩm';
                        } elseif ($dispatchItem->item_type === 'good' && $dispatchItem->good) {
                            $deviceCode = $dispatchItem->good->code;
                            $deviceName = $dispatchItem->good->name;
                            $deviceType = 'Hàng hoá';
                        }
                        
                        // Lấy serial number
                        $serialNumber = 'N/A';
                        if (!empty($dispatchItem->serial_numbers) && is_array($dispatchItem->serial_numbers)) {
                            $serialNumber = $dispatchItem->serial_numbers[$index] ?? 'N/A';
                        }
                        
                        // Tạo MaintenanceRequestProduct
                        MaintenanceRequestProduct::create([
                            'maintenance_request_id' => $maintenanceRequest->id,
                            'product_id' => $dispatchItem->item_id, // Thêm product_id
                            'product_code' => $deviceCode,
                            'product_name' => $deviceName,
                            'serial_number' => $serialNumber,
                            'type' => $deviceType,
                            'quantity' => 1,
                        ]);
                    }
                }
            }
            
            // Gửi thông báo cho người đề xuất (kỹ thuật viên)
            Notification::createNotification(
                'Phiếu bảo trì dự án mới',
                'Bạn đã tạo phiếu bảo trì dự án ' . $maintenanceRequest->project_name,
                'info',
                $request->proposer_id,
                'maintenance_request',
                $maintenanceRequest->id,
                route('requests.maintenance.show', $maintenanceRequest->id)
            );

            DB::commit();
            
            // Ghi nhật ký tạo phiếu bảo trì mới
            if (Auth::check() && Employee::find(Auth::id())) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'maintenance_requests',
                    'Tạo phiếu bảo trì dự án: ' . $maintenanceRequest->request_code,
                    null,
                    $maintenanceRequest->toArray()
                );
            }
            
            return redirect()->route('requests.maintenance.show', $maintenanceRequest->id)
                ->with('success', 'Phiếu bảo trì đã được tạo thành công.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log lỗi chi tiết
            Log::error('Lỗi khi tạo phiếu bảo trì: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo phiếu: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết phiếu bảo trì dự án
     */
    public function show($id)
    {
        $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products', 'warranty'])
            ->findOrFail($id);
        
        // Ghi nhật ký xem chi tiết phiếu bảo trì
        if (Auth::check() && Employee::find(Auth::id())) {
            \App\Models\UserLog::logActivity(
                Auth::id(),
                'view',
                'maintenance_requests',
                'Xem chi tiết phiếu bảo trì dự án: ' . $maintenanceRequest->request_code,
                null,
                ['id' => $maintenanceRequest->id, 'code' => $maintenanceRequest->request_code]
            );
        }
            
        return view('requests.maintenance.show', compact('maintenanceRequest'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu bảo trì dự án
     */
    public function edit($id)
    {
        $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products'])
            ->findOrFail($id);
        
        // Lấy danh sách projects và rentals giống như trong create
        $projects = \App\Models\Project::with('customer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($project) {
                $warrantyEndDate = \Carbon\Carbon::parse($project->start_date)->addMonths($project->warranty_period);
                return [
                    'id' => $project->id,
                    'project_code' => $project->project_code,
                    'project_name' => $project->project_name,
                    'customer' => $project->customer,
                    'warranty_end_date' => $warrantyEndDate->format('Y-m-d'),
                ];
            });
        
        $rentals = \App\Models\Rental::with('customer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rental) {
                return [
                    'id' => $rental->id,
                    'rental_code' => $rental->rental_code,
                    'rental_name' => $rental->rental_name,
                    'customer' => $rental->customer,
                    'due_date' => $rental->due_date ? \Carbon\Carbon::parse($rental->due_date)->format('Y-m-d') : null,
                ];
            });
        
        // Tự động gán chỉ khi cả type và id đều thiếu; nếu có tên dự án nhưng thiếu id, cố gắng map theo tên
        if (!$maintenanceRequest->project_type && !$maintenanceRequest->project_id) {
            $maintenanceRequest->project_type = count($projects) > 0 ? 'project' : (count($rentals) > 0 ? 'rental' : null);
            if ($maintenanceRequest->project_type === 'project' && count($projects) > 0) {
                $maintenanceRequest->project_id = $projects[0]['id'];
            } elseif ($maintenanceRequest->project_type === 'rental' && count($rentals) > 0) {
                $maintenanceRequest->project_id = $rentals[0]['id'];
            }
        }
        
        return view('requests.maintenance.edit', compact('maintenanceRequest', 'projects', 'rentals'));
    }

    /**
     * Cập nhật phiếu bảo trì dự án
     */
    public function update(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        
        // Lưu dữ liệu cũ trước khi cập nhật - load products để có thông tin đầy đủ
        $maintenanceRequest->load('products');
        $oldData = $maintenanceRequest->toArray();
        
        // Log để kiểm tra oldData
        Log::info('=== OLD DATA DEBUG ===');
        Log::info('Old data keys: ' . json_encode(array_keys($oldData)));
        Log::info('Old data has products: ' . (isset($oldData['products']) ? 'YES' : 'NO'));
        if (isset($oldData['products'])) {
            Log::info('Old products count: ' . count($oldData['products']));
            Log::info('Old products: ' . json_encode($oldData['products']));
        }
        
        // Chỉ cho phép cập nhật nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể chỉnh sửa phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        // Chuẩn hoá định dạng ngày tháng trước khi validate
        $request->merge([
            'request_date' => DateHelper::convertToDatabaseFormat($request->request_date),
            'maintenance_date' => DateHelper::convertToDatabaseFormat($request->maintenance_date),
        ]);

        // Validation cơ bản - cho phép chỉnh sửa toàn bộ
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date_format:Y-m-d',
            'project_type' => 'nullable|in:project,rental', // Bỏ required vì phiếu cũ có thể không có
            'project_id' => 'nullable|integer', // Bỏ required vì phiếu cũ có thể không có
            'project_name' => 'nullable|string|max:255',
            'maintenance_date' => 'required|date_format:Y-m-d',
            'maintenance_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
            'selected_devices' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Cập nhật phiếu bảo trì - chỉ cập nhật các trường có dữ liệu
            $updateData = [
                'request_date' => $request->request_date,
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type,
                'notes' => $request->notes,
            ];
            
            // Chỉ cập nhật project_type và project_id nếu có dữ liệu
            if ($request->project_type) {
                $updateData['project_type'] = $request->project_type;
            }
            if ($request->project_id) {
                $updateData['project_id'] = $request->project_id;
            }
            if ($request->project_name) {
                $updateData['project_name'] = $request->project_name;
            }
            
            $maintenanceRequest->update($updateData);
            
            // Log trước khi xóa products
            Log::info('=== UPDATE MAINTENANCE REQUEST DEBUG ===');
            Log::info('Maintenance Request ID: ' . $maintenanceRequest->id);
            Log::info('Selected devices raw: ' . $request->selected_devices);
            Log::info('Current products count before delete: ' . $maintenanceRequest->products()->count());
            
            // Xóa thiết bị cũ và tạo thiết bị mới
            $maintenanceRequest->products()->delete();
            
            // Xử lý thiết bị đã chọn
            $selectedDevices = json_decode($request->selected_devices, true);
            Log::info('Selected devices decoded: ' . json_encode($selectedDevices));
            Log::info('Selected devices count: ' . (is_array($selectedDevices) ? count($selectedDevices) : 0));
            
            if ($selectedDevices && is_array($selectedDevices)) {
                foreach ($selectedDevices as $deviceId) {
                    Log::info('Processing device ID: ' . $deviceId);
                    
                    // Kiểm tra xem deviceId có chứa '_' không (format của DispatchItem)
                    if (strpos($deviceId, '_') !== false) {
                        // Format: itemId_index (từ API devices)
                        $parts = explode('_', $deviceId);
                        $itemId = $parts[0];
                        $index = isset($parts[1]) ? $parts[1] : 0;
                        
                        Log::info('DispatchItem format - Item ID: ' . $itemId . ', Index: ' . $index);
                        
                        // Lấy thông tin item từ dispatch_items
                        $dispatchItem = \App\Models\DispatchItem::with(['product', 'good'])->find($itemId);
                        if ($dispatchItem) {
                            $deviceCode = '';
                            $deviceName = '';
                            $deviceType = '';
                            
                            if ($dispatchItem->item_type === 'product' && $dispatchItem->product) {
                                $deviceCode = $dispatchItem->product->code;
                                $deviceName = $dispatchItem->product->name;
                                $deviceType = 'Thành phẩm';
                            } elseif ($dispatchItem->item_type === 'good' && $dispatchItem->good) {
                                $deviceCode = $dispatchItem->good->code;
                                $deviceName = $dispatchItem->good->name;
                                $deviceType = 'Hàng hoá';
                            }
                            
                            // Lấy serial number
                            $serialNumber = 'N/A';
                            if (!empty($dispatchItem->serial_numbers) && is_array($dispatchItem->serial_numbers)) {
                                $serialNumber = $dispatchItem->serial_numbers[$index] ?? 'N/A';
                            }
                            
                            Log::info('Creating MaintenanceRequestProduct - Code: ' . $deviceCode . ', Name: ' . $deviceName);
                            
                            // Tạo MaintenanceRequestProduct
                            MaintenanceRequestProduct::create([
                                'maintenance_request_id' => $maintenanceRequest->id,
                                'product_id' => $dispatchItem->item_id,
                                'product_code' => $deviceCode,
                                'product_name' => $deviceName,
                                'serial_number' => $serialNumber,
                                'type' => $deviceType,
                                'quantity' => 1,
                            ]);
                        } else {
                            Log::warning('DispatchItem not found for ID: ' . $itemId);
                        }
                    } else {
                        // Format: MaintenanceRequestProduct ID (từ existing products)
                        Log::info('MaintenanceRequestProduct format - ID: ' . $deviceId);
                        
                        // Lấy thông tin từ oldData thay vì tìm trong database
                        $oldProducts = collect($oldData['products'] ?? []);
                        $existingProduct = $oldProducts->firstWhere('id', $deviceId);
                        
                        if ($existingProduct) {
                            Log::info('Creating MaintenanceRequestProduct from existing - Code: ' . $existingProduct['product_code'] . ', Name: ' . $existingProduct['product_name']);
                            
                            // Tạo lại product với thông tin hiện có
                            MaintenanceRequestProduct::create([
                                'maintenance_request_id' => $maintenanceRequest->id,
                                'product_id' => $existingProduct['product_id'],
                                'product_code' => $existingProduct['product_code'],
                                'product_name' => $existingProduct['product_name'],
                                'serial_number' => $existingProduct['serial_number'],
                                'type' => $existingProduct['type'],
                                'quantity' => $existingProduct['quantity'],
                            ]);
                        } else {
                            Log::warning('Existing MaintenanceRequestProduct not found for ID: ' . $deviceId);
                        }
                    }
                }
            } else {
                Log::warning('No selected devices or invalid format');
            }
            
            // Log sau khi tạo products
            Log::info('Products count after creation: ' . $maintenanceRequest->products()->count());
            
            DB::commit();
            
            // Ghi nhật ký cập nhật phiếu bảo trì
            if (Auth::check() && Employee::find(Auth::id())) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'maintenance_requests',
                    'Cập nhật phiếu bảo trì dự án: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
            
            return redirect()->route('requests.maintenance.show', $maintenanceRequest->id)
                ->with('success', 'Phiếu bảo trì đã được cập nhật thành công.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log lỗi chi tiết
            Log::error('Lỗi khi cập nhật phiếu bảo trì: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật phiếu: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Xóa phiếu bảo trì dự án
     */
    public function destroy($id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $requestCode = $maintenanceRequest->request_code;
        $requestData = $maintenanceRequest->toArray();
        
        // Cho phép xóa nếu phiếu ở trạng thái chờ duyệt hoặc đã từ chối
        if (!in_array($maintenanceRequest->status, ['pending', 'rejected'])) {
            return redirect()->back()
                ->with('error', 'Chỉ có thể xóa phiếu bảo trì ở trạng thái Chờ duyệt hoặc Đã từ chối.');
        }
        
        try {
            DB::beginTransaction();
            
            // Xóa phiếu bảo trì và các dữ liệu liên quan
            $maintenanceRequest->delete();
            
            DB::commit();
            
            // Ghi nhật ký xóa phiếu bảo trì
            if (Auth::check() && Employee::find(Auth::id())) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'maintenance_requests',
                    'Xóa phiếu bảo trì dự án: ' . $requestCode,
                    $requestData,
                    null
                );
            }
            
            return redirect()->route('requests.index')
                ->with('success', 'Phiếu bảo trì đã được xóa thành công.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log lỗi chi tiết
            Log::error('Lỗi khi xóa phiếu bảo trì: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xóa phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Duyệt phiếu bảo trì
     */
    public function approve(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $oldData = $maintenanceRequest->toArray();
        
        // Chỉ cho phép duyệt nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể duyệt phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        try {
            DB::beginTransaction();
            
            // Cập nhật trạng thái phiếu bảo trì
            $maintenanceRequest->update([
                'status' => 'approved',
            ]);
            
            // Tạo phiếu sửa chữa (repair) từ phiếu bảo trì
            $this->createRepairFromMaintenanceRequest($maintenanceRequest);
            
            DB::commit();
            
            // Ghi nhật ký duyệt phiếu bảo trì
            if (Auth::check() && Employee::find(Auth::id())) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'maintenance_requests',
                    'Duyệt phiếu bảo trì dự án: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
            
            return redirect()->route('requests.maintenance.show', $maintenanceRequest->id)
                ->with('success', 'Phiếu bảo trì đã được duyệt thành công và đã tạo phiếu sửa chữa tương ứng.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log lỗi chi tiết
            Log::error('Lỗi khi duyệt phiếu bảo trì: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi duyệt phiếu: ' . $e->getMessage());
        }
    }
    
    /**
     * Tạo phiếu sửa chữa từ phiếu bảo trì
     */
    private function createRepairFromMaintenanceRequest($maintenanceRequest)
    {
        // Lấy thông tin bảo hành nếu có
        $warranty = null;
        $warranty_code = null;
        $warranty_id = null;
        
        if ($maintenanceRequest->warranty_id) {
            $warranty = $maintenanceRequest->warranty;
            if ($warranty) {
                $warranty_code = $warranty->warranty_code;
                $warranty_id = $warranty->id;
            }
        }

        // Fallback 1: nếu đã chọn dự án/cho thuê, ưu tiên lấy bảo hành cấp dự án tương ứng
        if (!$warranty && $maintenanceRequest->project_id) {
            if ($maintenanceRequest->project_type === 'project') {
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($q) use ($maintenanceRequest) {
                        $q->where('project_id', $maintenanceRequest->project_id)
                          ->where('dispatch_type', '!=', 'rental');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
            } elseif ($maintenanceRequest->project_type === 'rental') {
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($q) use ($maintenanceRequest) {
                        $q->where('project_id', $maintenanceRequest->project_id)
                          ->where('dispatch_type', 'rental');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            if ($warranty) {
                $warranty_code = $warranty->warranty_code;
                $warranty_id = $warranty->id;
            }
        }

        // Fallback 2: nếu chưa có project_id nhưng có project_name, map theo tên dự án
        if (!$warranty && empty($maintenanceRequest->project_id) && !empty($maintenanceRequest->project_name)) {
            $project = \App\Models\Project::where('project_name', $maintenanceRequest->project_name)->first();
            if ($project) {
                $warranty = Warranty::where('item_type', 'project')
                    ->whereHas('dispatch', function ($q) use ($project) {
                        $q->where('project_id', $project->id)
                          ->where('dispatch_type', '!=', 'rental');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($warranty) {
                    $warranty_code = $warranty->warranty_code;
                    $warranty_id = $warranty->id;
                }
            }
        }

        // Fallback 3: nếu chưa có warranty trên MR và không tìm theo dự án được, cố gắng suy ra theo serial trong danh sách sản phẩm của MR
        if (!$warranty && method_exists($maintenanceRequest, 'products') && $maintenanceRequest->products && $maintenanceRequest->products->isNotEmpty()) {
            $serials = $maintenanceRequest->products
                ->pluck('serial_number')
                ->filter(function ($s) { return !empty($s) && trim($s) !== ''; })
                ->unique()
                ->values();

            foreach ($serials as $sn) {
                // Tìm trong bảng warranties: khớp trực tiếp serial_number hoặc trong dispatch items
                $candidate = \App\Models\Warranty::where('status', 'active')
                    ->where(function ($q) use ($sn) {
                        $normalized = preg_replace('/[\s-]+/', '', strtoupper($sn));
                        $q->whereRaw('UPPER(REPLACE(REPLACE(IFNULL(serial_number, ""), " ", ""), "-", "")) = ?', [$normalized])
                          ->orWhereHas('dispatch.items', function ($qi) use ($sn, $normalized) {
                              $qi->whereIn('item_type', ['product','good'])
                                 ->where(function ($qj) use ($sn, $normalized) {
                                     $qj->whereJsonContains('serial_numbers', $sn)
                                        ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$sn])
                                        ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$normalized]);
                                 });
                          });
                    })
                    ->first();

                if ($candidate) {
                    $warranty = $candidate;
                    $warranty_code = $candidate->warranty_code;
                    $warranty_id = $candidate->id;
                    break;
                }
            }
        }
        
        // Xác định loại sửa chữa dựa trên loại bảo trì
        $repairType = $maintenanceRequest->maintenance_type; // Map trực tiếp với loại bảo trì
        
        // Tìm warehouse mặc định (sử dụng warehouse_id = 1 nếu không tìm thấy)
        $defaultWarehouseId = 1;
        try {
            // Lấy warehouse đầu tiên trong hệ thống nếu có
            $warehouse = Warehouse::where('status', 'active')->first();
            if ($warehouse) {
                $defaultWarehouseId = $warehouse->id;
            }
        } catch (\Exception $e) {
            Log::warning('Không thể tìm thấy warehouse, sử dụng ID mặc định: ' . $e->getMessage());
        }
        
        // Tạo phiếu sửa chữa
        $repair = Repair::create([
            'repair_code' => Repair::generateRepairCode(),
            'warranty_code' => $warranty_code,
            'warranty_id' => $warranty_id,
            'repair_type' => $repairType,
            'repair_date' => now(),
            'technician_id' => $maintenanceRequest->proposer_id,
            'warehouse_id' => $defaultWarehouseId,
            'repair_description' => $maintenanceRequest->maintenance_reason,
            'repair_notes' => 'Tạo tự động từ phiếu bảo trì ' . $maintenanceRequest->request_code,
            'repair_photos' => [],
            'status' => 'in_progress',
            'created_by' => Auth::id() ?? 1,
            'maintenance_request_id' => $maintenanceRequest->id,
        ]);
        
        // Gửi thông báo cho kỹ thuật viên về phiếu sửa chữa mới
        Notification::createNotification(
            'Phiếu sửa chữa mới được tạo',
            'Một phiếu sửa chữa mới đã được tạo từ phiếu bảo trì ' . $maintenanceRequest->request_code,
            'info',
            $maintenanceRequest->proposer_id,
            'repair',
            $repair->id,
            '/repairs/' . $repair->id
        );
        
        // Lấy danh sách thiết bị từ warranty để thêm vào repair items
        $dispatchItems = collect([]);
        if ($warranty && $warranty->dispatch && $warranty->dispatch->items) {
            $dispatchItems = $warranty->dispatch->items->where('item_type', 'product');
        }
        
        // Nếu không có dispatch items, sử dụng danh sách products từ phiếu bảo trì
        if ($dispatchItems->isEmpty() && $maintenanceRequest->products->isNotEmpty()) {
            foreach ($maintenanceRequest->products as $product) {
                RepairItem::create([
                    'repair_id' => $repair->id,
                    'device_code' => $product->product_code,
                    'device_name' => $product->product_name,
                    'device_serial' => $product->serial_number ?? '', // Sửa: truyền serial_number
                    'device_quantity' => $product->quantity,
                    'device_status' => 'selected',
                    'device_notes' => '',
                    'device_images' => [],
                    'device_parts' => [],
                    'device_type' => 'product',
                ]);
            }
        } else {
            // Thêm các thiết bị từ dispatch items vào repair items
            foreach ($dispatchItems as $item) {
                if ($item->product) {
                    // Lấy serial number từ serial_numbers array
                    $serialNumber = '';
                    if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                        $serialNumber = $item->serial_numbers[0] ?? '';
                    }
                    
                    RepairItem::create([
                        'repair_id' => $repair->id,
                        'device_code' => $item->product->code,
                        'device_name' => $item->product->name,
                        'device_serial' => $serialNumber,
                        'device_quantity' => 1,
                        'device_status' => 'selected',
                        'device_notes' => '',
                        'device_images' => [],
                        'device_parts' => [],
                        'device_type' => 'product',
                    ]);
                }
            }
        }
        
        Log::info('Đã tạo phiếu sửa chữa mới từ phiếu bảo trì', [
            'maintenance_request_id' => $maintenanceRequest->id,
            'repair_id' => $repair->id,
            'repair_code' => $repair->repair_code
        ]);
        
        return $repair;
    }

    /**
     * Từ chối phiếu bảo trì
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $oldData = $maintenanceRequest->toArray();
        
        // Chỉ cho phép từ chối nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể từ chối phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        try {
            $maintenanceRequest->update([
                'status' => 'rejected',
                'reject_reason' => $request->rejection_reason,
            ]);

            // Ghi nhật ký từ chối phiếu bảo trì
            if (Auth::check() && Employee::find(Auth::id())) {
                \App\Models\UserLog::logActivity(
                    Auth::id(),
                    'reject',
                    'maintenance_requests',
                    'Từ chối phiếu bảo trì dự án: ' . $maintenanceRequest->request_code,
                    $oldData,
                    $maintenanceRequest->toArray()
                );
            }
            
            return redirect()->route('requests.maintenance.show', $maintenanceRequest->id)
                ->with('success', 'Phiếu bảo trì đã bị từ chối.');
                
        } catch (\Exception $e) {
            // Log lỗi chi tiết
            Log::error('Lỗi khi từ chối phiếu bảo trì: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi từ chối phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật trạng thái phiếu bảo trì
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_progress,completed,canceled',
            'status_note' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        
        // Chỉ cho phép cập nhật trạng thái nếu phiếu đã được duyệt hoặc đang thực hiện
        if (!in_array($maintenanceRequest->status, ['approved', 'in_progress'])) {
            return redirect()->back()
                ->with('error', 'Chỉ có thể cập nhật trạng thái cho phiếu bảo trì đã được duyệt hoặc đang thực hiện.');
        }
        
        try {
            $maintenanceRequest->update([
                'status' => $request->status,
                'notes' => $request->status_note ? ($maintenanceRequest->notes . "\n\n" . $request->status_note) : $maintenanceRequest->notes,
            ]);
            
            $statusText = $this->getStatusText($request->status);
            
            return redirect()->route('requests.maintenance.show', $maintenanceRequest->id)
                ->with('success', "Phiếu bảo trì đã được cập nhật thành trạng thái: {$statusText}");
                
        } catch (\Exception $e) {
            // Log lỗi chi tiết
            Log::error('Lỗi khi cập nhật trạng thái phiếu bảo trì: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái phiếu: ' . $e->getMessage());
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
     * Xem trước phiếu bảo trì
     */
    public function preview($id)
    {
        $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products'])
            ->findOrFail($id);
            
        return view('requests.maintenance.preview', compact('maintenanceRequest'));
    }

    /**
     * Lấy thiết bị từ project hoặc rental
     */
    public function getDevices(Request $request)
    {
        Log::info('=== GET DEVICES (WEB) CALLED ===');
        Log::info('Request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'project_type' => 'required|in:project,rental',
            'project_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['error' => 'Dữ liệu không hợp lệ'], 400);
        }

        try {
            $devices = $this->collectDevicesWithSerials($request->project_type, (int) $request->project_id);
            Log::info('Final devices array (web):', ['count' => count($devices)]);
            return response()->json(['devices' => $devices]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thiết bị: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi lấy thiết bị: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy thiết bị (format đơn giản cho bên ngoài)
     */
    public function getDevicesApi(Request $request)
    {
        Log::info('=== GET DEVICES (API) CALLED ===');
        Log::info('Request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'project_type' => 'required|in:project,rental',
            'project_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['error' => 'Dữ liệu không hợp lệ'], 400);
        }

        try {
            $detailedDevices = $this->collectDevicesWithSerials($request->project_type, (int) $request->project_id);
            $devices = $this->formatDevicesForApi($detailedDevices);
            Log::info('Final devices array (api):', ['count' => count($devices)]);
            return response()->json(['devices' => $devices]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thiết bị (API): ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi lấy thiết bị: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Thu thập danh sách thiết bị đầy đủ (bao gồm serial) theo project/rental
     */
    private function collectDevicesWithSerials(string $projectType, int $projectId): array
    {
        $devices = [];

        if ($projectType === 'project') {
            Log::info('Processing PROJECT type');
            $project = \App\Models\Project::with(['customer'])->findOrFail($projectId);
            Log::info('Project found:', ['id' => $project->id, 'name' => $project->project_name]);
            
            $dispatches = \App\Models\Dispatch::where('dispatch_type', 'project')
                ->where('project_id', $project->id)
                ->whereIn('status', ['approved', 'completed'])
                ->with(['items.product', 'items.good'])
                ->get();
            
            Log::info('Project dispatches found:', ['count' => $dispatches->count()]);
            foreach ($dispatches as $dispatch) {
                Log::info('Dispatch:', ['id' => $dispatch->id, 'code' => $dispatch->dispatch_code, 'type' => $dispatch->dispatch_type]);
            }

            foreach ($dispatches as $dispatch) {
                $this->appendDispatchItems($dispatch, $devices);
            }
        } else {
            Log::info('Processing RENTAL type');
            $rental = \App\Models\Rental::with(['customer'])->findOrFail($projectId);
            Log::info('Rental found:', ['id' => $rental->id, 'name' => $rental->rental_name]);
            
            $dispatches = \App\Models\Dispatch::where('dispatch_type', 'rental')
                ->where('project_id', $rental->id)
                ->whereIn('status', ['approved', 'completed'])
                ->with(['items.product', 'items.good'])
                ->get();
            
            Log::info('Rental dispatches found:', ['count' => $dispatches->count()]);
            foreach ($dispatches as $dispatch) {
                Log::info('Dispatch:', ['id' => $dispatch->id, 'code' => $dispatch->dispatch_code, 'type' => $dispatch->dispatch_type]);
            }

            foreach ($dispatches as $dispatch) {
                $this->appendDispatchItems($dispatch, $devices);
            }
        }

        return $devices;
    }

    /**
     * Bổ sung thiết bị từ dispatch vào danh sách
     */
    private function appendDispatchItems($dispatch, array &$devices): void
    {
        foreach ($dispatch->items as $item) {
            Log::info('Processing item:', [
                'id' => $item->id,
                'type' => $item->item_type,
                'item_id' => $item->item_id,
                'quantity' => $item->quantity
            ]);

            $returnedSerials = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                ->pluck('serial_number')
                ->filter()
                ->toArray();
            $returnedCount = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)->count();

            if ($item->item_type === 'product' && $item->product) {
                $this->appendDeviceEntries($devices, $item, $returnedSerials, $returnedCount, 'Thành phẩm', $item->product->code, $item->product->name);
            } elseif ($item->item_type === 'good' && $item->good) {
                $this->appendDeviceEntries($devices, $item, $returnedSerials, $returnedCount, 'Hàng hoá', $item->good->code, $item->good->name);
            }
        }
    }

    /**
     * Thêm dòng thiết bị theo serial/quantity
     */
    private function appendDeviceEntries(array &$devices, $item, array $returnedSerials, int $returnedCount, string $typeLabel, string $code, string $name): void
    {
        if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
            foreach ($item->serial_numbers as $idx => $sn) {
                if (!empty($sn) && in_array($sn, $returnedSerials)) {
                    continue;
                }
                $devices[] = [
                    'id' => $item->id . '_' . $idx,
                    'code' => $code,
                    'name' => $name,
                    'serial_number' => !empty($sn) ? $sn : 'N/A',
                    'type' => $typeLabel,
                    'quantity' => 1,
                ];
            }
        } else {
            $availableQty = max(0, (int) $item->quantity - $returnedCount);
            for ($i = 0; $i < $availableQty; $i++) {
                $devices[] = [
                    'id' => $item->id . '_' . $i,
                    'code' => $code,
                    'name' => $name,
                    'serial_number' => 'N/A',
                    'type' => $typeLabel,
                    'quantity' => 1,
                ];
            }
        }
    }

    /**
     * Chuẩn hóa danh sách thiết bị cho API bên ngoài
     */
    private function formatDevicesForApi(array $devices): array
    {
        $uniqueDevices = [];
        $seenDevices = [];

        foreach ($devices as $device) {
            $code = $device['code'] ?? null;
            if (!$code) {
                continue;
            }
            $type = $this->normalizeDeviceType($device['type'] ?? '');
            $key = $code . '_' . $type;

            if (!isset($seenDevices[$key])) {
                $seenDevices[$key] = true;
                $uniqueDevices[] = [
                    'code' => $code,
                    'name' => $device['name'] ?? '',
                    'type' => $type,
                ];
            }
        }

        return $uniqueDevices;
    }

    private function normalizeDeviceType(?string $typeLabel): string
    {
        if (!$typeLabel) {
            return 'product';
        }

        $normalized = strtolower($typeLabel);
        if (in_array($normalized, ['hàng hoá', 'hang hoa', 'good'])) {
            return 'good';
        }

        return 'product';
    }

    /**
     * API: Lấy danh sách dự án/phiếu cho thuê dựa trên project_type và thông tin khách hàng
     */
    public function getProjectsOrRentals(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_type' => 'required|in:project,rental',
            'customer_id' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'customer_email' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ'], 400);
        }

        try {
            $projectType = $request->project_type;
            $results = [];

            if ($projectType === 'project') {
                $query = \App\Models\Project::with('customer');

                // Filter theo thông tin khách hàng
                if ($request->filled('customer_id')) {
                    $query->where('customer_id', $request->customer_id);
                } elseif ($request->filled('customer_name')) {
                    $query->whereHas('customer', function($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->customer_name . '%')
                          ->orWhere('company_name', 'like', '%' . $request->customer_name . '%');
                    });
                } elseif ($request->filled('customer_phone')) {
                    $query->whereHas('customer', function($q) use ($request) {
                        $q->where('phone', 'like', '%' . $request->customer_phone . '%');
                    });
                } elseif ($request->filled('customer_email')) {
                    $query->whereHas('customer', function($q) use ($request) {
                        $q->where('email', 'like', '%' . $request->customer_email . '%');
                    });
                }

                $projects = $query->get();

                $results = $projects->map(function($project) {
                    return [
                        'project_code' => $project->project_code,
                        'project_name' => $project->project_name,
                    ];
                })->toArray();

            } else { // rental
                $query = \App\Models\Rental::with('customer');

                // Filter theo thông tin khách hàng
                if ($request->filled('customer_id')) {
                    $query->where('customer_id', $request->customer_id);
                } elseif ($request->filled('customer_name')) {
                    $query->whereHas('customer', function($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->customer_name . '%')
                          ->orWhere('company_name', 'like', '%' . $request->customer_name . '%');
                    });
                } elseif ($request->filled('customer_phone')) {
                    $query->whereHas('customer', function($q) use ($request) {
                        $q->where('phone', 'like', '%' . $request->customer_phone . '%');
                    });
                } elseif ($request->filled('customer_email')) {
                    $query->whereHas('customer', function($q) use ($request) {
                        $q->where('email', 'like', '%' . $request->customer_email . '%');
                    });
                }

                $rentals = $query->get();

                $results = $rentals->map(function($rental) {
                    return [
                        'project_code' => $rental->rental_code, // Dùng project_code để đồng bộ với API
                        'project_name' => $rental->rental_name, // Dùng project_name để đồng bộ với API
                    ];
                })->toArray();
            }

            return response()->json(['projects' => $results]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách dự án/phiếu cho thuê: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi lấy danh sách: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy danh sách serial thiết bị dựa trên device_id (product_id/good_id) và project_id
     */
    public function getDeviceSerials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|integer',
            'project_type' => 'required|in:project,rental',
            'project_id' => 'required|integer',
            'item_type' => 'nullable|in:product,good', // Loại thiết bị: product hoặc good
            'category' => 'nullable|in:contract,backup', // Loại category: contract hoặc backup
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ'], 400);
        }

        try {
            $deviceId = $request->device_id; // product_id hoặc good_id
            $projectType = $request->project_type;
            $projectId = $request->project_id;
            $itemType = $request->item_type; // 'product' hoặc 'good'
            $category = $request->category; // 'contract' hoặc 'backup'

            // Lấy dispatches theo project_type và project_id
            $dispatches = \App\Models\Dispatch::where('dispatch_type', $projectType)
                ->where('project_id', $projectId)
                ->whereIn('status', ['approved', 'completed'])
                ->pluck('id');

            if ($dispatches->isEmpty()) {
                return response()->json(['error' => 'Không tìm thấy dự án/phiếu cho thuê với ID: ' . $projectId], 404);
            }

            // Tìm dispatch items theo item_id và item_type
            $query = \App\Models\DispatchItem::whereIn('dispatch_id', $dispatches)
                ->where('item_id', $deviceId);

            // Nếu có category, filter theo category (contract hoặc backup)
            if ($category) {
                $query->where('category', $category);
            } else {
                // Nếu không có category, lấy cả contract và backup (giống trang show)
                $query->whereIn('category', ['contract', 'backup']);
            }

            // Nếu có item_type, filter theo item_type
            if ($itemType) {
                $query->where('item_type', $itemType);
            } else {
                // Nếu không có item_type, thử tìm cả product và good
                $query->whereIn('item_type', ['product', 'good']);
            }

            $dispatchItems = $query->with(['product', 'good', 'dispatch'])->get();

            if ($dispatchItems->isEmpty()) {
                return response()->json(['error' => 'Không tìm thấy thiết bị với ID: ' . $deviceId . ' trong dự án/phiếu cho thuê này'], 404);
            }

            $serials = [];

            // Lấy serial từ tất cả dispatch items tìm được (giống logic trang show)
            foreach ($dispatchItems as $dispatchItem) {
                $serialNumbers = $dispatchItem->serial_numbers ?? [];
                
                // Lấy serial hiển thị sử dụng SerialDisplayHelper (giống trang show)
                if (!empty($serialNumbers) && is_array($serialNumbers)) {
                    foreach ($serialNumbers as $originalSerial) {
                        $originalSerial = trim($originalSerial);
                        if (!empty($originalSerial)) {
                            // Sử dụng SerialDisplayHelper để lấy serial hiển thị (có thể đã đổi tên trong device_codes)
                            $displaySerial = \App\Helpers\SerialDisplayHelper::getDisplaySerial(
                                $dispatchItem->dispatch_id,
                                $dispatchItem->item_id,
                                $dispatchItem->item_type,
                                $originalSerial
                            );
                            $serials[] = $displaySerial;
                        }
                    }
                }
            }

            // Xử lý nhiều "N/A" thành "N/A", "N/A-2", "N/A-3", ...
            $naCount = 0;
            $processedSerials = [];
            foreach ($serials as $serial) {
                if ($serial === 'N/A' || empty($serial)) {
                    $naCount++;
                    if ($naCount === 1) {
                        $processedSerials[] = 'N/A';
                    } else {
                        $processedSerials[] = 'N/A-' . $naCount;
                    }
                } else {
                    $processedSerials[] = $serial;
                }
            }

            return response()->json(['serial' => $processedSerials]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy serial thiết bị: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi lấy serial thiết bị: ' . $e->getMessage()], 500);
        }
    }
} 