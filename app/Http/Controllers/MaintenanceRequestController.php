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

class MaintenanceRequestController extends Controller
{
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
        
        // Lấy các bảo hành còn hiệu lực
        $warranties = Warranty::with(['dispatch.project', 'dispatch.project.customer'])
            ->where('status', 'active')
            ->whereDate('warranty_end_date', '>=', now()) // Chỉ lấy bảo hành chưa hết hạn
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('requests.maintenance.create', compact('employees', 'customers', 'products', 'warranties'));
    }

    /**
     * Lưu phiếu bảo trì dự án mới vào database
     */
    public function store(Request $request)
    {
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
                if (Auth::check()) {
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
            'request_date' => 'required|date',
            'proposer_id' => 'required|exists:employees,id',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'maintenance_date' => 'required|date',
            'maintenance_reason' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];
        
        // Validation khác nhau phụ thuộc vào việc có warranty_id hay không
        if ($request->filled('warranty_id')) {
            // Nếu có warranty_id, không bắt buộc phải có products hoặc warranty_items
            // Chúng ta sẽ tự động lấy các thiết bị từ warranty
        } else {
            // Nếu không có warranty_id, bắt buộc phải có products
            $validationRules['products'] = 'required|array|min:1';
            $validationRules['products.*.id'] = 'required|exists:products,id';
            $validationRules['products.*.quantity'] = 'required|integer|min:1';
        }
        
        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Tạo phiếu bảo trì mới
            $maintenanceRequest = MaintenanceRequest::create([
                'request_code' => MaintenanceRequest::generateRequestCode(),
                'request_date' => $request->request_date,
                'proposer_id' => $request->proposer_id,
                'project_name' => $request->project_name,
                'customer_id' => $request->customer_id,
                'warranty_id' => $request->warranty_id,
                'project_address' => $request->customer_address,
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type ?? 'regular', // Mặc định là regular
                'maintenance_reason' => $request->maintenance_reason,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);
            
            // Lưu danh sách thành phẩm từ bảo hành hoặc chọn thủ công
            if ($request->filled('warranty_id')) {
                // Lấy thông tin warranty
                $warranty = Warranty::with(['dispatch.items' => function($query) {
                    $query->where('category', 'contract');
                }, 'dispatch.items.product'])->findOrFail($request->warranty_id);
                
                // Lấy các thiết bị từ warranty
                if ($warranty->dispatch && $warranty->dispatch->items) {
                    foreach ($warranty->dispatch->items as $dispatchItem) {
                        if ($dispatchItem->item_type == 'product' && $dispatchItem->product) {
                            MaintenanceRequestProduct::create([
                                'maintenance_request_id' => $maintenanceRequest->id,
                                'product_id' => $dispatchItem->product->id,
                                'product_name' => $dispatchItem->product->name,
                                'product_code' => $dispatchItem->product->code,
                                'quantity' => 1,
                                'unit' => $dispatchItem->product->unit,
                                'description' => $dispatchItem->product->description
                            ]);
                        }
                    }
                }
            } elseif ($request->has('products')) {
                // Xử lý cách cũ nếu không có warranty_items
                foreach ($request->products as $product) {
                    $productModel = Product::findOrFail($product['id']);
                    MaintenanceRequestProduct::create([
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'product_id' => $product['id'],
                        'product_name' => $productModel->name,
                        'product_code' => $productModel->code,
                        'quantity' => $product['quantity'],
                        'unit' => $productModel->unit,
                        'description' => $productModel->description
                    ]);
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
            if (Auth::check()) {
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
            Log::error($e->getTraceAsString());
            
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
        if (Auth::check()) {
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
            
        return view('requests.maintenance.edit', compact('maintenanceRequest'));
    }

    /**
     * Cập nhật phiếu bảo trì dự án
     */
    public function update(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        
        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $maintenanceRequest->toArray();
        
        // Chỉ cho phép cập nhật nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể chỉnh sửa phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        // Validation cơ bản
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'project_name' => 'required|string|max:255',
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|in:regular,emergency,preventive',
            'maintenance_reason' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Cập nhật phiếu bảo trì
            $maintenanceRequest->update([
                'request_date' => $request->request_date,
                'project_name' => $request->project_name,
                'project_address' => $request->customer_address,
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type,
                'maintenance_reason' => $request->maintenance_reason,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'notes' => $request->notes,
            ]);
            
            DB::commit();
            
            // Ghi nhật ký cập nhật phiếu bảo trì
            if (Auth::check()) {
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
        
        // Chỉ cho phép xóa nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể xóa phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        try {
            DB::beginTransaction();
            
            // Xóa phiếu bảo trì và các dữ liệu liên quan
            $maintenanceRequest->delete();
            
            DB::commit();
            
            // Ghi nhật ký xóa phiếu bảo trì
            if (Auth::check()) {
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
            if (Auth::check()) {
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
        
        // Xác định loại sửa chữa dựa trên loại bảo trì
        $repairType = 'maintenance';
        switch ($maintenanceRequest->maintenance_type) {
            case 'emergency':
                $repairType = 'repair';
                break;
            case 'preventive':
                $repairType = 'maintenance';
                break;
            default:
                $repairType = 'maintenance';
        }
        
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
                    'device_serial' => '',
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
                    RepairItem::create([
                        'repair_id' => $repair->id,
                        'device_code' => $item->product->code,
                        'device_name' => $item->product->name,
                        'device_serial' => $item->serial_number ?? '',
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
            'reject_reason' => 'required|string|min:10',
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
                'reject_reason' => $request->reject_reason,
            ]);

            // Ghi nhật ký từ chối phiếu bảo trì
            if (Auth::check()) {
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
} 