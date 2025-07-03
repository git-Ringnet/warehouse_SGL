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
use App\Models\Rental;
use App\Helpers\ChangeLogHelper;
use App\Models\UserLog;
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
            $validationRules = [
                'dispatch_date' => 'required|date',
                'dispatch_type' => 'required|in:project,rental,warranty',
                'dispatch_detail' => 'required|in:all,contract,backup',
                'project_id' => 'nullable|exists:projects,id',
                'items' => 'required|array|min:1',
                'items.*.item_type' => 'required|in:material,product,good',
                'items.*.item_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.warehouse_id' => 'required|exists:warehouses,id',
                'items.*.category' => 'sometimes|in:contract,backup,general',
            ];

            // Validation for project_receiver depends on dispatch_type
            if ($request->dispatch_type === 'project' || $request->dispatch_type === 'warranty') {
                $validationRules['project_receiver'] = 'required|string';
            } elseif ($request->dispatch_type === 'rental') {
                // For rental, either project_receiver or rental_receiver should be provided
                $validationRules['project_receiver'] = 'required_without:rental_receiver|string';
                $validationRules['rental_receiver'] = 'required_without:project_receiver|string';
            }

            $request->validate($validationRules);
            Log::info('Basic validation passed');

            // Additional validation based on dispatch_detail
            $this->validateItemsByDispatchDetail($request);
            Log::info('Dispatch detail validation passed');

            // For rental type, ensure project_receiver is filled from rental_receiver if needed
            if ($request->dispatch_type === 'rental' && !$request->project_receiver && $request->rental_receiver) {
                $request->merge(['project_receiver' => $request->rental_receiver]);
                Log::info('Synced rental_receiver to project_receiver:', ['project_receiver' => $request->project_receiver]);
            }
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

            // KHÔNG trừ tồn kho khi tạo phiếu - chỉ trừ khi duyệt
            Log::info('Stock check passed, creating dispatch without reducing stock');

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

            // Ghi nhật ký tạo mới phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'dispatches',
                    'Tạo mới phiếu xuất: ' . $dispatch->dispatch_code,
                    null,
                    $dispatch->toArray()
                );
            }

            // Create dispatch items
            Log::info('Creating dispatch items...');
            $firstDispatchItem = null;
            foreach ($request->items as $index => $item) {
                Log::info("Creating dispatch item $index:", $item);
                Log::info("Raw serial_numbers for item $index:", [
                    'serial_numbers' => $item['serial_numbers'] ?? 'not set',
                    'type' => gettype($item['serial_numbers'] ?? null)
                ]);

                // Determine category based on dispatch_detail and item data
                $category = $this->determineItemCategory($dispatch->dispatch_detail, $item);
                Log::info("Determined category for item $index:", ['category' => $category]);

                // Handle serial numbers - can be JSON string or array
                $serialNumbers = [];
                if (isset($item['serial_numbers'])) {
                    if (is_string($item['serial_numbers'])) {
                        // If it's a JSON string, decode it
                        $decoded = json_decode($item['serial_numbers'], true);
                        $serialNumbers = is_array($decoded) ? $decoded : [];
                    } elseif (is_array($item['serial_numbers'])) {
                        // If it's already an array, use it directly
                        $serialNumbers = $item['serial_numbers'];
                    }
                    // Filter out empty values
                    $serialNumbers = array_filter($serialNumbers, function ($serial) {
                        return !empty(trim($serial));
                    });
                }

                $dispatchItemData = [
                    'dispatch_id' => $dispatch->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'warehouse_id' => $item['warehouse_id'],
                    'category' => $category,
                    'serial_numbers' => $serialNumbers,
                    'notes' => $item['notes'] ?? null,
                ];
                Log::info("Creating dispatch item with data:", $dispatchItemData);
                Log::info("Final serial_numbers for item $index:", [
                    'serial_numbers' => $serialNumbers,
                    'count' => count($serialNumbers)
                ]);

                $dispatchItem = DispatchItem::create($dispatchItemData);
                Log::info("DispatchItem created with ID:", ['id' => $dispatchItem->id]);

                // Store first dispatch item for warranty creation reference
                if ($firstDispatchItem === null) {
                    $firstDispatchItem = $dispatchItem;
                }
            }

            // KHÔNG tạo bảo hành khi tạo phiếu - chỉ tạo khi duyệt
            Log::info('Skipping warranty creation - will create when dispatch is approved');

            DB::commit();
            Log::info('Transaction committed successfully');

            // Count total warranties created
            $totalWarranties = $dispatch->warranties()->count();
            Log::info('Total warranties created:', ['count' => $totalWarranties]);

            Log::info('=== DISPATCH STORE COMPLETED SUCCESSFULLY ===');
            return redirect()->route('inventory.index')
                ->with('success', 'Phiếu xuất kho đã được tạo thành công.' . '. Đã tạo ' . $totalWarranties . ' bảo hành điện tử.');
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

        // Ghi nhật ký xem chi tiết phiếu xuất
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'dispatches',
                'Xem chi tiết phiếu xuất: ' . $dispatch->dispatch_code,
                null,
                $dispatch->toArray()
            );
        }

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

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $dispatch->toArray();

        // Validation rules depend on dispatch status
        if ($dispatch->status === 'pending') {
            // Full editing for pending dispatch
            $request->validate([
                'dispatch_date' => 'required|date',
                'dispatch_type' => 'required|in:project,rental,warranty',
                'dispatch_detail' => 'required|in:all,contract,backup',
                'project_id' => 'nullable|exists:projects,id',
                'project_receiver' => 'required|string',
                'company_representative_id' => 'nullable|exists:employees,id',
                'dispatch_note' => 'nullable|string',
                // Items validation for pending
                'contract_items.*' => 'nullable|array',
                'backup_items.*' => 'nullable|array',
                'general_items.*' => 'nullable|array',
            ]);

            // Additional validation for dispatch detail and items
            $this->validateUpdateItemsByDispatchDetail($request, $dispatch);
        } else {
            // Limited editing for approved dispatch
            $request->validate([
                'dispatch_date' => 'required|date',
                'company_representative_id' => 'nullable|exists:employees,id',
                'dispatch_note' => 'nullable|string',
                // Serial numbers validation for approved
                'contract_items.*' => 'nullable|array',
                'backup_items.*' => 'nullable|array',
                'general_items.*' => 'nullable|array',
            ]);
        }

        DB::beginTransaction();

        try {
            if ($dispatch->status === 'pending') {
                // Full update for pending dispatch
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

                // Process items for pending dispatch
                $this->updateDispatchItemsPending($request, $dispatch);
            } else {
                // Limited update for approved dispatch
                $dispatch->update([
                    'dispatch_date' => $request->dispatch_date,
                    'company_representative_id' => $request->company_representative_id,
                    'dispatch_note' => $request->dispatch_note,
                ]);

                // Only update serial numbers for approved dispatch
                $this->updateDispatchItemsApproved($request, $dispatch);
            }

            DB::commit();

            // Ghi nhật ký cập nhật phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'dispatches',
                    'Cập nhật phiếu xuất: ' . $dispatch->dispatch_code,
                    $oldData,
                    $dispatch->toArray()
                );
            }

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
     * Update dispatch items for pending dispatch (full editing)
     */
    private function updateDispatchItemsPending(Request $request, Dispatch $dispatch)
    {
        // Collect all items from different categories (existing + newly added)
        $allItems = [];

        // Process existing contract items (by dispatch item ID)
        if ($request->has('contract_items')) {
            foreach ($request->contract_items as $itemId => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                        $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'contract',
                        'serial_numbers' => $serialNumbers,
                    ];
                }
            }
        }

        // Process newly added items from dropdowns (items array)
        if ($request->has('items')) {
            foreach ($request->items as $itemKey => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    // Handle serial numbers - can be JSON string or array
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers'])) {
                        if (is_string($itemData['serial_numbers'])) {
                            // If it's a JSON string, decode it
                            $decoded = json_decode($itemData['serial_numbers'], true);
                            $serialNumbers = is_array($decoded) ? $decoded : [];
                        } elseif (is_array($itemData['serial_numbers'])) {
                            // If it's already an array, use it directly
                            $serialNumbers = $itemData['serial_numbers'];
                        }
                        // Filter out empty values
                        $serialNumbers = array_filter($serialNumbers, function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'general',
                        'serial_numbers' => $serialNumbers,
                    ];
                }
            }
        }

        // Process backup items
        if ($request->has('backup_items')) {
            foreach ($request->backup_items as $itemId => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                        $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'backup',
                        'serial_numbers' => $serialNumbers,
                    ];
                }
            }
        }

        // Process general items
        if ($request->has('general_items')) {
            foreach ($request->general_items as $itemId => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                        $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'general',
                        'serial_numbers' => $serialNumbers,
                    ];
                }
            }
        }

        // Validate stock for pending dispatch
        $stockErrors = [];
        foreach ($allItems as $item) {
            $stockCheck = $this->checkItemStock($item['item_type'], $item['item_id'], $item['warehouse_id'], $item['quantity']);
            if (!$stockCheck['sufficient']) {
                $stockErrors[] = $stockCheck['message'];
            }
        }

        if (!empty($stockErrors)) {
            throw new \Exception('Không đủ tồn kho:\n' . implode('\n', $stockErrors));
        }

        // Delete existing items and recreate
        $dispatch->items()->delete();

        // Create new dispatch items
        foreach ($allItems as $item) {
            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'warehouse_id' => $item['warehouse_id'],
                'category' => $item['category'],
                'serial_numbers' => $item['serial_numbers'],
            ]);
        }
    }

    /**
     * Update only serial numbers for approved dispatch
     */
    private function updateDispatchItemsApproved(Request $request, Dispatch $dispatch)
    {
        // Update contract items serial numbers
        if ($request->has('contract_items')) {
            foreach ($request->contract_items as $itemId => $itemData) {
                if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                    $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                        return !empty(trim($serial));
                    });

                    $dispatch->items()->where('id', $itemId)->update([
                        'serial_numbers' => $serialNumbers
                    ]);
                }
            }
        }

        // Update backup items serial numbers
        if ($request->has('backup_items')) {
            foreach ($request->backup_items as $itemId => $itemData) {
                if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                    $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                        return !empty(trim($serial));
                    });

                    $dispatch->items()->where('id', $itemId)->update([
                        'serial_numbers' => $serialNumbers
                    ]);
                }
            }
        }

        // Update general items serial numbers
        if ($request->has('general_items')) {
            foreach ($request->general_items as $itemId => $itemData) {
                if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                    $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                        return !empty(trim($serial));
                    });

                    $dispatch->items()->where('id', $itemId)->update([
                        'serial_numbers' => $serialNumbers
                    ]);
                }
            }
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

        // Check for duplicate serials with already approved dispatches
        $duplicateSerials = $this->checkDuplicateSerials($dispatch);
        if (!empty($duplicateSerials)) {
            return response()->json([
                'success' => false,
                'message' => 'Phát hiện serial numbers trùng lặp với phiếu xuất đã duyệt',
                'duplicate_serials' => $duplicateSerials
            ]);
        }

        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho lại trước khi duyệt
            Log::info('Re-checking stock before approval for dispatch:', ['dispatch_id' => $dispatch->id]);
            $stockErrors = [];

            // Nhóm items theo sản phẩm và kho để tính tổng số lượng
            $groupedItems = [];
            foreach ($dispatch->items as $item) {
                $key = $item->item_type . '_' . $item->item_id . '_' . $item->warehouse_id;
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'warehouse_id' => $item->warehouse_id,
                        'total_quantity' => 0,
                        'categories' => []
                    ];
                }
                $groupedItems[$key]['total_quantity'] += $item->quantity;
                $groupedItems[$key]['categories'][] = $item->category ?? 'general';
            }

            // Kiểm tra tồn kho cho từng nhóm sản phẩm
            foreach ($groupedItems as $key => $groupedItem) {
                try {
                    $stockCheck = $this->checkItemStock(
                        $groupedItem['item_type'],
                        $groupedItem['item_id'],
                        $groupedItem['warehouse_id'],
                        $groupedItem['total_quantity']
                    );

                    if (!$stockCheck['sufficient']) {
                        $categoriesText = implode(', ', array_unique($groupedItem['categories']));
                        $stockErrors[] = $stockCheck['message'] . " (Tổng từ: $categoriesText)";
                    }
                } catch (\Exception $stockException) {
                    Log::error("Error checking stock for grouped item $key:", [
                        'item' => $groupedItem,
                        'error' => $stockException->getMessage()
                    ]);
                    $stockErrors[] = "Lỗi kiểm tra tồn kho cho sản phẩm ID {$groupedItem['item_id']}";
                }
            }

            if (!empty($stockErrors)) {
                Log::error('Stock errors found during approval:', $stockErrors);
                return response()->json([
                    'success' => false,
                    'message' => 'Không đủ tồn kho để duyệt phiếu:\n' . implode('\n', $stockErrors)
                ]);
            }

            // Trừ tồn kho khi duyệt
            Log::info('Reducing stock for all items during approval...');
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
                    throw $stockException;
                }
            }

            // Ghi nhật ký thay đổi cho từng sản phẩm khi duyệt phiếu xuất
            Log::info('Creating change logs for dispatch approval...');
            foreach ($groupedItems as $key => $groupedItem) {
                try {
                    // Lấy thông tin sản phẩm
                    $itemModel = null;
                    switch ($groupedItem['item_type']) {
                        case 'material':
                            $itemModel = \App\Models\Material::find($groupedItem['item_id']);
                            break;
                        case 'product':
                            $itemModel = \App\Models\Product::find($groupedItem['item_id']);
                            break;
                        case 'good':
                            $itemModel = \App\Models\Good::find($groupedItem['item_id']);
                            break;
                    }

                    if ($itemModel) {
                        // Lấy tên dự án hoặc cho thuê dựa vào dispatch_type
                        $description = '';

                        if ($dispatch->dispatch_type === 'project' && $dispatch->project_id) {
                            $project = \App\Models\Project::find($dispatch->project_id);
                            $description = $project ? $project->project_name : 'Không có dự án';
                        } elseif ($dispatch->dispatch_type === 'rental' && $dispatch->project_id) {
                            // Với rental, project_id thực ra là rental_id
                            $rental = \App\Models\Rental::find($dispatch->project_id);
                            $description = $rental ? $rental->rental_name : 'Không có thông tin cho thuê';
                        }

                        // Tạo nhật ký xuất kho cho sản phẩm chính
                        ChangeLogHelper::xuatKho(
                            $itemModel->code,
                            $itemModel->name,
                            $groupedItem['total_quantity'],
                            $dispatch->dispatch_code,
                            $description, // Tên dự án/cho thuê
                            [
                                'dispatch_id' => $dispatch->id,
                                'warehouse_id' => $groupedItem['warehouse_id'],
                                'project_id' => $dispatch->project_id,
                                'dispatch_type' => $dispatch->dispatch_type,
                                'dispatch_detail' => $dispatch->dispatch_detail,
                                'project_receiver' => $dispatch->project_receiver,
                                'categories' => array_unique($groupedItem['categories']),
                                'item_type' => $groupedItem['item_type'],
                                'approved_by' => Auth::id(),
                                'approved_at' => now()->toDateTimeString()
                            ],
                            $dispatch->dispatch_note // Ghi chú của phiếu xuất
                        );
                    }
                } catch (\Exception $logException) {
                    Log::error("Error creating change log for grouped item $key:", [
                        'item' => $groupedItem,
                        'error' => $logException->getMessage()
                    ]);
                    // Continue processing even if change log creation fails
                }
            }

            // Cập nhật trạng thái duyệt
            $dispatch->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Tạo bảo hành điện tử khi duyệt (nếu không phải backup-only)
            if (!$this->isBackupOnlyDispatch($dispatch)) {
                $dispatch->load('items');
                $firstDispatchItem = $dispatch->items->first();

                if ($firstDispatchItem) {
                    try {
                        // Get warranty period for rental from rental record
                        $warrantyPeriod = $dispatch->warranty_period;
                        
                        // If not set and this is a rental, try to get from rental record
                        if (!$warrantyPeriod && $dispatch->dispatch_type === 'rental' && $dispatch->project_id) {
                            $rental = \App\Models\Rental::find($dispatch->project_id);
                            if ($rental) {
                                // Default warranty period for rental - can be customized
                                $warrantyPeriod = '12 tháng'; // Default 12 months for rental
                                Log::info("Using default warranty period for rental", [
                                    'rental_id' => $rental->id,
                                    'warranty_period' => $warrantyPeriod
                                ]);
                            }
                        }
                        
                        // If still no warranty period, use default
                        if (!$warrantyPeriod) {
                            $warrantyPeriod = '12 tháng';
                            Log::info("Using fallback warranty period", ['warranty_period' => $warrantyPeriod]);
                        }

                        // Tạo fake request object với thông tin cần thiết
                        $fakeRequest = new \Illuminate\Http\Request();
                        $fakeRequest->merge([
                            'dispatch_type' => $dispatch->dispatch_type,
                            'project_id' => $dispatch->project_id,
                            'project_receiver' => $dispatch->project_receiver,
                            'warranty_period' => $warrantyPeriod
                        ]);

                        Log::info("Creating warranty with fake request", [
                            'dispatch_id' => $dispatch->id,
                            'dispatch_type' => $dispatch->dispatch_type,
                            'warranty_period' => $warrantyPeriod
                        ]);

                        $this->createWarrantyForDispatchItem($dispatch, $firstDispatchItem, $fakeRequest);
                        Log::info("Project warranty created during approval:", ['dispatch_id' => $dispatch->id]);
                    } catch (\Exception $warrantyException) {
                        Log::error("Error creating project warranty during approval:", [
                            'dispatch_id' => $dispatch->id,
                            'error' => $warrantyException->getMessage(),
                            'trace' => $warrantyException->getTraceAsString()
                        ]);
                        // Continue processing even if warranty creation fails
                    }
                }
            }

            DB::commit();

            // Ghi nhật ký duyệt phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'dispatches',
                    'Duyệt phiếu xuất: ' . $dispatch->dispatch_code,
                    null,
                    $dispatch->toArray()
                );
            }

            // Count total warranties created
            $totalWarranties = $dispatch->warranties()->count();

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được duyệt thành công. Đã tạo ' . $totalWarranties . ' bảo hành điện tử.',
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
        if (in_array($dispatch->status, ['approved', 'completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy phiếu xuất đã duyệt, đã hoàn thành hoặc đã hủy.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Chỉ pending mới có thể hủy, và pending chưa trừ tồn kho nên không cần hoàn trả
            Log::info('Cancelling pending dispatch:', ['dispatch_id' => $dispatch->id]);

            $dispatch->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            // Ghi nhật ký hủy phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'cancel',
                    'dispatches',
                    'Hủy phiếu xuất: ' . $dispatch->dispatch_code,
                    null,
                    $dispatch->toArray()
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được hủy thành công.',
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
     * Delete the specified dispatch (only when cancelled).
     */
    public function destroy(Dispatch $dispatch)
    {
        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $dispatch->toArray();
        $dispatchCode = $dispatch->dispatch_code;

        if ($dispatch->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa phiếu xuất đã hủy.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Xóa tất cả items của dispatch
            $dispatch->items()->delete();

            // Xóa dispatch
            $dispatch->delete();

            DB::commit();

            // Ghi nhật ký xóa phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'dispatches',
                    'Xóa phiếu xuất: ' . $dispatchCode,
                    $oldData,
                    null
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phiếu xuất: ' . $e->getMessage()
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

        // Get goods
        if (in_array($itemType, ['all', 'good'])) {
            $goods = Good::whereHas('warehouseMaterials', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'good')
                    ->where('quantity', '>', 0);
            })->with(['warehouseMaterials' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'good');
            }])->get();

            foreach ($goods as $good) {
                $quantity = $good->warehouseMaterials->sum('quantity');
                if ($quantity > 0) {
                    $items->push([
                        'id' => $good->id,
                        'type' => 'good',
                        'code' => $good->code,
                        'name' => $good->name,
                        'unit' => $good->unit ?? 'Cái',
                        'available_quantity' => $quantity,
                        'display_name' => "{$good->code} - {$good->name} (Tồn: {$quantity})"
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
     * Returns thành phẩm (products) and hàng hóa (goods) for dispatch
     */
    public function getAllAvailableItems(Request $request)
    {
        $items = collect();

        // Get products from products table (thành phẩm)
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

        // Get goods from goods table (hàng hóa)
        $goods = Good::with(['warehouseMaterials' => function ($query) {
            $query->where('item_type', 'good')
                ->with('warehouse');
        }])->get();

        foreach ($goods as $good) {
            // Get all warehouses that have this good
            $warehouses = [];
            if ($good->warehouseMaterials && $good->warehouseMaterials->isNotEmpty()) {
                foreach ($good->warehouseMaterials as $warehouseMaterial) {
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

            // Add item - always include goods
            $items->push([
                'id' => $good->id,
                'type' => 'good',
                'code' => $good->code,
                'name' => $good->name,
                'unit' => $good->unit ?? 'Cái', // Use good's unit or default to 'Cái'
                'warehouses' => $warehouses,
                'display_name' => "{$good->code} - {$good->name}"
            ]);
        }

        // If no items found, return empty but with debug info
        if ($items->isEmpty()) {
            $totalProducts = Product::count();
            $totalGoods = Good::count();
            $productsWithInventory = Product::whereHas('warehouseMaterials', function ($query) {
                $query->where('item_type', 'product')->where('quantity', '>', 0);
            })->count();
            $goodsWithInventory = Good::whereHas('warehouseMaterials', function ($query) {
                $query->where('item_type', 'good')->where('quantity', '>', 0);
            })->count();

            return response()->json([
                'success' => true,
                'items' => [],
                'debug' => [
                    'total_products' => $totalProducts,
                    'total_goods' => $totalGoods,
                    'products_with_inventory' => $productsWithInventory,
                    'goods_with_inventory' => $goodsWithInventory,
                    'message' => 'Không tìm thấy thành phẩm hoặc hàng hóa nào có tồn kho. Vui lòng kiểm tra dữ liệu trong bảng products, goods và warehouse_materials.'
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
        Log::info('=== CREATING WARRANTY FOR DISPATCH ITEM ===', [
            'dispatch_id' => $dispatch->id,
            'dispatch_code' => $dispatch->dispatch_code,
            'project_id' => $dispatch->project_id,
            'item_type' => $dispatchItem->item_type,
            'item_id' => $dispatchItem->item_id,
            'quantity' => $dispatchItem->quantity
        ]);

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
            Log::warning('Item not found, skipping warranty creation', [
                'item_type' => $dispatchItem->item_type,
                'item_id' => $dispatchItem->item_id
            ]);
            return; // Skip if item not found
        }

        Log::info('Item found for warranty', [
            'item_name' => $item->name,
            'item_code' => $item->code
        ]);

        // Create warranty for the dispatch 
        // For regular projects: one warranty per project (shared across dispatches)
        // For rentals: one warranty per rental (shared across all dispatches of same rental)
        $existingWarranty = null;
        
        if ($dispatch->dispatch_type === 'rental') {
            Log::info('Rental dispatch: checking for existing warranty in same rental (project_id)', [
                'dispatch_id' => $dispatch->id,
                'rental_id' => $dispatch->project_id,
                'dispatch_type' => $dispatch->dispatch_type
            ]);

            // For rental dispatches, share warranty across all dispatches of the same rental
            $existingWarranty = Warranty::whereHas('dispatch', function ($query) use ($dispatch) {
                $query->where('project_id', $dispatch->project_id)
                    ->where('dispatch_type', 'rental'); // Only rental dispatches share warranty
            })
                ->first();
                
            Log::info('Rental warranty check result', [
                'found_existing' => $existingWarranty ? true : false,
                'existing_warranty_id' => $existingWarranty ? $existingWarranty->id : null,
                'existing_warranty_code' => $existingWarranty ? $existingWarranty->warranty_code : null
            ]);
            
        } elseif ($dispatch->project_id) {
            Log::info('Project dispatch: checking for existing warranty in project (any dispatch)', [
                'project_id' => $dispatch->project_id,
                'dispatch_type' => $dispatch->dispatch_type
            ]);

            // For regular project dispatches, share warranty across all dispatches of the same project
            $existingWarranty = Warranty::whereHas('dispatch', function ($query) use ($dispatch) {
                $query->where('project_id', $dispatch->project_id)
                    ->where('dispatch_type', '!=', 'rental'); // Exclude rentals from shared warranty
            })
                ->first();

            Log::info('Project warranty check result', [
                'found_existing' => $existingWarranty ? true : false,
                'existing_warranty_id' => $existingWarranty ? $existingWarranty->id : null,
                'existing_warranty_code' => $existingWarranty ? $existingWarranty->warranty_code : null
            ]);
        } else {
            Log::info('No project_id, checking within same dispatch only');

            // For non-project dispatches, check only within the same dispatch
            $existingWarranty = Warranty::where('dispatch_id', $dispatch->id)
                ->first();
        }

        if (!$existingWarranty) {
            Log::info('Creating new warranty for project (no existing warranty found)');

            // Get only contract items from this dispatch for warranty (exclude backup items)
            $contractItems = $dispatch->items()->where('category', 'contract')->get();
            $allItemsInfo = [];
            $allSerialNumbers = [];

            Log::info('Processing contract items for warranty (excluding backup)', [
                'dispatch_id' => $dispatch->id,
                'total_contract_items' => $contractItems->count(),
                'total_all_items' => $dispatch->items->count()
            ]);

            foreach ($contractItems as $index => $item) {
                Log::info("Processing item $index", [
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity
                ]);

                // Get item details
                $itemDetails = null;
                switch ($item->item_type) {
                    case 'material':
                        $itemDetails = Material::find($item->item_id);
                        break;
                    case 'product':
                        $itemDetails = Product::find($item->item_id);
                        break;
                    case 'good':
                        $itemDetails = Good::find($item->item_id);
                        break;
                }

                if ($itemDetails) {
                    $itemInfo = "{$itemDetails->code} - {$itemDetails->name} (SL: {$item->quantity})";
                    $allItemsInfo[] = $itemInfo;
                    Log::info("Added item to warranty", ['item_info' => $itemInfo]);
                } else {
                    Log::warning("Item details not found", [
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id
                    ]);
                }

                // Collect serial numbers
                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                    $allSerialNumbers = array_merge($allSerialNumbers, $item->serial_numbers);
                    Log::info("Added serial numbers", ['serials' => $item->serial_numbers]);
                }
            }

            Log::info('Final warranty items summary', [
                'total_items_info' => count($allItemsInfo),
                'items_info' => $allItemsInfo,
                'total_serial_numbers' => count($allSerialNumbers)
            ]);

            $warranty = Warranty::create([
                'warranty_code' => Warranty::generateWarrantyCode(),
                'dispatch_id' => $dispatch->id,
                'dispatch_item_id' => $dispatchItem->id, // Keep reference to first item for compatibility
                'item_type' => 'project', // Mark as project-wide warranty
                'item_id' => $dispatch->project_id ?? 0, // Use project_id as item_id
                'serial_number' => !empty($allSerialNumbers) ? implode(', ', array_unique($allSerialNumbers)) : null,
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
                'warranty_terms' => $this->getProjectWarrantyTerms($allItemsInfo),
                'notes' => "Bảo hành tự động tạo từ phiếu xuất {$dispatch->dispatch_code}" .
                    ($dispatch->project_id && $dispatch->dispatch_type !== 'rental' ? " - Dự án: {$dispatch->project->project_name}" : "") .
                    ($dispatch->dispatch_type === 'rental' ? " - Cho thuê ID: {$dispatch->project_id}" : "") .
                    "\nBao gồm các sản phẩm: " . implode(', ', $allItemsInfo),
                'created_by' => Auth::id() ?? 1,
                'activated_at' => now(),
            ]);

            // Ghi nhật ký tạo mới bảo hành
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'warranties',
                    'Tạo mới bảo hành: ' . $warranty->warranty_code,
                    null,
                    $warranty->toArray()
                );
            }

            // Generate QR code
            $warranty->generateQRCode();

            Log::info('New project warranty created successfully', [
                'warranty_id' => $warranty->id,
                'warranty_code' => $warranty->warranty_code,
                'items_count' => count($allItemsInfo)
            ]);
        } else {
            Log::info('Updating existing project warranty instead of creating new one', [
                'existing_warranty_id' => $existingWarranty->id,
                'existing_warranty_code' => $existingWarranty->warranty_code
            ]);

            // Update existing warranty with additional information from new dispatch
            // Get only contract items from this dispatch to update warranty info (exclude backup)
            $contractItems = $dispatch->items()->where('category', 'contract')->get();
            $newItemsInfo = [];
            $newSerialNumbers = [];

            foreach ($contractItems as $item) {
                // Get item details
                $itemDetails = null;
                switch ($item->item_type) {
                    case 'material':
                        $itemDetails = Material::find($item->item_id);
                        break;
                    case 'product':
                        $itemDetails = Product::find($item->item_id);
                        break;
                    case 'good':
                        $itemDetails = Good::find($item->item_id);
                        break;
                }

                if ($itemDetails) {
                    $newItemsInfo[] = "{$itemDetails->code} - {$itemDetails->name} (SL: {$item->quantity})";
                }

                // Collect serial numbers
                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                    $newSerialNumbers = array_merge($newSerialNumbers, $item->serial_numbers);
                }
            }

            // Merge with existing serial numbers
            $existingSerials = $existingWarranty->serial_number ? explode(', ', $existingWarranty->serial_number) : [];
            $allSerials = array_unique(array_merge($existingSerials, $newSerialNumbers));

            // Update notes to include new dispatch information
            $additionalNote = "\nPhiếu xuất bổ sung: {$dispatch->dispatch_code} - " . now()->format('d/m/Y H:i') .
                "\nThêm sản phẩm: " . implode(', ', $newItemsInfo);

            $existingWarranty->update([
                'serial_number' => !empty($allSerials) ? implode(', ', $allSerials) : $existingWarranty->serial_number,
                'notes' => $existingWarranty->notes . $additionalNote,
            ]);

            Log::info('Existing project warranty updated successfully', [
                'added_items_count' => count($newItemsInfo)
            ]);
        }

        Log::info('=== WARRANTY CREATION/UPDATE COMPLETED ===');
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
     * Get warranty terms for project-wide warranty
     */
    private function getProjectWarrantyTerms($allItemsInfo)
    {
        $terms = [
            "1. Toàn bộ sản phẩm trong dự án được bảo hành miễn phí trong thời gian bảo hành.",
            "2. Bảo hành không áp dụng cho các trường hợp hư hỏng do người sử dụng.",
            "3. Sản phẩm phải còn nguyên tem bảo hành và không có dấu hiệu tác động vật lý.",
            "4. Khách hàng cần mang theo phiếu bảo hành khi yêu cầu bảo hành.",
            "5. Thời gian bảo hành được tính từ ngày xuất kho.",
            "6. Bảo hành áp dụng cho tất cả sản phẩm trong dự án:"
        ];

        if (!empty($allItemsInfo)) {
            foreach ($allItemsInfo as $index => $itemInfo) {
                $terms[] = "   " . ($index + 1) . ". " . $itemInfo;
            }
        }

        return implode("\n", $terms);
    }

    /**
     * Check if dispatch contains only backup items (no warranty needed)
     */
    private function isBackupOnlyDispatch($dispatch)
    {
        // If dispatch_detail is explicitly 'backup', it's backup-only
        if ($dispatch->dispatch_detail === 'backup') {
            return true;
        }

        // If dispatch_detail is 'all', check if all items are backup category
        if ($dispatch->dispatch_detail === 'all') {
            $allItemsAreBackup = true;
            foreach ($dispatch->items as $item) {
                if ($item->category !== 'backup') {
                    $allItemsAreBackup = false;
                    break;
                }
            }
            return $allItemsAreBackup;
        }

        return false;
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
     * Get available serial numbers for a specific item in a specific warehouse
     * Only returns serials that are not already used in approved dispatches
     */
    public function getItemSerials(Request $request)
    {
        $itemType = $request->get('item_type');
        $itemId = $request->get('item_id');
        $warehouseId = $request->get('warehouse_id');
        $currentDispatchId = $request->get('current_dispatch_id'); // For edit mode

        if (!$itemType || !$itemId || !$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters'
            ]);
        }

        try {
            // Debug: Log query parameters
            Log::info('getItemSerials called with params:', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'current_dispatch_id' => $currentDispatchId
            ]);

            // Get serials based on item type and warehouse
            $serials = \App\Models\Serial::where('type', $itemType)
                ->where('product_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'active')
                ->pluck('serial_number')
                ->toArray();

            // Debug: Log raw serials found
            Log::info('Raw serials found in database:', [
                'count' => count($serials),
                'serials' => $serials
            ]);

            // Get serial numbers that are already used in approved dispatches
            $usedSerials = \App\Models\DispatchItem::whereHas('dispatch', function ($query) use ($currentDispatchId) {
                $query->where('status', 'approved');
                // Exclude current dispatch when editing
                if ($currentDispatchId) {
                    $query->where('id', '!=', $currentDispatchId);
                }
            })
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->get()
                ->pluck('serial_numbers')
                ->flatten()
                ->filter()
                ->toArray();

            // Debug: Log used serials
            Log::info('Used serials found:', [
                'count' => count($usedSerials),
                'used_serials' => $usedSerials
            ]);

            // Filter out used serials
            $availableSerials = array_diff($serials, $usedSerials);

            // Debug: Log final result
            Log::info('Final serial calculation:', [
                'total_serials' => count($serials),
                'used_serials' => count($usedSerials),
                'available_serials' => count($availableSerials),
                'available_serial_list' => array_values($availableSerials)
            ]);

            return response()->json([
                'success' => true,
                'serials' => array_values($availableSerials), // Re-index array
                'total_serials' => count($serials),
                'used_serials' => count($usedSerials),
                'available_serials' => count($availableSerials)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getItemSerials:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching serials: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check for duplicate serial numbers with already approved dispatches
     */
    private function checkDuplicateSerials(Dispatch $dispatch)
    {
        $duplicates = [];

        foreach ($dispatch->items as $dispatchItem) {
            if (empty($dispatchItem->serial_numbers) || !is_array($dispatchItem->serial_numbers)) {
                continue;
            }

            foreach ($dispatchItem->serial_numbers as $serial) {
                if (empty(trim($serial))) continue;

                // Check if this serial exists in any approved dispatch (excluding current one)
                $existingItem = DispatchItem::whereHas('dispatch', function ($query) use ($dispatch) {
                    $query->where('status', 'approved')
                        ->where('id', '!=', $dispatch->id);
                })
                    ->where('item_type', $dispatchItem->item_type)
                    ->where('item_id', $dispatchItem->item_id)
                    ->where('warehouse_id', $dispatchItem->warehouse_id)
                    ->whereJsonContains('serial_numbers', $serial)
                    ->with('dispatch')
                    ->first();

                if ($existingItem) {
                    $duplicates[] = [
                        'serial' => $serial,
                        'item_type' => $dispatchItem->item_type,
                        'item_id' => $dispatchItem->item_id,
                        'item_code' => $dispatchItem->item_code ?? 'N/A',
                        'item_name' => $dispatchItem->item_name ?? 'N/A',
                        'existing_dispatch_code' => $existingItem->dispatch->dispatch_code,
                        'existing_dispatch_id' => $existingItem->dispatch->id,
                    ];
                }
            }
        }

        return $duplicates;
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

    /**
     * Get all rentals for dispatch form
     */
    public function getRentals()
    {
        $rentals = Rental::with('customer')->get();

        return response()->json([
            'success' => true,
            'rentals' => $rentals->map(function ($rental) {
                return [
                    'id' => $rental->id,
                    'rental_code' => $rental->rental_code,
                    'rental_name' => $rental->rental_name,
                    'customer_name' => $rental->customer->company_name ?? '',
                    'customer_representative' => $rental->customer->name ?? '',
                    'rental_date' => $rental->rental_date,
                    'due_date' => $rental->due_date,
                    'display_name' => $rental->rental_code . ' - ' . $rental->rental_name . ' (' . ($rental->customer->company_name ?? 'N/A') . ')'
                ];
            })
        ]);
    }

    /**
     * Validate items based on dispatch_detail
     */
    private function validateItemsByDispatchDetail(Request $request)
    {
        $dispatchDetail = $request->dispatch_detail;
        $items = $request->items ?? [];

        if (empty($items)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['items' => ['Phiếu xuất phải có ít nhất một sản phẩm!']]
            );
        }

        // Group items by category
        $contractItems = [];
        $backupItems = [];
        $generalItems = [];

        foreach ($items as $item) {
            $category = $item['category'] ?? 'general';
            switch ($category) {
                case 'contract':
                    $contractItems[] = $item;
                    break;
                case 'backup':
                    $backupItems[] = $item;
                    break;
                default:
                    $generalItems[] = $item;
                    break;
            }
        }

        // Validate based on dispatch_detail
        switch ($dispatchDetail) {
            case 'contract':
                if (empty($contractItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất theo hợp đồng phải có ít nhất một thành phẩm theo hợp đồng!']]
                    );
                }
                if (!empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất theo hợp đồng không được chứa thiết bị dự phòng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.']]
                    );
                }
                break;

            case 'backup':
                if (empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất thiết bị dự phòng phải có ít nhất một thiết bị dự phòng!']]
                    );
                }
                if (!empty($contractItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất thiết bị dự phòng không được chứa sản phẩm hợp đồng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.']]
                    );
                }
                break;

            case 'all':
                if (empty($contractItems) && empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!']]
                    );
                }
                if (empty($contractItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất "Tất cả" phải có ít nhất một sản phẩm hợp đồng!']]
                    );
                }
                if (empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất "Tất cả" phải có ít nhất một thiết bị dự phòng!']]
                    );
                }
                break;
        }

        Log::info('Dispatch detail validation passed', [
            'dispatch_detail' => $dispatchDetail,
            'contract_items' => count($contractItems),
            'backup_items' => count($backupItems),
            'general_items' => count($generalItems)
        ]);
    }

    /**
     * Validate items for update based on dispatch_detail
     */
    private function validateUpdateItemsByDispatchDetail(Request $request, Dispatch $dispatch)
    {
        $dispatchDetail = $request->dispatch_detail;

        // Count existing items + new items
        $contractItemsCount = 0;
        $backupItemsCount = 0;
        $generalItemsCount = 0;

        // Count existing items that are not disabled/removed
        if ($request->has('contract_items')) {
            foreach ($request->contract_items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $contractItemsCount++;
                }
            }
        }

        if ($request->has('backup_items')) {
            foreach ($request->backup_items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $backupItemsCount++;
                }
            }
        }

        if ($request->has('general_items')) {
            foreach ($request->general_items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $generalItemsCount++;
                }
            }
        }

        // Count newly added items
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $category = $itemData['category'] ?? 'general';
                    switch ($category) {
                        case 'contract':
                            $contractItemsCount++;
                            break;
                        case 'backup':
                            $backupItemsCount++;
                            break;
                        default:
                            $generalItemsCount++;
                            break;
                    }
                }
            }
        }

        // Validate based on dispatch_detail
        switch ($dispatchDetail) {
            case 'contract':
                if ($contractItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['contract_items' => ['Phiếu xuất theo hợp đồng phải có ít nhất một thành phẩm theo hợp đồng!']]
                    );
                }
                if ($backupItemsCount > 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['backup_items' => ['Phiếu xuất theo hợp đồng không được chứa thiết bị dự phòng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.']]
                    );
                }
                break;

            case 'backup':
                if ($backupItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['backup_items' => ['Phiếu xuất thiết bị dự phòng phải có ít nhất một thiết bị dự phòng!']]
                    );
                }
                if ($contractItemsCount > 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['contract_items' => ['Phiếu xuất thiết bị dự phòng không được chứa sản phẩm hợp đồng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.']]
                    );
                }
                break;

            case 'all':
                if ($contractItemsCount === 0 && $backupItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!']]
                    );
                }
                if ($contractItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['contract_items' => ['Phiếu xuất "Tất cả" phải có ít nhất một sản phẩm hợp đồng!']]
                    );
                }
                if ($backupItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['backup_items' => ['Phiếu xuất "Tất cả" phải có ít nhất một thiết bị dự phòng!']]
                    );
                }
                break;
        }

        Log::info('Update dispatch detail validation passed', [
            'dispatch_detail' => $dispatchDetail,
            'contract_items' => $contractItemsCount,
            'backup_items' => $backupItemsCount,
            'general_items' => $generalItemsCount
        ]);
    }

    /**
     * Search dispatches via AJAX
     */
    public function search(Request $request)
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

        // Apply dispatch type filter
        if ($request->filled('dispatch_type')) {
            $query->where('dispatch_type', $request->dispatch_type);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('dispatch_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('dispatch_date', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'dispatch_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (in_array($sortBy, ['dispatch_code', 'dispatch_date', 'status', 'dispatch_type'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('dispatch_date', 'desc');
        }

        $query->orderBy('created_at', 'desc');

        $dispatches = $query->get();

        // Transform dispatches for JSON response
        $transformedDispatches = $dispatches->map(function ($dispatch) {
            return [
                'id' => $dispatch->id,
                'dispatch_code' => $dispatch->dispatch_code,
                'dispatch_date' => $dispatch->dispatch_date->format('d/m/Y'),
                'project_receiver' => $dispatch->project_receiver,
                'total_items' => $dispatch->items->count(),
                'dispatch_type' => $dispatch->dispatch_type,
                'company_representative' => $dispatch->companyRepresentative->name ?? '-',
                'creator' => $dispatch->creator->name ?? '-',
                'status' => $dispatch->status,
                'status_label' => $this->getStatusLabel($dispatch->status),
                'status_color' => $this->getStatusColor($dispatch->status),
                'can_edit' => !in_array($dispatch->status, ['completed', 'cancelled']),
                'can_approve' => $dispatch->status === 'pending',
                'can_cancel' => $dispatch->status === 'pending',
                'can_delete' => $dispatch->status === 'cancelled',
            ];
        });

        return response()->json([
            'success' => true,
            'dispatches' => $transformedDispatches,
            'total' => $dispatches->count(),
        ]);
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'pending':
                return 'Chờ xử lý';
            case 'approved':
                return 'Đã duyệt';
            case 'completed':
                return 'Đã hoàn thành';
            case 'cancelled':
                return 'Đã hủy';
            default:
                return 'Không xác định';
        }
    }

    /**
     * Get status color for styling
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'pending':
                return 'yellow';
            case 'approved':
                return 'blue';
            case 'completed':
                return 'green';
            case 'cancelled':
                return 'red';
            default:
                return 'gray';
        }
    }
}
