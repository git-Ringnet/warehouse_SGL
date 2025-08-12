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
        
        // Lấy danh sách dự án (tất cả, bao gồm cả đã quá hạn)
        $projects = \App\Models\Project::with(['customer'])
            ->select('*', \DB::raw('DATE_ADD(end_date, INTERVAL warranty_period MONTH) as warranty_end_date'))
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
                'request_date' => 'required|date',
                'proposer_id' => 'required|exists:employees,id',
                'project_name' => 'nullable|string|max:255', // Bỏ required vì tự động điền
                'customer_id' => 'required|exists:customers,id',
                'maintenance_date' => 'required|date',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'customer_address' => 'required|string|max:255',
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
            
            // Lấy thông tin dự án/phiếu cho thuê
            $projectName = '';
            $customerId = null;
            
            if ($request->project_type === 'project') {
                $project = \App\Models\Project::with('customer')->findOrFail($request->project_id);
                $projectName = $project->project_name;
                $customerId = $project->customer_id;
            } else {
                $rental = \App\Models\Rental::with('customer')->findOrFail($request->project_id);
                $projectName = $rental->rental_name;
                $customerId = $rental->customer_id;
            }
            
            // Tạo phiếu bảo trì mới
            $maintenanceRequest = MaintenanceRequest::create([
                'request_code' => MaintenanceRequest::generateRequestCode(),
                'request_date' => $request->request_date, // Sử dụng request_date
                'proposer_id' => $request->proposer_id,
                'project_name' => $projectName,
                'customer_id' => $customerId,
                'warranty_id' => null, // Không còn sử dụng warranty_id
                'project_address' => $request->customer_address,
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type ?? 'maintenance', // Mặc định là maintenance
                'maintenance_reason' => $request->notes ?? '', // Sử dụng notes làm maintenance_reason
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
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
        
        // Tự động gán project_type và project_id nếu thiếu
        if (!$maintenanceRequest->project_type) {
            $maintenanceRequest->project_type = count($projects) > 0 ? 'project' : (count($rentals) > 0 ? 'rental' : null);
        }
        if (!$maintenanceRequest->project_id) {
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
        
        // Validation cơ bản - cho phép chỉnh sửa toàn bộ
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'project_type' => 'nullable|in:project,rental', // Bỏ required vì phiếu cũ có thể không có
            'project_id' => 'nullable|integer', // Bỏ required vì phiếu cũ có thể không có
            'project_name' => 'nullable|string|max:255',
            'maintenance_date' => 'required|date',
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
        Log::info('=== GET DEVICES API CALLED ===');
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
            $devices = [];

            if ($request->project_type === 'project') {
                Log::info('Processing PROJECT type');
                $project = \App\Models\Project::with(['customer'])->findOrFail($request->project_id);
                Log::info('Project found:', ['id' => $project->id, 'name' => $project->project_name]);
                
                // Lấy thiết bị từ dispatches có dispatch_type = 'project' và project_id = project->id
                $dispatches = \App\Models\Dispatch::where('dispatch_type', 'project')
                    ->where('project_id', $project->id)
                    ->with(['items.product', 'items.good'])
                    ->get();
                
                Log::info('Project dispatches found:', ['count' => $dispatches->count()]);
                foreach($dispatches as $dispatch) {
                    Log::info('Dispatch:', ['id' => $dispatch->id, 'code' => $dispatch->dispatch_code, 'type' => $dispatch->dispatch_type]);
                }
                
                foreach ($dispatches as $dispatch) {
                    foreach ($dispatch->items as $item) {
                        Log::info('Processing item:', ['id' => $item->id, 'type' => $item->item_type, 'item_id' => $item->item_id, 'quantity' => $item->quantity]);
                        
                        if ($item->item_type === 'product' && $item->product) {
                            // Tạo từng record riêng biệt cho mỗi quantity
                            for ($i = 0; $i < $item->quantity; $i++) {
                                // Lấy serial number từ array nếu có
                                $serialNumber = 'N/A';
                                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                                    $serialNumber = $item->serial_numbers[$i] ?? 'N/A';
                                }
                                
                                $device = [
                                    'id' => $item->id . '_' . $i, // Tạo ID duy nhất cho từng item
                                    'code' => $item->product->code,
                                    'name' => $item->product->name,
                                    'serial_number' => $serialNumber,
                                    'type' => 'Thành phẩm',
                                    'quantity' => 1, // Mỗi record chỉ có quantity = 1
                                ];
                                $devices[] = $device;
                                Log::info('Added product device:', $device);
                            }
                        } elseif ($item->item_type === 'good' && $item->good) {
                            // Tạo từng record riêng biệt cho mỗi quantity
                            for ($i = 0; $i < $item->quantity; $i++) {
                                // Lấy serial number từ array nếu có
                                $serialNumber = 'N/A';
                                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                                    $serialNumber = $item->serial_numbers[$i] ?? 'N/A';
                                }
                                
                                $device = [
                                    'id' => $item->id . '_' . $i, // Tạo ID duy nhất cho từng item
                                    'code' => $item->good->code,
                                    'name' => $item->good->name,
                                    'serial_number' => $serialNumber,
                                    'type' => 'Hàng hoá',
                                    'quantity' => 1, // Mỗi record chỉ có quantity = 1
                                ];
                                $devices[] = $device;
                                Log::info('Added good device:', $device);
                            }
                        }
                    }
                }
            } else { // rental
                Log::info('Processing RENTAL type');
                $rental = \App\Models\Rental::with(['customer'])->findOrFail($request->project_id);
                Log::info('Rental found:', ['id' => $rental->id, 'name' => $rental->rental_name]);
                
                // Lấy thiết bị từ dispatches có dispatch_type = 'rental' và project_id = rental->id
                $dispatches = \App\Models\Dispatch::where('dispatch_type', 'rental')
                    ->where('project_id', $rental->id)
                    ->with(['items.product', 'items.good'])
                    ->get();
                
                Log::info('Rental dispatches found:', ['count' => $dispatches->count()]);
                foreach($dispatches as $dispatch) {
                    Log::info('Dispatch:', ['id' => $dispatch->id, 'code' => $dispatch->dispatch_code, 'type' => $dispatch->dispatch_type]);
                }
                
                foreach ($dispatches as $dispatch) {
                    foreach ($dispatch->items as $item) {
                        Log::info('Processing item:', ['id' => $item->id, 'type' => $item->item_type, 'item_id' => $item->item_id, 'quantity' => $item->quantity]);
                        
                        if ($item->item_type === 'product' && $item->product) {
                            // Tạo từng record riêng biệt cho mỗi quantity
                            for ($i = 0; $i < $item->quantity; $i++) {
                                // Lấy serial number từ array nếu có
                                $serialNumber = 'N/A';
                                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                                    $serialNumber = $item->serial_numbers[$i] ?? 'N/A';
                                }
                                
                                $device = [
                                    'id' => $item->id . '_' . $i, // Tạo ID duy nhất cho từng item
                                    'code' => $item->product->code,
                                    'name' => $item->product->name,
                                    'serial_number' => $serialNumber,
                                    'type' => 'Thành phẩm',
                                    'quantity' => 1, // Mỗi record chỉ có quantity = 1
                                ];
                                $devices[] = $device;
                                Log::info('Added product device:', $device);
                            }
                        } elseif ($item->item_type === 'good' && $item->good) {
                            // Tạo từng record riêng biệt cho mỗi quantity
                            for ($i = 0; $i < $item->quantity; $i++) {
                                // Lấy serial number từ array nếu có
                                $serialNumber = 'N/A';
                                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                                    $serialNumber = $item->serial_numbers[$i] ?? 'N/A';
                                }
                                
                                $device = [
                                    'id' => $item->id . '_' . $i, // Tạo ID duy nhất cho từng item
                                    'code' => $item->good->code,
                                    'name' => $item->good->name,
                                    'serial_number' => $serialNumber,
                                    'type' => 'Hàng hoá',
                                    'quantity' => 1, // Mỗi record chỉ có quantity = 1
                                ];
                                $devices[] = $device;
                                Log::info('Added good device:', $device);
                            }
                        }
                    }
                }
            }

            Log::info('Final devices array:', ['count' => count($devices), 'devices' => $devices]);
            return response()->json(['devices' => $devices]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thiết bị: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Có lỗi xảy ra khi lấy thiết bị: ' . $e->getMessage()], 500);
        }
    }
} 