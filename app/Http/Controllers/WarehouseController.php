<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Material;
use App\Models\Product;
use App\Models\Employee;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\UserLog;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the warehouses.
     */
    public function index(Request $request)
    {
        // Start with base query for active warehouses that are not hidden
        $query = Warehouse::where('is_hidden', false)
            ->where('status', 'active')->orderBy('id','desc');

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('managerEmployee', function ($q) use ($searchTerm) {
                        $q->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply manager filter
        if ($request->filled('manager')) {
            $query->whereHas('managerEmployee', function ($q) use ($request) {
                $q->where('name', $request->manager);
            });
        }

        // Apply inventory status filter before pagination
        if ($request->filled('inventory_status')) {
            // Get all warehouses to calculate total quantity
            $allWarehouses = $query->with('managerEmployee')->get();
            
            // Calculate total quantity for each warehouse
            $allWarehouses->each(function ($warehouse) {
                $warehouse->total_quantity = $warehouse->warehouseMaterials()->sum('quantity');
            });
            
            // Filter by inventory status
            if ($request->inventory_status === 'has_inventory') {
                $filteredWarehouseIds = $allWarehouses->filter(function ($warehouse) {
                    return $warehouse->total_quantity > 0;
                })->pluck('id');
            } elseif ($request->inventory_status === 'no_inventory') {
                $filteredWarehouseIds = $allWarehouses->filter(function ($warehouse) {
                    return $warehouse->total_quantity <= 0;
                })->pluck('id');
            }
            
            // Query again with filtered IDs
            $query = Warehouse::where('is_hidden', false)
                ->where('status', 'active')
                ->whereIn('id', $filteredWarehouseIds);
                
            // Re-apply search filter
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('managerEmployee', function ($q) use ($searchTerm) {
                            $q->where('name', 'LIKE', "%{$searchTerm}%");
                        })
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Re-apply manager filter
            if ($request->filled('manager')) {
                $query->whereHas('managerEmployee', function ($q) use ($request) {
                    $q->where('name', $request->manager);
                });
            }
        }

        $warehouses = $query->with('managerEmployee')->paginate(10)->withQueryString();

        // Calculate total quantity for display
        $warehouses->each(function ($warehouse) {
            $warehouse->total_quantity = $warehouse->warehouseMaterials()->sum('quantity');
        });

        // Get unique managers for filter dropdown
        $managers = \App\Models\Employee::whereHas('warehouses', function($q) {
            $q->where('is_hidden', false)
              ->where('status', 'active');
        })->pluck('name')->toArray();

        return view('warehouses.index', compact('warehouses', 'managers'));
    }

    /**
     * Show hidden warehouses
     */
    public function showHidden(Request $request)
    {
        // Start with base query for hidden warehouses
        $query = Warehouse::where('is_hidden', true);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('manager', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply manager filter
        if ($request->filled('manager')) {
            $query->where('manager', $request->manager);
        }

        $warehouses = $query->get();

        // Get unique managers for filter dropdown
        $managers = Warehouse::where('is_hidden', true)
            ->select('manager')
            ->distinct()
            ->pluck('manager')
            ->toArray();

        return view('warehouses.hidden', compact('warehouses', 'managers'));
    }

    /**
     * Show deleted warehouses
     */
    public function showDeleted(Request $request)
    {
        // Start with base query for deleted warehouses
        $query = Warehouse::where('status', 'deleted');

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('manager', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply manager filter
        if ($request->filled('manager')) {
            $query->where('manager', $request->manager);
        }

        $warehouses = $query->paginate(10);

        // Get unique managers for filter dropdown
        $managers = Warehouse::where('status', 'deleted')
            ->select('manager')
            ->distinct()
            ->pluck('manager')
            ->toArray();

        return view('warehouses.deleted', compact('warehouses', 'managers'));
    }

    /**
     * Restore hidden warehouse
     */
    public function restoreHidden(Request $request, Warehouse $warehouse)
    {
        try {
            $warehouse->update(['is_hidden' => false]);

            return redirect()->route('warehouses.hidden')
                ->with('success', 'Kho hàng đã được khôi phục thành công.');
        } catch (\Exception $e) {
            Log::error('Warehouse restore hidden error: ' . $e->getMessage());

            return redirect()->route('warehouses.hidden')
                ->with('error', 'Có lỗi xảy ra khi khôi phục kho hàng: ' . $e->getMessage());
        }
    }

    /**
     * Restore deleted warehouse
     */
    public function restoreDeleted(Request $request, Warehouse $warehouse)
    {
        try {
            $warehouse->update(['status' => 'active']);

            return redirect()->route('warehouses.deleted')
                ->with('success', 'Kho hàng đã được khôi phục thành công.');
        } catch (\Exception $e) {
            Log::error('Warehouse restore deleted error: ' . $e->getMessage());

            return redirect()->route('warehouses.deleted')
                ->with('error', 'Có lỗi xảy ra khi khôi phục kho hàng: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        return view('warehouses.create', compact('employees'));
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'name' => 'required',
            'manager' => 'required',
        ]);

        // Check if warehouse code exists in active or hidden warehouses
        $existingWarehouse = Warehouse::where('code', $request->code)
            ->where(function($query) {
                $query->where('status', 'active')
                    ->orWhere('is_hidden', true);
            })
            ->first();

        if ($existingWarehouse) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'Mã kho hàng đã tồn tại']);
        }

        $data = $request->all();
        $data['status'] = 'active';
        $data['is_hidden'] = false;

        // Tạo đối tượng mới
        $object = Warehouse::create($data);

        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'warehouses',
                'Tạo mới kho hàng: ' . $object->name,
                null,
                $object->toArray()
            );
        }

        return redirect()->route('warehouses.index')
            ->with('success', 'Kho hàng đã được thêm thành công.');
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse)
    {
        // Get materials in this warehouse
        $materials = WarehouseMaterial::where('warehouse_id', $warehouse->id)
            ->where('item_type', 'material')
            ->with('material')
            ->get()
            ->filter(function ($warehouseMaterial) {
                return $warehouseMaterial->material !== null;
            })
            ->map(function ($warehouseMaterial) {
                return [
                    'id' => $warehouseMaterial->material->id,
                    'code' => $warehouseMaterial->material->code,
                    'name' => $warehouseMaterial->material->name,
                    'category' => $warehouseMaterial->material->category ?? '',
                    'unit' => $warehouseMaterial->material->unit ?? '',
                    'quantity' => $warehouseMaterial->quantity,
                    'location' => $warehouseMaterial->location ?? '',
                    'serial_number' => $warehouseMaterial->serial_number ?? '',
                ];
            });

        // Get products in this warehouse
        $products = WarehouseMaterial::where('warehouse_id', $warehouse->id)
            ->where('item_type', 'product')
            ->with('product')
            ->get()
            ->filter(function ($warehouseMaterial) {
                return $warehouseMaterial->product !== null;
            })
            ->map(function ($warehouseMaterial) {
                return [
                    'id' => $warehouseMaterial->product->id,
                    'code' => $warehouseMaterial->product->code,
                    'name' => $warehouseMaterial->product->name,
                    'description' => $warehouseMaterial->product->description ?? '',
                    'quantity' => $warehouseMaterial->quantity,
                    'location' => $warehouseMaterial->location ?? '',
                    'serial_number' => $warehouseMaterial->serial_number ?? '',
                ];
            });

        $goods = WarehouseMaterial::where('warehouse_id', $warehouse->id)
            ->where('item_type', 'good')
            ->with('good')
            ->get()
            ->filter(function ($warehouseMaterial) {
                return $warehouseMaterial->good !== null;
            })
            ->map(function ($warehouseMaterial) {
                return [
                    'id' => $warehouseMaterial->good->id,
                    'code' => $warehouseMaterial->good->code,
                    'name' => $warehouseMaterial->good->name,
                    'category' => $warehouseMaterial->good->category ?? '',
                    'unit' => $warehouseMaterial->good->unit ?? '',
                    'quantity' => $warehouseMaterial->quantity,
                    'serial_number' => $warehouseMaterial->serial_number ?? '',
                ];
            });

        // Calculate totals
        $totalMaterials = $materials->sum('quantity');
        $totalProducts = $products->sum('quantity');
        $totalGoods = $goods->sum('quantity');
        $grandTotal = $totalMaterials + $totalProducts + $totalGoods;

        // Ghi nhật ký xem chi tiết kho hàng
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'warehouses',
                'Xem chi tiết kho hàng: ' . $warehouse->name,
                null,
                $warehouse->toArray()
            );
        }

        return view('warehouses.show', compact('warehouse', 'materials', 'products', 'goods', 'totalMaterials', 'totalProducts', 'totalGoods', 'grandTotal'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        $employees = Employee::orderBy('name')->get();
        return view('warehouses.edit', compact('warehouse', 'employees'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'code' => 'required',
            'name' => 'required',
            'manager' => 'required',
        ]);

        // Check if warehouse code exists in other warehouses (active or hidden)
        $existingWarehouse = Warehouse::where('code', $request->code)
            ->where('id', '!=', $warehouse->id)
            ->where(function($query) {
                $query->where('status', 'active')
                    ->orWhere('is_hidden', true);
            })
            ->first();

        if ($existingWarehouse) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'Mã kho hàng đã tồn tại']);
        }

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $warehouse->toArray();

        $warehouse->update($request->all());

        // Ghi nhật ký cập nhật kho hàng
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'warehouses',
                'Cập nhật kho hàng: ' . $warehouse->name,
                $oldData,
                $warehouse->toArray()
            );
        }

        return redirect()->route('warehouses.index')
            ->with('success', 'Kho hàng đã được cập nhật thành công.');
    }

    /**
     * Check inventory for a warehouse
     */
    public function checkInventory($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);

            // Tính tổng số lượng tất cả items trong kho này
            $totalQuantity = WarehouseMaterial::where('warehouse_id', $id)->sum('quantity');

            return response()->json([
                'hasInventory' => $totalQuantity > 0,
                'totalQuantity' => $totalQuantity
            ]);
        } catch (\Exception $e) {
            Log::error('Check warehouse inventory error: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi kiểm tra tồn kho: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Request $request, Warehouse $warehouse)
    {
        try {
            $action = $request->input('action', 'delete');
            $oldData = $warehouse->toArray();
            $warehouseName = $warehouse->name;
            $warehouseCode = $warehouse->code;

            if ($action === 'hide') {
                // Ẩn warehouse
                $warehouse->update(['is_hidden' => true]);
                $message = 'Kho hàng đã được ẩn thành công.';

                // Ghi nhật ký
                if (Auth::check()) {
                    UserLog::logActivity(
                        Auth::id(),
                        'update',
                        'warehouses',
                        'Ẩn kho hàng: ' . $warehouseName . ' (' . $warehouseCode . ')',
                        $oldData,
                        array_merge($oldData, ['is_hidden' => true])
                    );
                }
            } else {
                // Kiểm tra xem kho có vật tư không
                if ($warehouse->warehouseMaterials()->exists()) {
                    return redirect()->back()
                        ->with('error', 'Không thể xóa kho này vì còn vật tư tồn kho.');
                }

                // Cập nhật thông tin xóa
                $warehouse->update([
                    'status' => 'deleted',
                    'deleted_by' => Auth::id(),
                    'delete_reason' => $request->input('delete_reason', 'Xóa bởi người dùng')
                ]);

                // // Thực hiện soft delete
                // $warehouse->delete();

                $message = 'Kho hàng đã được xóa thành công.';

                // Ghi nhật ký
                if (Auth::check()) {
                    UserLog::logActivity(
                        Auth::id(),
                        'delete',
                        'warehouses',
                        'Xóa kho hàng: ' . $warehouseName . ' (' . $warehouseCode . ')',
                        $oldData,
                        $warehouse->toArray()
                    );
                }
            }

            return redirect()->route('warehouses.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Warehouse delete/hide error: ' . $e->getMessage());

            return redirect()->route('warehouses.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to get list of warehouses for search/filter
     */
    public function apiSearch(Request $request)
    {
        try {
            $warehouses = Warehouse::where('status', 'active')
                ->where('is_hidden', false)
                ->select('id', 'name', 'code', 'address')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'warehouses' => $warehouses
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in warehouse apiSearch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách kho',
                'data' => [
                    'warehouses' => []
                ]
            ], 500);
        }
    }

    /**
     * Get materials for a specific warehouse
     */
    public function getMaterials($warehouseId, Request $request)
    {
        try {
            $searchTerm = $request->input('term');

            // Get the warehouse
            $warehouse = Warehouse::findOrFail($warehouseId);

            // Query for materials in this warehouse
            // Chỉ lấy các mục có item_type = 'material', nếu trường này đã tồn tại
            $query = WarehouseMaterial::where('warehouse_id', $warehouseId);

            // Chỉ áp dụng điều kiện item_type nếu cột này đã tồn tại trong bảng
            if (Schema::hasColumn('warehouse_materials', 'item_type')) {
                $query->where('item_type', 'material');
            }

            $query->with('material');

            // Add search filter if term is provided
            if ($searchTerm) {
                $query->whereHas('material', function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(category) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                        ->orWhereRaw('LOWER(serial) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
                });
            }

            // Get warehouse materials with their related material information
            $warehouseMaterials = $query->get();

            // Format the response
            $materials = $warehouseMaterials->map(function ($warehouseMaterial) {
                $material = $warehouseMaterial->material;
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'category' => $material->category,
                    'serial' => $material->serial ?? null,
                    'stock_quantity' => $warehouseMaterial->quantity,
                ];
            });

            return response()->json($materials);
        } catch (\Exception $e) {
            Log::error('Warehouse materials error: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi lấy danh sách vật tư: ' . $e->getMessage()
            ], 500);
        }
    }


}
