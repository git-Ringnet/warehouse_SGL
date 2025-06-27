<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestProduct;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Notification;
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
        
        return view('requests.maintenance.create', compact('employees', 'customers', 'products'));
    }

    /**
     * Lưu phiếu bảo trì dự án mới vào database
     */
    public function store(Request $request)
    {
        // Kiểm tra nếu là sao chép từ phiếu đã tồn tại
        if ($request->has('copy_from')) {
            $sourceRequest = MaintenanceRequest::with(['products', 'staff'])->findOrFail($request->copy_from);
            
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
                
                // Sao chép nhân sự từ phiếu nguồn
                $staffIds = $sourceRequest->staff->pluck('id')->toArray();
                $newRequest->staff()->attach($staffIds);
                
                DB::commit();
                
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
        
        // Validation cơ bản
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'proposer_id' => 'required|exists:employees,id',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'project_address' => 'required|string|max:255',
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|in:regular,emergency,preventive',
            'maintenance_reason' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'staff' => 'required|array|min:1',
            'staff.*.id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);
        
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
                'project_address' => $request->project_address,
                'maintenance_date' => $request->maintenance_date,
                'maintenance_type' => $request->maintenance_type,
                'maintenance_reason' => $request->maintenance_reason,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);
            
            // Lưu danh sách thành phẩm
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
            
            // Lưu danh sách nhân sự
            $staffIds = collect($request->staff)->pluck('id')->toArray();
            $maintenanceRequest->staff()->attach($staffIds);
            
            // Gửi thông báo cho người đề xuất
            Notification::createNotification(
                'Phiếu bảo trì dự án mới',
                'Bạn đã tạo phiếu bảo trì dự án ' . $maintenanceRequest->project_name,
                'info',
                $request->proposer_id,
                'maintenance_request',
                $maintenanceRequest->id,
                route('requests.maintenance.show', $maintenanceRequest->id)
            );

            // Gửi thông báo cho các nhân viên được phân công
            foreach ($staffIds as $staffId) {
                Notification::createNotification(
                    'Được phân công bảo trì dự án mới',
                    'Bạn được phân công thực hiện bảo trì dự án ' . $maintenanceRequest->project_name,
                    'info',
                    $staffId,
                    'maintenance_request',
                    $maintenanceRequest->id,
                    route('requests.maintenance.show', $maintenanceRequest->id)
                );
            }
            
            DB::commit();
            
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
        $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])
            ->findOrFail($id);
            
        return view('requests.maintenance.show', compact('maintenanceRequest'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu bảo trì dự án
     */
    public function edit($id)
    {
        $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])
            ->findOrFail($id);
            
        return view('requests.maintenance.edit', compact('maintenanceRequest'));
    }

    /**
     * Cập nhật phiếu bảo trì dự án
     */
    public function update(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        
        // Chỉ cho phép cập nhật nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể chỉnh sửa phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        // Validation cơ bản
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'project_name' => 'required|string|max:255',
            'project_address' => 'required|string|max:255',
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
                'project_address' => $request->project_address,
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
        
        // Chỉ cho phép duyệt nếu phiếu đang ở trạng thái chờ duyệt
        if ($maintenanceRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể duyệt phiếu bảo trì ở trạng thái chờ duyệt.');
        }
        
        try {
            $maintenanceRequest->update([
                'status' => 'approved',
            ]);
            
            return redirect()->route('requests.maintenance.show', $maintenanceRequest->id)
                ->with('success', 'Phiếu bảo trì đã được duyệt thành công.');
                
        } catch (\Exception $e) {
            // Log lỗi chi tiết
            Log::error('Lỗi khi duyệt phiếu bảo trì: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi duyệt phiếu: ' . $e->getMessage());
        }
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
        $maintenanceRequest = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])
            ->findOrFail($id);
            
        return view('requests.maintenance.preview', compact('maintenanceRequest'));
    }
} 