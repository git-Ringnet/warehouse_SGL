<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Warehouse;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DispatchController extends Controller
{
    /**
     * Display a listing of the dispatches.
     */
    public function index(Request $request)
    {
        $query = Dispatch::with(['warehouse', 'creator', 'companyRepresentative', 'items']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('dispatch_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('project_receiver', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('dispatch_note', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('dispatch_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('dispatch_date', '<=', $request->date_to);
        }

        $dispatches = $query->orderBy('dispatch_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('inventory.index', compact('dispatches'));
    }

    /**
     * Show the form for creating a new dispatch.
     */
    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();

        $employees = Employee::all();

        $nextDispatchCode = Dispatch::generateDispatchCode();

        return view('inventory.dispatch', compact('warehouses', 'employees', 'nextDispatchCode'));
    }

    /**
     * Store a newly created dispatch in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log received data
        Log::info('Dispatch store request data:', $request->all());
        
        $request->validate([
            'dispatch_date' => 'required|date',
            'dispatch_type' => 'required|in:project,rental,other',
            'dispatch_detail' => 'required|in:all,contract,backup',
            'project_receiver' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:material,product,good',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.category' => 'sometimes|in:contract,backup,general',
        ]);

        DB::beginTransaction();

        try {
            // Create dispatch
            $dispatch = Dispatch::create([
                'dispatch_code' => Dispatch::generateDispatchCode(),
                'dispatch_date' => $request->dispatch_date,
                'dispatch_type' => $request->dispatch_type,
                'dispatch_detail' => $request->dispatch_detail,
                'project_receiver' => $request->project_receiver,
                'warranty_period' => $request->warranty_period,
                'company_representative_id' => $request->company_representative_id,
                'warehouse_id' => $request->warehouse_id,
                'dispatch_note' => $request->dispatch_note,
                'status' => 'pending',
                'created_by' => Auth::id() ?? 1, // Default to user ID 1 if not authenticated
            ]);

            // Create dispatch items and warranties
            foreach ($request->items as $item) {
                // Determine category based on dispatch_detail and item data
                $category = $this->determineItemCategory($dispatch->dispatch_detail, $item);
                
                $dispatchItem = DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'category' => $category,
                    'serial_numbers' => $item['serial_numbers'] ?? [],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Create warranty for each item
                $this->createWarrantyForDispatchItem($dispatch, $dispatchItem, $request);
            }

            DB::commit();

            // Count total warranties created
            $totalWarranties = $dispatch->warranties()->count();

            return redirect()->route('inventory.index')
                ->with('success', 'Phiếu xuất kho đã được tạo thành công. Mã phiếu: ' . $dispatch->dispatch_code . '. Đã tạo ' . $totalWarranties . ' bảo hành điện tử.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo phiếu xuất: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dispatch.
     */
    public function show(Dispatch $dispatch)
    {
        $dispatch->load(['warehouse', 'creator', 'companyRepresentative', 'items.material', 'items.product', 'items.good']);

        return view('inventory.dispatch_detail', compact('dispatch'));
    }

    /**
     * Show the form for editing the specified dispatch.
     */
    public function edit(Dispatch $dispatch)
    {
        if (in_array($dispatch->status, ['completed', 'cancelled'])) {
            return redirect()->route('inventory.dispatch.show', $dispatch->id)
                ->with('error', 'Không thể chỉnh sửa phiếu xuất đã hoàn thành hoặc đã hủy.');
        }

        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();

        $employees = Employee::all();

        $dispatch->load(['items.material', 'items.product', 'items.good']);

        return view('inventory.dispatch_edit', compact('dispatch', 'warehouses', 'employees'));
    }

    /**
     * Update the specified dispatch in storage.
     */
    public function update(Request $request, Dispatch $dispatch)
    {
        if (in_array($dispatch->status, ['completed', 'cancelled'])) {
            return redirect()->route('inventory.dispatch.show', $dispatch->id)
                ->with('error', 'Không thể cập nhật phiếu xuất đã hoàn thành hoặc đã hủy.');
        }

        $request->validate([
            'dispatch_date' => 'required|date',
            'dispatch_type' => 'required|in:project,rental,other',
            'dispatch_detail' => 'required|in:all,contract,backup',
            'project_receiver' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:material,product,good',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.category' => 'sometimes|in:contract,backup,general',
        ]);

        DB::beginTransaction();

        try {
            // Update dispatch
            $dispatch->update([
                'dispatch_date' => $request->dispatch_date,
                'dispatch_type' => $request->dispatch_type,
                'dispatch_detail' => $request->dispatch_detail,
                'project_receiver' => $request->project_receiver,
                'warranty_period' => $request->warranty_period,
                'company_representative_id' => $request->company_representative_id,
                'warehouse_id' => $request->warehouse_id,
                'dispatch_note' => $request->dispatch_note,
            ]);

            // Delete existing items and recreate
            $dispatch->items()->delete();

            // Create new dispatch items
            foreach ($request->items as $item) {
                // Determine category based on dispatch_detail and item data
                $category = $this->determineItemCategory($dispatch->dispatch_detail, $item);
                
                DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'category' => $category,
                    'serial_numbers' => $item['serial_numbers'] ?? [],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('inventory.dispatch.show', $dispatch->id)
                ->with('success', 'Phiếu xuất kho đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật phiếu xuất: ' . $e->getMessage());
        }
    }

    /**
     * Approve the specified dispatch.
     */
    public function approve(Request $request, Dispatch $dispatch)
    {
        if ($dispatch->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể duyệt phiếu xuất đang chờ xử lý.'
            ]);
        }

        try {
            $dispatch->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được duyệt thành công.',
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt phiếu xuất: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel the specified dispatch.
     */
    public function cancel(Request $request, Dispatch $dispatch)
    {
        if (in_array($dispatch->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy phiếu xuất đã hoàn thành hoặc đã hủy.'
            ]);
        }

        try {
            $dispatch->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được hủy thành công.',
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy phiếu xuất: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mark the specified dispatch as completed.
     */
    public function complete(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hoàn thành phiếu xuất đã được duyệt.'
            ]);
        }

        try {
            $dispatch->update([
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được đánh dấu hoàn thành.',
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available items for dispatch from a specific warehouse.
     */
    public function getAvailableItems(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $itemType = $request->get('item_type', 'all');

        if (!$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse ID is required'
            ]);
        }

        $items = collect();

        // Get materials
        if (in_array($itemType, ['all', 'material'])) {
            $materials = Material::whereHas('warehouseMaterials', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'material')
                    ->where('quantity', '>', 0);
            })->with(['warehouseMaterials' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'material');
            }])->get();

            foreach ($materials as $material) {
                $quantity = $material->warehouseMaterials->sum('quantity');
                if ($quantity > 0) {
                    $items->push([
                        'id' => $material->id,
                        'type' => 'material',
                        'code' => $material->code,
                        'name' => $material->name,
                        'unit' => $material->unit,
                        'available_quantity' => $quantity,
                        'display_name' => "{$material->code} - {$material->name} (Tồn: {$quantity})"
                    ]);
                }
            }
        }

        // Get products
        if (in_array($itemType, ['all', 'product'])) {
            $products = Product::whereHas('warehouseMaterials', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'product')
                    ->where('quantity', '>', 0);
            })->with(['warehouseMaterials' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'product');
            }])->get();

            foreach ($products as $product) {
                $quantity = $product->warehouseMaterials->sum('quantity');
                if ($quantity > 0) {
                    $items->push([
                        'id' => $product->id,
                        'type' => 'product',
                        'code' => $product->code,
                        'name' => $product->name,
                        'unit' => 'Cái', // Products typically use "Cái" as unit
                        'available_quantity' => $quantity,
                        'display_name' => "{$product->code} - {$product->name} (Tồn: {$quantity})"
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items->sortBy('name')->values()
        ]);
    }

    /**
     * Create warranty for dispatch item
     */
    private function createWarrantyForDispatchItem(Dispatch $dispatch, DispatchItem $dispatchItem, Request $request)
    {
        // Parse warranty period from request or use default
        $warrantyPeriodMonths = 12; // Default 12 months
        if ($request->warranty_period) {
            // Extract number from warranty period string (e.g., "12 tháng" -> 12)
            preg_match('/(\d+)/', $request->warranty_period, $matches);
            if (!empty($matches[1])) {
                $warrantyPeriodMonths = (int) $matches[1];
            }
        }

        // Calculate warranty dates
        $warrantyStartDate = $dispatch->dispatch_date;
        $warrantyEndDate = $warrantyStartDate->copy()->addMonths($warrantyPeriodMonths);

        // Get item details
        $item = null;
        switch ($dispatchItem->item_type) {
            case 'material':
                $item = Material::find($dispatchItem->item_id);
                break;
            case 'product':
                $item = Product::find($dispatchItem->item_id);
                break;
            case 'good':
                $item = Good::find($dispatchItem->item_id);
                break;
        }

        if (!$item) {
            return; // Skip if item not found
        }

        // Create warranty for each quantity
        for ($i = 0; $i < $dispatchItem->quantity; $i++) {
            $warranty = Warranty::create([
                'warranty_code' => Warranty::generateWarrantyCode(),
                'dispatch_id' => $dispatch->id,
                'dispatch_item_id' => $dispatchItem->id,
                'item_type' => $dispatchItem->item_type,
                'item_id' => $dispatchItem->item_id,
                'serial_number' => $dispatchItem->serial_numbers[$i] ?? null,
                'customer_name' => $dispatch->project_receiver,
                'customer_phone' => null, // Can be added to form later
                'customer_email' => null, // Can be added to form later
                'customer_address' => null, // Can be added to form later
                'project_name' => $dispatch->project_receiver,
                'purchase_date' => $dispatch->dispatch_date,
                'warranty_start_date' => $warrantyStartDate,
                'warranty_end_date' => $warrantyEndDate,
                'warranty_period_months' => $warrantyPeriodMonths,
                'warranty_type' => 'standard',
                'status' => 'active',
                'warranty_terms' => $this->getDefaultWarrantyTerms($item),
                'notes' => "Bảo hành tự động tạo từ phiếu xuất {$dispatch->dispatch_code}",
                'created_by' => Auth::id() ?? 1,
                'activated_at' => now(),
            ]);

            // Generate QR code
            $warranty->generateQRCode();
        }
    }

    /**
     * Get default warranty terms based on item type
     */
    private function getDefaultWarrantyTerms($item)
    {
        $terms = [
            "1. Sản phẩm được bảo hành miễn phí trong thời gian bảo hành.",
            "2. Bảo hành không áp dụng cho các trường hợp hư hỏng do người sử dụng.",
            "3. Sản phẩm phải còn nguyên tem bảo hành và không có dấu hiệu tác động vật lý.",
            "4. Khách hàng cần mang theo phiếu bảo hành khi yêu cầu bảo hành.",
            "5. Thời gian bảo hành được tính từ ngày xuất kho."
        ];

        if ($item) {
            $terms[] = "6. Sản phẩm: {$item->name} - Mã: {$item->code}";
        }

        return implode("\n", $terms);
    }

    /**
     * Determine category for dispatch item based on dispatch_detail and item data
     */
    private function determineItemCategory($dispatchDetail, $item)
    {
        // If dispatch_detail is contract or backup, all items follow that category
        if ($dispatchDetail === 'contract') {
            return 'contract';
        }
        
        if ($dispatchDetail === 'backup') {
            return 'backup';
        }
        
        // If dispatch_detail is 'all', check if item has category specified
        if ($dispatchDetail === 'all') {
            // Check if category is explicitly set in the request
            if (isset($item['category']) && in_array($item['category'], ['contract', 'backup'])) {
                return $item['category'];
            }
            
            // Fallback: try to determine from item type or other indicators
            // This is for backward compatibility or when category is not explicitly set
            if (isset($item['item_type'])) {
                // You can add custom logic here based on your business rules
                // For now, we'll default to 'general' for mixed dispatches
                return 'general';
            }
        }
        
        // Default fallback
        return 'general';
    }
}
