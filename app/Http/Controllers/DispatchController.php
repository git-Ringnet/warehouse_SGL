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
use App\Models\Project;
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
        $query = Dispatch::with(['project', 'creator', 'companyRepresentative', 'items']);

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
        
        $projects = Project::with('customer')->get();

        $nextDispatchCode = Dispatch::generateDispatchCode();

        return view('inventory.dispatch', compact('warehouses', 'employees', 'projects', 'nextDispatchCode'));
    }

    /**
     * Store a newly created dispatch in storage.
     */
    public function store(Request $request)
    {
        Log::info('=== DISPATCH STORE STARTED ===');
        Log::info('Request data:', $request->all());
        
        try {
            $request->validate([
                'dispatch_date' => 'required|date',
                'dispatch_type' => 'required|in:project,rental,warranty',
                'dispatch_detail' => 'required|in:all,contract,backup',
                'project_id' => 'nullable|exists:projects,id',
                'project_receiver' => 'required|string',
                'items' => 'required|array|min:1',
                'items.*.item_type' => 'required|in:material,product,good',
                'items.*.item_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.warehouse_id' => 'required|exists:warehouses,id',
                'items.*.category' => 'sometimes|in:contract,backup,general',
            ]);
            Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        DB::beginTransaction();

        try {
            // TEMPORARY: Disable stock check to allow dispatch creation
            Log::info('STOCK CHECK TEMPORARILY DISABLED - Creating dispatch without stock validation');
            Log::info('Items to be dispatched:', $request->items);
            
            // Kiểm tra tồn kho trước khi tạo phiếu xuất (tính tổng số lượng theo sản phẩm)
            Log::info('Starting stock check for items:', $request->items);
            $stockErrors = [];
            
            // Nhóm items theo sản phẩm và kho để tính tổng số lượng
            $groupedItems = [];
            if (isset($request->items) && is_array($request->items)) {
                foreach ($request->items as $index => $item) {
                    $key = $item['item_type'] . '_' . $item['item_id'] . '_' . $item['warehouse_id'];
                    if (!isset($groupedItems[$key])) {
                        $groupedItems[$key] = [
                            'item_type' => $item['item_type'],
                            'item_id' => $item['item_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'total_quantity' => 0,
                            'categories' => []
                        ];
                    }
                    $groupedItems[$key]['total_quantity'] += (int)$item['quantity'];
                    $groupedItems[$key]['categories'][] = $item['category'] ?? 'general';
                }
                
                // Kiểm tra tồn kho cho từng nhóm sản phẩm
                foreach ($groupedItems as $key => $groupedItem) {
                    Log::info("Checking stock for grouped item $key:", $groupedItem);
                    try {
                        $stockCheck = $this->checkItemStock(
                            $groupedItem['item_type'], 
                            $groupedItem['item_id'], 
                            $groupedItem['warehouse_id'], 
                            $groupedItem['total_quantity']
                        );
                        Log::info("Stock check result for grouped item $key:", $stockCheck);
                        
                        if (!$stockCheck['sufficient']) {
                            // Thêm thông tin về categories để user hiểu rõ hơn
                            $categoriesText = implode(', ', array_unique($groupedItem['categories']));
                            $stockErrors[] = $stockCheck['message'] . " (Tổng từ: $categoriesText)";
                        }
                    } catch (\Exception $stockException) {
                        Log::error("Error checking stock for grouped item $key:", [
                            'item' => $groupedItem,
                            'error' => $stockException->getMessage(),
                            'trace' => $stockException->getTraceAsString()
                        ]);
                        // Skip stock check nếu có lỗi, để không block quá trình tạo phiếu
                    }
                }
            }

            if (!empty($stockErrors)) {
                Log::error('Stock errors found:', $stockErrors);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không đủ tồn kho:\n' . implode('\n', $stockErrors));
            }
            
            Log::info('Stock check passed, creating dispatch');

            // Trừ tồn kho ngay khi tạo phiếu xuất (nhóm theo sản phẩm)
            Log::info('Reducing stock for all items...');
            foreach ($groupedItems as $key => $groupedItem) {
                try {
                    $this->reduceItemStock(
                        $groupedItem['item_type'], 
                        $groupedItem['item_id'], 
                        $groupedItem['warehouse_id'], 
                        $groupedItem['total_quantity']
                    );
                    Log::info("Reduced stock for grouped item $key: {$groupedItem['item_type']} ID {$groupedItem['item_id']}, total quantity {$groupedItem['total_quantity']}");
                } catch (\Exception $stockException) {
                    Log::error("Error reducing stock for grouped item $key:", [
                        'item' => $groupedItem,
                        'error' => $stockException->getMessage()
                    ]);
                    // Rollback sẽ được xử lý bởi DB::rollback() trong catch block
                    throw $stockException;
                }
            }

            // Create dispatch
            $dispatchData = [
                'dispatch_code' => Dispatch::generateDispatchCode(),
                'dispatch_date' => $request->dispatch_date,
                'dispatch_type' => $request->dispatch_type,
                'dispatch_detail' => $request->dispatch_detail,
                'project_id' => $request->project_id,
                'project_receiver' => $request->project_receiver,
                'warranty_period' => $request->warranty_period,
                'company_representative_id' => $request->company_representative_id,
                'dispatch_note' => $request->dispatch_note,
                'status' => 'pending',
                'created_by' => Auth::id() ?? 1, // Default to user ID 1 if not authenticated
            ];
            Log::info('Creating dispatch with data:', $dispatchData);
            
            $dispatch = Dispatch::create($dispatchData);
            Log::info('Dispatch created with ID:', ['id' => $dispatch->id]);

            // Create dispatch items and warranties
            Log::info('Creating dispatch items...');
            foreach ($request->items as $index => $item) {
                Log::info("Creating dispatch item $index:", $item);
                
                // Determine category based on dispatch_detail and item data
                $category = $this->determineItemCategory($dispatch->dispatch_detail, $item);
                Log::info("Determined category for item $index:", ['category' => $category]);
                
                $dispatchItemData = [
                    'dispatch_id' => $dispatch->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'warehouse_id' => $item['warehouse_id'],
                    'category' => $category,
                    'serial_numbers' => $item['serial_numbers'] ?? [],
                    'notes' => $item['notes'] ?? null,
                ];
                Log::info("Creating dispatch item with data:", $dispatchItemData);
                
                $dispatchItem = DispatchItem::create($dispatchItemData);
                Log::info("DispatchItem created with ID:", ['id' => $dispatchItem->id]);

                // Create warranty for each item
                try {
                    $this->createWarrantyForDispatchItem($dispatch, $dispatchItem, $request);
                    Log::info("Warranty created for dispatch item:", ['item_id' => $dispatchItem->id]);
                } catch (\Exception $warrantyException) {
                    Log::error("Error creating warranty for dispatch item:", [
                        'item_id' => $dispatchItem->id,
                        'error' => $warrantyException->getMessage()
                    ]);
                    // Continue processing other items even if warranty creation fails
                }
            }

            DB::commit();
            Log::info('Transaction committed successfully');

            // Count total warranties created
            $totalWarranties = $dispatch->warranties()->count();
            Log::info('Total warranties created:', ['count' => $totalWarranties]);

            Log::info('=== DISPATCH STORE COMPLETED SUCCESSFULLY ===');
            return redirect()->route('inventory.index')
                ->with('success', 'Phiếu xuất kho đã được tạo thành công. Mã phiếu: ' . $dispatch->dispatch_code . '. Đã tạo ' . $totalWarranties . ' bảo hành điện tử.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('=== DISPATCH STORE FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
        $dispatch->load(['project', 'creator', 'companyRepresentative', 'items.material', 'items.product', 'items.good', 'items.warehouse']);

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
        
        $projects = Project::with('customer')->get();

        $dispatch->load(['items.material', 'items.product', 'items.good', 'items.warehouse', 'project', 'companyRepresentative']);

        return view('inventory.dispatch_edit', compact('dispatch', 'warehouses', 'employees', 'projects'));
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
            'dispatch_type' => 'required|in:project,rental,warranty',
            'dispatch_detail' => 'required|in:all,contract,backup',
            'project_id' => 'nullable|exists:projects,id',
            'project_receiver' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:material,product,good',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.category' => 'sometimes|in:contract,backup,general',
        ]);

        DB::beginTransaction();

        try {
            // Update dispatch
            $dispatch->update([
                'dispatch_date' => $request->dispatch_date,
                'dispatch_type' => $request->dispatch_type,
                'dispatch_detail' => $request->dispatch_detail,
                'project_id' => $request->project_id,
                'project_receiver' => $request->project_receiver,
                'warranty_period' => $request->warranty_period,
                'company_representative_id' => $request->company_representative_id,
                'dispatch_note' => $request->dispatch_note,
            ]);

            // Kiểm tra tồn kho cho các items được cập nhật (chỉ khi phiếu chưa duyệt)
            if ($dispatch->status === 'pending') {
                $stockErrors = [];
                foreach ($request->items as $item) {
                    $stockCheck = $this->checkItemStock($item['item_type'], $item['item_id'], $item['warehouse_id'], $item['quantity']);
                    if (!$stockCheck['sufficient']) {
                        $stockErrors[] = $stockCheck['message'];
                    }
                }

                if (!empty($stockErrors)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Không đủ tồn kho:\n' . implode('\n', $stockErrors));
                }
            }

            // Delete existing items and recreate
            $dispatch->items()->delete();

            // Create new dispatch items
            foreach ($request->items as $item) {
                // Determine category based on dispatch_detail and item data
                $category = $this->determineItemCategory($dispatch->dispatch_detail, $item);
                
                // Xử lý serial numbers từ textarea
                $serialNumbers = [];
                if (isset($item['serial_numbers_text']) && !empty($item['serial_numbers_text'])) {
                    $serialNumbers = array_filter(
                        array_map('trim', explode("\n", $item['serial_numbers_text'])),
                        function($serial) { return !empty($serial); }
                    );
                } elseif (isset($item['serial_numbers'])) {
                    $serialNumbers = is_array($item['serial_numbers']) ? $item['serial_numbers'] : json_decode($item['serial_numbers'], true) ?? [];
                }
                
                DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'warehouse_id' => $item['warehouse_id'],
                    'category' => $category,
                    'serial_numbers' => $serialNumbers,
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

        DB::beginTransaction();

        try {
            // Tồn kho đã được trừ khi tạo phiếu, chỉ cần duyệt
            $dispatch->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được duyệt thành công.',
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
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

        DB::beginTransaction();

        try {
            // Hoàn trả tồn kho cho cả phiếu pending và approved (vì tồn kho đã bị trừ khi tạo)
            if (in_array($dispatch->status, ['pending', 'approved'])) {
                foreach ($dispatch->items as $item) {
                    $this->restoreItemStock($item->item_type, $item->item_id, $item->warehouse_id, $item->quantity);
                }
            }

            $dispatch->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            $message = in_array($dispatch->status, ['pending', 'approved'])
                ? 'Phiếu xuất đã được hủy thành công và tồn kho đã được hoàn trả.'
                : 'Phiếu xuất đã được hủy thành công.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            DB::rollback();

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
     * Get all available items for dispatch from all warehouses.
     * Returns thành phẩm (products) for dispatch from products table
     */
    public function getAllAvailableItems(Request $request)
    {
        $items = collect();

        // Get products from products table (thành phẩm) - TEMPORARY: Show all products
        $products = Product::with(['warehouseMaterials' => function ($query) {
                $query->where('item_type', 'product')
                    ->with('warehouse');
            }])->get();

        foreach ($products as $product) {
            // Get all warehouses that have this product
            $warehouses = [];
            if ($product->warehouseMaterials && $product->warehouseMaterials->isNotEmpty()) {
                foreach ($product->warehouseMaterials as $warehouseMaterial) {
                    $warehouses[] = [
                        'warehouse_id' => $warehouseMaterial->warehouse_id,
                        'warehouse_name' => $warehouseMaterial->warehouse->name ?? 'N/A',
                        'quantity' => $warehouseMaterial->quantity ?? 0
                    ];
                }
            } else {
                // If no warehouse materials, create default warehouses with 0 quantity
                $allWarehouses = \App\Models\Warehouse::take(3)->get();
                foreach ($allWarehouses as $warehouse) {
                    $warehouses[] = [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'quantity' => 0 // Show 0 quantity when no stock data
                    ];
                }
            }
            
            // Add item - always include products
            $items->push([
                'id' => $product->id,
                'type' => 'product',
                'code' => $product->code,
                'name' => $product->name,
                'unit' => 'Cái', // Default unit for products
                'warehouses' => $warehouses,
                'display_name' => "{$product->code} - {$product->name}"
            ]);
        }

        // If no products found, return empty but with debug info
        if ($items->isEmpty()) {
            $totalProducts = Product::count();
            $productsWithInventory = Product::whereHas('warehouseMaterials', function ($query) {
                $query->where('item_type', 'product')->where('quantity', '>', 0);
            })->count();
            
            return response()->json([
                'success' => true,
                'items' => [],
                'debug' => [
                    'total_products' => $totalProducts,
                    'products_with_inventory' => $productsWithInventory,
                    'message' => 'Không tìm thấy thành phẩm nào có tồn kho. Vui lòng kiểm tra dữ liệu trong bảng products và warehouse_materials.'
                ]
            ]);
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

        // Create warranty for the dispatch item (only one warranty per item type per project)
        // Check if warranty already exists for this item in this project
        $existingWarranty = Warranty::where('dispatch_id', $dispatch->id)
            ->where('item_type', $dispatchItem->item_type)
            ->where('item_id', $dispatchItem->item_id)
            ->first();

        if (!$existingWarranty) {
            // Get all serial numbers for this item
            $serialNumbers = is_array($dispatchItem->serial_numbers) ? $dispatchItem->serial_numbers : [];
            
            $warranty = Warranty::create([
                'warranty_code' => Warranty::generateWarrantyCode(),
                'dispatch_id' => $dispatch->id,
                'dispatch_item_id' => $dispatchItem->id,
                'item_type' => $dispatchItem->item_type,
                'item_id' => $dispatchItem->item_id,
                'serial_number' => !empty($serialNumbers) ? implode(', ', $serialNumbers) : null,
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
                'notes' => "Bảo hành tự động tạo từ phiếu xuất {$dispatch->dispatch_code} (Số lượng: {$dispatchItem->quantity})",
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

    /**
     * Get all projects for dispatch form
     */
    public function getProjects()
    {
        $projects = Project::with('customer')->get();
        
        return response()->json([
            'success' => true,
            'projects' => $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_code' => $project->project_code,
                    'project_name' => $project->project_name,
                    'customer_name' => $project->customer->name ?? '',
                    'warranty_period' => $project->warranty_period,
                    'warranty_period_formatted' => $project->warranty_period_formatted,
                    'display_name' => $project->project_code . ' - ' . $project->project_name . ' (' . ($project->customer->name ?? 'N/A') . ')'
                ];
            })
        ]);
    }

    /**
     * Check if item has sufficient stock.
     */
    private function checkItemStock($itemType, $itemId, $warehouseId, $requestedQuantity)
    {
        try {
            Log::info("Checking stock in database:", [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'requested_quantity' => $requestedQuantity
            ]);
            
            $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $itemType)
                ->where('material_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            Log::info("Database query result:", [
                'found_record' => $warehouseMaterial ? true : false,
                'quantity' => $warehouseMaterial ? $warehouseMaterial->quantity : 'N/A'
            ]);

            $currentStock = $warehouseMaterial ? $warehouseMaterial->quantity : 0;
            $sufficient = $currentStock >= $requestedQuantity;

            // Get item name for error message
            $itemName = 'Unknown';
            if ($itemType === 'material') {
                $item = \App\Models\Material::find($itemId);
            } elseif ($itemType === 'product') {
                $item = \App\Models\Product::find($itemId);
            } elseif ($itemType === 'good') {
                $item = \App\Models\Good::find($itemId);
            }
            
            if (isset($item)) {
                $itemName = "{$item->code} - {$item->name}";
            }

            return [
                'sufficient' => $sufficient,
                'current_stock' => $currentStock,
                'requested_quantity' => $requestedQuantity,
                'message' => $sufficient ? '' : "Không đủ tồn kho cho {$itemName}. Tồn kho hiện tại: {$currentStock}, yêu cầu: {$requestedQuantity}"
            ];

        } catch (\Exception $e) {
            Log::error("Error checking stock:", [
                'error' => $e->getMessage(),
                'params' => compact('itemType', 'itemId', 'warehouseId', 'requestedQuantity')
            ]);
            
            // Trả về kết quả an toàn để không block việc tạo phiếu
            return [
                'sufficient' => true, // Cho phép tạo phiếu nếu không check được stock
                'current_stock' => 0,
                'requested_quantity' => $requestedQuantity,
                'message' => ''
            ];
        }
    }

    /**
     * Reduce item stock in warehouse.
     */
    private function reduceItemStock($itemType, $itemId, $warehouseId, $quantity)
    {
        $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $itemType)
            ->where('material_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($warehouseMaterial) {
            $newQuantity = $warehouseMaterial->quantity - $quantity;
            $warehouseMaterial->update(['quantity' => max(0, $newQuantity)]);
        }
    }

    /**
     * Restore item stock in warehouse (for cancelled dispatches).
     */
    private function restoreItemStock($itemType, $itemId, $warehouseId, $quantity)
    {
        $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $itemType)
            ->where('material_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($warehouseMaterial) {
            $newQuantity = $warehouseMaterial->quantity + $quantity;
            $warehouseMaterial->update(['quantity' => $newQuantity]);
        } else {
            // Create new warehouse material record if it doesn't exist
            \App\Models\WarehouseMaterial::create([
                'item_type' => $itemType,
                'material_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);
        }
    }
}
