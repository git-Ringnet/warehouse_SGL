<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use App\Models\InventoryImport;
use App\Models\InventoryImportMaterial;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Assembly;
use App\Models\AssemblyMaterial;
use App\Models\AssemblyProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Hiển thị báo cáo tổng hợp xuất nhập tồn kho (trang chính)
     */
    public function index(Request $request)
    {
        // Lấy tham số lọc
        $dateFrom = $request->get('from_date');
        $dateTo = $request->get('to_date');
        $search = $request->get('search');
        $category = $request->get('category_filter');

        // Nếu không có ngày, mặc định là tháng hiện tại
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        // Lấy dữ liệu báo cáo (chỉ vật tư)
        $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category);

        // Thống kê tổng quan
        $stats = $this->getInventoryStats($dateFrom, $dateTo);

        // Dữ liệu cho biểu đồ
        $chartData = $this->getChartData($dateFrom, $dateTo);

        // Lấy danh sách categories để filter
        $categories = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        return view('reports.index', compact(
            'reportData',
            'stats',
            'chartData',
            'dateFrom',
            'dateTo',
            'search',
            'category',
            'categories'
        ));
    }

    /**
     * Ajax: Lấy dữ liệu báo cáo theo bộ lọc
     */
    public function filterAjax(Request $request)
    {
        try {
            // Lấy tham số lọc
            $dateFrom = $request->get('from_date');
            $dateTo = $request->get('to_date');
            $search = $request->get('search');
            $category = $request->get('category_filter');

            // Debug log
            \Illuminate\Support\Facades\Log::info('Filter request:', [
                'from_date' => $dateFrom,
                'to_date' => $dateTo,
                'search' => $search,
                'category_filter' => $category
            ]);

            // Nếu không có ngày, mặc định là tháng hiện tại
            if (!$dateFrom) {
                $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
            }
            if (!$dateTo) {
                $dateTo = Carbon::now()->format('Y-m-d');
            }

            // Lấy dữ liệu báo cáo
            $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category);

            // Lấy thống kê tổng quan theo filter
            $stats = $this->getFilteredInventoryStats($dateFrom, $dateTo, $search, $category);

            // Lấy dữ liệu biểu đồ theo filter
            $chartData = $this->getFilteredChartData($dateFrom, $dateTo, $search, $category);

            // Render HTML table
            $tableHtml = view('reports.partials.report-table', compact('reportData', 'dateFrom', 'dateTo'))->render();

            return response()->json([
                'success' => true,
                'html' => $tableHtml,
                'count' => $reportData->count(),
                'total_opening' => $reportData->sum('opening_stock'),
                'total_imports' => $reportData->sum('imports'),
                'total_exports' => $reportData->sum('exports'),
                'total_closing' => $reportData->sum('closing_stock'),
                'total_current' => $reportData->sum('current_stock'),
                'stats' => $stats,
                'chartData' => $chartData,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Filter Ajax Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lọc dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị báo cáo tổng hợp xuất nhập tồn kho (chi tiết)
     */
    public function inventoryReport(Request $request)
    {
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();

        // Lấy tham số lọc
        $warehouseId = $request->get('warehouse_id');
        $itemType = $request->get('item_type', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Nếu không có ngày, mặc định là tháng hiện tại
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        // Lấy dữ liệu báo cáo
        $reportData = $this->getInventoryReportData($warehouseId, $itemType, $dateFrom, $dateTo);

        return view('reports.inventory', compact(
            'warehouses',
            'reportData',
            'warehouseId',
            'itemType',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Lấy dữ liệu báo cáo vật tư (gộp theo mã vật tư)
     */
    private function getMaterialsReportData($dateFrom, $dateTo, $search = null, $category = null)
    {
        $reportData = [];

        // Lấy danh sách vật tư
        $materialsQuery = Material::where('status', 'active')
            ->where('is_hidden', false);

        // Áp dụng tìm kiếm
        if ($search) {
            $materialsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        // Áp dụng lọc theo category
        if ($category) {
            $materialsQuery->where('category', $category);
        }

        $materials = $materialsQuery->get();

        foreach ($materials as $material) {
            // Tổng hợp tất cả các kho cho vật tư này
            $totalOpeningStock = 0;
            $totalImports = 0;
            $totalExports = 0;
            $totalCurrentStock = 0;

            $warehouses = Warehouse::where('status', 'active')
                ->where('is_hidden', false)
                ->get();

            foreach ($warehouses as $warehouse) {
                // Tồn đầu kỳ
                $openingStock = $this->getOpeningStock($material->id, 'material', $warehouse->id, $dateFrom);
                
                // Nhập trong kỳ
                $imports = $this->getImportsInPeriod($material->id, 'material', $warehouse->id, $dateFrom, $dateTo);
                
                // Xuất trong kỳ
                $exports = $this->getExportsInPeriod($material->id, 'material', $warehouse->id, $dateFrom, $dateTo);
                
                // Tồn cuối kỳ (tính theo công thức: Tồn đầu + Nhập - Xuất)
                $calculatedClosingStock = $openingStock + $imports - $exports;
                
                // Tồn kho hiện tại thực tế
                $currentStock = $this->getCurrentStock($material->id, 'material', $warehouse->id);

                $totalOpeningStock += $openingStock;
                $totalImports += $imports;
                $totalExports += $exports;
                $totalCurrentStock += $currentStock;
            }

            // Tính tồn cuối kỳ theo công thức
            $calculatedClosingStock = $totalOpeningStock + $totalImports - $totalExports;

            // Kiểm tra xem vật tư có hoạt động thực sự liên quan đến kỳ được chọn không
            $hasActivityBeforeOrInPeriod = false;
            
            // Có nhập/xuất trong kỳ được chọn
            if ($totalImports > 0 || $totalExports > 0) {
                $hasActivityBeforeOrInPeriod = true;
            }
            // Hoặc có nhập/xuất trước kỳ được chọn (tạo ra tồn đầu kỳ thực sự)
            elseif ($totalOpeningStock > 0) {
                // Kiểm tra có giao dịch nào trước ngày bắt đầu không
                foreach ($warehouses as $warehouse) {
                    $importsBeforeDate = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                        ->where('inventory_import_materials.material_id', $material->id)
                        ->where('inventory_import_materials.warehouse_id', $warehouse->id)
                        ->whereDate('inventory_imports.import_date', '<', $dateFrom)
                        ->exists();
                        
                    $exportsBeforeDate = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                        ->where('dispatch_items.item_id', $material->id)
                        ->where('dispatch_items.warehouse_id', $warehouse->id)
                        ->whereIn('dispatches.status', ['approved', 'completed'])
                        ->whereDate('dispatches.dispatch_date', '<', $dateFrom)
                        ->exists();
                        
                    if ($importsBeforeDate || $exportsBeforeDate) {
                        $hasActivityBeforeOrInPeriod = true;
                        break;
                    }
                }
            }
            
            if ($hasActivityBeforeOrInPeriod) {
                $reportData[] = [
                    'item_type' => 'material',
                    'item_type_label' => 'Vật tư',
                    'item_id' => $material->id,
                    'item_code' => $material->code ?? 'N/A',
                    'item_name' => $material->name ?? 'N/A',
                    'item_unit' => $material->unit ?? 'N/A',
                    'item_category' => $material->category ?? 'N/A',
                    'opening_stock' => $totalOpeningStock,
                    'imports' => $totalImports,
                    'exports' => $totalExports,
                    'closing_stock' => $calculatedClosingStock, // Sử dụng tồn cuối kỳ tính theo công thức
                    'current_stock' => $totalCurrentStock, // Tồn kho hiện tại thực tế
                ];
            }
        }

        return collect($reportData)->sortBy(['item_code']);
    }

    /**
     * Lấy dữ liệu báo cáo xuất nhập tồn
     */
    private function getInventoryReportData($warehouseId, $itemType, $dateFrom, $dateTo)
    {
        $reportData = [];

        // Định nghĩa các loại item cần báo cáo
        $itemTypes = $itemType === 'all' ? ['material', 'product', 'good'] : [$itemType];

        foreach ($itemTypes as $type) {
            $items = $this->getItemsByType($type);

            foreach ($items as $item) {
                $warehousesToCheck = $warehouseId ? [$warehouseId] : Warehouse::where('status', 'active')->where('is_hidden', false)->pluck('id')->toArray();

                foreach ($warehousesToCheck as $whId) {
                    $warehouse = Warehouse::find($whId);
                    if (!$warehouse) continue;

                    // Tồn đầu kỳ
                    $openingStock = $this->getOpeningStock($item->id, $type, $whId, $dateFrom);

                    // Nhập trong kỳ
                    $imports = $this->getImportsInPeriod($item->id, $type, $whId, $dateFrom, $dateTo);

                    // Xuất trong kỳ
                    $exports = $this->getExportsInPeriod($item->id, $type, $whId, $dateFrom, $dateTo);

                    // Tồn cuối kỳ
                    $currentStock = $this->getCurrentStock($item->id, $type, $whId);

                    // Chỉ hiển thị nếu có giao dịch hoặc tồn kho
                    if ($openingStock > 0 || $imports > 0 || $exports > 0 || $currentStock > 0) {
                        $reportData[] = [
                            'item_type' => $type,
                            'item_type_label' => $this->getItemTypeLabel($type),
                            'item_id' => $item->id,
                            'item_code' => $item->code ?? 'N/A',
                            'item_name' => $item->name ?? 'N/A',
                            'item_unit' => $item->unit ?? 'N/A',
                            'warehouse_id' => $whId,
                            'warehouse_name' => $warehouse->name,
                            'opening_stock' => $openingStock,
                            'imports' => $imports,
                            'exports' => $exports,
                            'closing_stock' => $currentStock,
                            'calculated_closing' => $openingStock + $imports - $exports,
                        ];
                    }
                }
            }
        }

        return collect($reportData)->sortBy(['item_type', 'item_name', 'warehouse_name']);
    }

    /**
     * Lấy items theo loại
     */
    private function getItemsByType($type)
    {
        switch ($type) {
            case 'material':
                return Material::where('status', 'active')
                    ->where('is_hidden', false)
                    ->get();
            case 'product':
                return Product::where('status', 'active')
                    ->where('is_hidden', false)
                    ->get();
            case 'good':
                return Good::where('status', 'active')
                    ->get();
            default:
                return collect();
        }
    }

    /**
     * Lấy tồn đầu kỳ
     */
    private function getOpeningStock($itemId, $itemType, $warehouseId, $dateFrom)
    {
        // Tồn kho hiện tại
        $currentStock = $this->getCurrentStock($itemId, $itemType, $warehouseId);

        // Trừ đi các giao dịch từ ngày bắt đầu đến hiện tại
        $importsAfter = $this->getImportsAfterDate($itemId, $itemType, $warehouseId, $dateFrom);
        $exportsAfter = $this->getExportsAfterDate($itemId, $itemType, $warehouseId, $dateFrom);

        return $currentStock - $importsAfter + $exportsAfter;
    }

    /**
     * Lấy nhập kho trong kỳ
     */
    private function getImportsInPeriod($itemId, $itemType, $warehouseId, $dateFrom, $dateTo)
    {
        return InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->where('inventory_import_materials.material_id', $itemId)
            ->where('inventory_import_materials.item_type', $itemType)
            ->where('inventory_import_materials.warehouse_id', $warehouseId)
            ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
            ->whereDate('inventory_imports.import_date', '<=', $dateTo)
            ->sum('inventory_import_materials.quantity');
    }

    /**
     * Lấy xuất kho trong kỳ
     */
    private function getExportsInPeriod($itemId, $itemType, $warehouseId, $dateFrom, $dateTo)
    {
        $directExports = 0;
        $indirectExports = 0;
        
        if ($itemType === 'material') {
            // 1. Xuất trực tiếp vật tư
            $directExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->where('dispatch_items.item_id', $itemId)
                ->where('dispatch_items.item_type', 'material')
                ->where('dispatch_items.warehouse_id', $warehouseId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
                ->sum('dispatch_items.quantity');
            
            // 2. Xuất gián tiếp qua việc xuất thành phẩm (assembly)
            // Lấy tất cả phiếu xuất thành phẩm trong kỳ
            $productExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->where('dispatch_items.item_type', 'product')
                ->where('dispatch_items.warehouse_id', $warehouseId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
                ->get();
            
            // Tính toán vật tư đã sử dụng từ việc xuất thành phẩm
            foreach ($productExports as $productExport) {
                // Tìm TẤT CẢ assembly materials cho thành phẩm này (không chỉ first())
                $materialUsagesInAssembly = AssemblyMaterial::join('assembly_products', 'assembly_materials.assembly_id', '=', 'assembly_products.assembly_id')
                    ->where('assembly_products.product_id', $productExport->item_id)
                    ->where('assembly_materials.material_id', $itemId)
                    ->get(); // Đổi từ first() thành get()
                
                foreach ($materialUsagesInAssembly as $materialUsage) {
                    // Tính tỷ lệ vật tư sử dụng cho 1 thành phẩm và nhân với số lượng xuất
                    $materialPerProduct = $materialUsage->quantity;
                    $indirectExports += $materialPerProduct * $productExport->quantity;
                }
            }
        } else {
            // Với product, good thì chỉ tính xuất trực tiếp
            $directExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->where('dispatch_items.item_id', $itemId)
                ->where('dispatch_items.item_type', $itemType)
                ->where('dispatch_items.warehouse_id', $warehouseId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
                ->sum('dispatch_items.quantity');
        }
        
        return $directExports + $indirectExports;
    }

    /**
     * Lấy nhập kho từ ngày cụ thể
     */
    private function getImportsAfterDate($itemId, $itemType, $warehouseId, $date)
    {
        return InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->where('inventory_import_materials.material_id', $itemId)
            ->where('inventory_import_materials.item_type', $itemType)
            ->where('inventory_import_materials.warehouse_id', $warehouseId)
            ->whereDate('inventory_imports.import_date', '>=', $date)
            ->sum('inventory_import_materials.quantity');
    }

    /**
     * Lấy xuất kho từ ngày cụ thể
     */
    private function getExportsAfterDate($itemId, $itemType, $warehouseId, $date)
    {
        $directExports = 0;
        $indirectExports = 0;
        
        if ($itemType === 'material') {
            // 1. Xuất trực tiếp vật tư
            $directExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->where('dispatch_items.item_id', $itemId)
                ->where('dispatch_items.item_type', 'material')
                ->where('dispatch_items.warehouse_id', $warehouseId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->whereDate('dispatches.dispatch_date', '>=', $date)
                ->sum('dispatch_items.quantity');
            
            // 2. Xuất gián tiếp qua việc xuất thành phẩm (assembly)
            $productExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->where('dispatch_items.item_type', 'product')
                ->where('dispatch_items.warehouse_id', $warehouseId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->whereDate('dispatches.dispatch_date', '>=', $date)
                ->get();
            
            foreach ($productExports as $productExport) {
                $materialUsagesInAssembly = AssemblyMaterial::join('assembly_products', 'assembly_materials.assembly_id', '=', 'assembly_products.assembly_id')
                    ->where('assembly_products.product_id', $productExport->item_id)
                    ->where('assembly_materials.material_id', $itemId)
                    ->get();
                
                foreach ($materialUsagesInAssembly as $materialUsage) {
                    $materialPerProduct = $materialUsage->quantity;
                    $indirectExports += $materialPerProduct * $productExport->quantity;
                }
            }
        } else {
            $directExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->where('dispatch_items.item_id', $itemId)
                ->where('dispatch_items.item_type', $itemType)
                ->where('dispatch_items.warehouse_id', $warehouseId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->whereDate('dispatches.dispatch_date', '>=', $date)
                ->sum('dispatch_items.quantity');
        }
        
        return $directExports + $indirectExports;
    }

    /**
     * Lấy tồn kho hiện tại
     */
    private function getCurrentStock($itemId, $itemType, $warehouseId)
    {
        $warehouseMaterial = WarehouseMaterial::where('material_id', $itemId)
            ->where('item_type', $itemType)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $warehouseMaterial ? $warehouseMaterial->quantity : 0;
    }

    /**
     * Lấy label cho loại item
     */
    private function getItemTypeLabel($type)
    {
        return match($type) {
            'material' => 'Vật tư',
            'product' => 'Thành phẩm',
            'good' => 'Hàng hóa',
            default => 'Không xác định'
        };
    }

    /**
     * Xuất báo cáo ra Excel
     */
    public function exportInventoryReport(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $itemType = $request->get('item_type', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Nếu không có ngày, mặc định là tháng hiện tại
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        $reportData = $this->getInventoryReportData($warehouseId, $itemType, $dateFrom, $dateTo);

        // Tạo Excel export (sẽ implement sau)
        return response()->json([
            'success' => true,
            'message' => 'Tính năng xuất Excel sẽ được phát triển sau.',
            'data_count' => $reportData->count()
        ]);
    }

    /**
     * Lấy thống kê tổng quan theo filter
     */
    private function getFilteredInventoryStats($dateFrom, $dateTo, $search = null, $category = null)
    {
        // Tạo query base cho materials
        $materialsQuery = Material::where('status', 'active')
            ->where('is_hidden', false);

        // Áp dụng tìm kiếm
        if ($search) {
            $materialsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        // Áp dụng lọc theo category
        if ($category) {
            $materialsQuery->where('category', $category);
        }

        // Tổng số vật tư có tồn kho hoặc có giao dịch trong kỳ (đã filter)
        $materialIds = $materialsQuery->pluck('id')->toArray();
        
        $materialsWithActivity = Material::whereIn('id', $materialIds)
            ->where(function($query) use ($dateFrom, $dateTo) {
                // Có nhập trong kỳ
                $query->whereExists(function($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->select(DB::raw(1))
                        ->from('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                        ->whereRaw('inventory_import_materials.material_id = materials.id')
                        ->where('inventory_import_materials.item_type', 'material')
                        ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
                        ->whereDate('inventory_imports.import_date', '<=', $dateTo);
                })
                // Hoặc có xuất trong kỳ
                ->orWhereExists(function($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->select(DB::raw(1))
                        ->from('dispatch_items')
                        ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                        ->whereRaw('dispatch_items.item_id = materials.id')
                        ->where('dispatch_items.item_type', 'material')
                        ->whereIn('dispatches.status', ['approved', 'completed'])
                        ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                        ->whereDate('dispatches.dispatch_date', '<=', $dateTo);
                })
                // Hoặc có giao dịch trước kỳ này (tạo ra tồn đầu kỳ)
                ->orWhereExists(function($subQuery) use ($dateFrom) {
                    $subQuery->select(DB::raw(1))
                        ->from('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                        ->whereRaw('inventory_import_materials.material_id = materials.id')
                        ->where('inventory_import_materials.item_type', 'material')
                        ->whereDate('inventory_imports.import_date', '<', $dateFrom);
                })
                ->orWhereExists(function($subQuery) use ($dateFrom) {
                    $subQuery->select(DB::raw(1))
                        ->from('dispatch_items')
                        ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                        ->whereRaw('dispatch_items.item_id = materials.id')
                        ->where('dispatch_items.item_type', 'material')
                        ->whereIn('dispatches.status', ['approved', 'completed'])
                        ->whereDate('dispatches.dispatch_date', '<', $dateFrom);
                });
            })
            ->count();

        // Số lượng danh mục vật tư (categories) - áp dụng filter
        $totalCategories = $materialsQuery->whereNotNull('category')
            ->distinct()
            ->count('category');

        // Nhập vật tư trong kỳ (chỉ materials đã filter)
        $imports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereIn('inventory_import_materials.material_id', $materialIds)
            ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
            ->whereDate('inventory_imports.import_date', '<=', $dateTo)
            ->sum('inventory_import_materials.quantity');

        // Xuất vật tư trong kỳ (bao gồm cả xuất gián tiếp qua assembly)
        $exports = 0;
        foreach ($materialIds as $materialId) {
            // Gộp tất cả warehouse để tính tổng
            $warehouses = Warehouse::where('status', 'active')->where('is_hidden', false)->get();
            foreach ($warehouses as $warehouse) {
                $exports += $this->getExportsInPeriod($materialId, 'material', $warehouse->id, $dateFrom, $dateTo);
            }
        }

        // Tính phần trăm thay đổi so với kỳ trước (30 ngày trước)
        $previousFromDate = Carbon::parse($dateFrom)->subDays(30)->format('Y-m-d');
        $previousToDate = Carbon::parse($dateTo)->subDays(30)->format('Y-m-d');

        $previousImports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereIn('inventory_import_materials.material_id', $materialIds)
            ->whereDate('inventory_imports.import_date', '>=', $previousFromDate)
            ->whereDate('inventory_imports.import_date', '<=', $previousToDate)
            ->sum('inventory_import_materials.quantity');

        // Tính xuất kỳ trước cũng bao gồm assembly
        $previousExports = 0;
        foreach ($materialIds as $materialId) {
            $warehouses = Warehouse::where('status', 'active')->where('is_hidden', false)->get();
            foreach ($warehouses as $warehouse) {
                $previousExports += $this->getExportsInPeriod($materialId, 'material', $warehouse->id, $previousFromDate, $previousToDate);
            }
        }

        $importsChange = $previousImports > 0 ? (($imports - $previousImports) / $previousImports) * 100 : 0;
        $exportsChange = $previousExports > 0 ? (($exports - $previousExports) / $previousExports) * 100 : 0;

        return [
            'total_items' => $materialsWithActivity,
            'total_categories' => $totalCategories,
            'imports' => $imports,
            'exports' => $exports,
            'imports_change' => round($importsChange, 1),
            'exports_change' => round($exportsChange, 1),
        ];
    }

    /**
     * Lấy thống kê tổng quan
     */
    private function getInventoryStats($dateFrom, $dateTo)
    {
        // Tổng số vật tư có tồn kho hoặc có giao dịch trong kỳ
        $materialsWithActivity = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->where(function($query) use ($dateFrom, $dateTo) {
                // Có tồn kho hiện tại
                $query->whereExists(function($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('warehouse_materials')
                        ->whereRaw('warehouse_materials.material_id = materials.id')
                        ->where('warehouse_materials.item_type', 'material')
                        ->where('warehouse_materials.quantity', '>', 0);
                })
                // Hoặc có nhập trong kỳ
                ->orWhereExists(function($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->select(DB::raw(1))
                        ->from('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                        ->whereRaw('inventory_import_materials.material_id = materials.id')
                        ->where('inventory_import_materials.item_type', 'material')
                        ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
                        ->whereDate('inventory_imports.import_date', '<=', $dateTo);
                })
                // Hoặc có xuất trong kỳ
                ->orWhereExists(function($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->select(DB::raw(1))
                        ->from('dispatch_items')
                        ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                        ->whereRaw('dispatch_items.item_id = materials.id')
                        ->where('dispatch_items.item_type', 'material')
                        ->whereIn('dispatches.status', ['approved', 'completed'])
                        ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                        ->whereDate('dispatches.dispatch_date', '<=', $dateTo);
                });
            })
            ->count();

        // Số lượng danh mục vật tư (categories)
        $totalCategories = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNotNull('category')
            ->distinct()
            ->count('category');

        // Nhập vật tư trong kỳ (chỉ materials)
        $imports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
            ->whereDate('inventory_imports.import_date', '<=', $dateTo)
            ->sum('inventory_import_materials.quantity');

        // Xuất vật tư trong kỳ (bao gồm cả xuất gián tiếp qua assembly)
        $exports = 0;
        $materials = Material::where('status', 'active')->where('is_hidden', false)->get();
        foreach ($materials as $material) {
            $warehouses = Warehouse::where('status', 'active')->where('is_hidden', false)->get();
            foreach ($warehouses as $warehouse) {
                $exports += $this->getExportsInPeriod($material->id, 'material', $warehouse->id, $dateFrom, $dateTo);
            }
        }

        // Tính phần trăm thay đổi so với kỳ trước (30 ngày trước)
        $previousFromDate = Carbon::parse($dateFrom)->subDays(30)->format('Y-m-d');
        $previousToDate = Carbon::parse($dateTo)->subDays(30)->format('Y-m-d');

        $previousImports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereDate('inventory_imports.import_date', '>=', $previousFromDate)
            ->whereDate('inventory_imports.import_date', '<=', $previousToDate)
            ->sum('inventory_import_materials.quantity');

        $previousExports = 0;
        foreach ($materials as $material) {
            $warehouses = Warehouse::where('status', 'active')->where('is_hidden', false)->get();
            foreach ($warehouses as $warehouse) {
                $previousExports += $this->getExportsInPeriod($material->id, 'material', $warehouse->id, $previousFromDate, $previousToDate);
            }
        }

        $importsChange = $previousImports > 0 ? (($imports - $previousImports) / $previousImports) * 100 : 0;
        $exportsChange = $previousExports > 0 ? (($exports - $previousExports) / $previousExports) * 100 : 0;

        return [
            'total_items' => $materialsWithActivity,
            'total_categories' => $totalCategories,
            'imports' => $imports,
            'exports' => $exports,
            'imports_change' => round($importsChange, 1),
            'exports_change' => round($exportsChange, 1),
        ];
    }

    /**
     * Lấy dữ liệu cho biểu đồ theo filter
     */
    private function getFilteredChartData($dateFrom, $dateTo, $search = null, $category = null)
    {
        // Tạo query base cho materials
        $materialsQuery = Material::where('status', 'active')
            ->where('is_hidden', false);

        // Áp dụng tìm kiếm
        if ($search) {
            $materialsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        // Áp dụng lọc theo category
        if ($category) {
            $materialsQuery->where('category', $category);
        }

        $materialIds = $materialsQuery->pluck('id')->toArray();

        // Lấy dữ liệu 12 tháng gần nhất (áp dụng filter)
        $months = [];
        $importsData = [];
        $exportsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $months[] = $monthStart->format('M');
            
            // Nhập theo tháng (áp dụng filter)
            $monthImports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                ->whereIn('inventory_import_materials.material_id', $materialIds)
                ->whereDate('inventory_imports.import_date', '>=', $monthStart)
                ->whereDate('inventory_imports.import_date', '<=', $monthEnd)
                ->sum('inventory_import_materials.quantity');
            
            // Xuất theo tháng (áp dụng filter)
            $monthExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->where('dispatch_items.item_type', 'material')
                ->whereIn('dispatch_items.item_id', $materialIds)
                ->whereDate('dispatches.dispatch_date', '>=', $monthStart)
                ->whereDate('dispatches.dispatch_date', '<=', $monthEnd)
                ->sum('dispatch_items.quantity');
            
            $importsData[] = $monthImports;
            $exportsData[] = $monthExports;
        }

        // Top 5 vật tư có tồn kho cao nhất (áp dụng filter)
        $topItems = WarehouseMaterial::select('material_id', DB::raw('SUM(quantity) as total_stock'))
            ->whereIn('material_id', $materialIds)
            ->groupBy('material_id')
            ->orderBy('total_stock', 'desc')
            ->limit(5)
            ->get();

        $topItemsData = [];
        $topItemsLabels = [];

        foreach ($topItems as $item) {
            $material = Material::find($item->material_id);
            if ($material) {
                $topItemsLabels[] = $material->name ?? 'N/A';
                $topItemsData[] = $item->total_stock;
            }
        }

        return [
            'months' => $months,
            'imports_data' => $importsData,
            'exports_data' => $exportsData,
            'top_items_labels' => $topItemsLabels,
            'top_items_data' => $topItemsData,
        ];
    }

    /**
     * Lấy dữ liệu cho biểu đồ
     */
    private function getChartData($dateFrom, $dateTo)
    {
        // Lấy dữ liệu 12 tháng gần nhất
        $months = [];
        $importsData = [];
        $exportsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $months[] = $monthStart->format('M');
            
            // Nhập theo tháng
            $monthImports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                ->whereDate('inventory_imports.import_date', '>=', $monthStart)
                ->whereDate('inventory_imports.import_date', '<=', $monthEnd)
                ->sum('inventory_import_materials.quantity');
            
            // Xuất theo tháng (chỉ materials)
            $monthExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->where('dispatch_items.item_type', 'material')
                ->whereDate('dispatches.dispatch_date', '>=', $monthStart)
                ->whereDate('dispatches.dispatch_date', '<=', $monthEnd)
                ->sum('dispatch_items.quantity');
            
            $importsData[] = $monthImports;
            $exportsData[] = $monthExports;
        }

        // Top 5 vật tư có tồn kho cao nhất (chỉ materials)
        $topItems = WarehouseMaterial::select('material_id', DB::raw('SUM(quantity) as total_stock'))
            ->groupBy('material_id')
            ->orderBy('total_stock', 'desc')
            ->limit(5)
            ->get();

        $topItemsData = [];
        $topItemsLabels = [];

        foreach ($topItems as $item) {
            $material = Material::find($item->material_id);
            if ($material) {
                $topItemsLabels[] = $material->name ?? 'N/A';
                $topItemsData[] = $item->total_stock;
            }
        }

        return [
            'months' => $months,
            'imports_data' => $importsData,
            'exports_data' => $exportsData,
            'top_items_labels' => $topItemsLabels,
            'top_items_data' => $topItemsData,
        ];
    }

    /**
     * Báo cáo theo thời gian thực
     */
    public function realtimeInventory(Request $request)
    {
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();

        $warehouseId = $request->get('warehouse_id');
        $itemType = $request->get('item_type', 'all');

        $data = [];
        $itemTypes = $itemType === 'all' ? ['material', 'product', 'good'] : [$itemType];

        foreach ($itemTypes as $type) {
            $items = $this->getItemsByType($type);

            foreach ($items as $item) {
                $warehousesToCheck = $warehouseId ? [$warehouseId] : $warehouses->pluck('id')->toArray();

                foreach ($warehousesToCheck as $whId) {
                    $warehouse = Warehouse::find($whId);
                    if (!$warehouse) continue;

                    $currentStock = $this->getCurrentStock($item->id, $type, $whId);

                    if ($currentStock > 0) {
                        $data[] = [
                            'item_type' => $type,
                            'item_type_label' => $this->getItemTypeLabel($type),
                            'item_id' => $item->id,
                            'item_code' => $item->code ?? 'N/A',
                            'item_name' => $item->name ?? 'N/A',
                            'item_unit' => $item->unit ?? 'N/A',
                            'warehouse_id' => $whId,
                            'warehouse_name' => $warehouse->name,
                            'current_stock' => $currentStock,
                        ];
                    }
                }
            }
        }

        $realtimeData = collect($data)->sortBy(['item_type', 'item_name', 'warehouse_name']);

        return view('reports.realtime-inventory', compact(
            'warehouses',
            'realtimeData',
            'warehouseId',
            'itemType'
        ));
    }

    /**
     * Xuất báo cáo Excel
     */
    public function exportExcel(Request $request)
    {
        $dateFrom = $request->get('from_date');
        $dateTo = $request->get('to_date');
        $search = $request->get('search');
        $category = $request->get('category_filter');

        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category);

        // Tạo CSV content
        $filename = 'bao_cao_vat_tu_' . date('Y_m_d_H_i_s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($reportData, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'STT',
                'Mã vật tư',
                'Tên vật tư', 
                'Đơn vị',
                'Danh mục',
                'Tồn đầu kỳ',
                'Nhập trong kỳ',
                'Xuất trong kỳ', 
                'Tồn cuối kỳ',
                'Chênh lệch'
            ]);

            // Data
            foreach ($reportData as $index => $item) {
                $difference = $item['closing_stock'] - $item['calculated_closing'];
                fputcsv($file, [
                    $index + 1,
                    $item['item_code'],
                    $item['item_name'],
                    $item['item_unit'],
                    $item['item_category'],
                    $item['opening_stock'],
                    $item['imports'],
                    $item['exports'],
                    $item['closing_stock'],
                    $difference
                ]);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, [
                'TỔNG CỘNG',
                '',
                '',
                '',
                '',
                $reportData->sum('opening_stock'),
                $reportData->sum('imports'),
                $reportData->sum('exports'),
                $reportData->sum('closing_stock'),
                $reportData->sum('closing_stock') - $reportData->sum('calculated_closing')
            ]);

            // Info
            fputcsv($file, []);
            fputcsv($file, ['Thời gian:', "Từ " . Carbon::parse($dateFrom)->format('d/m/Y') . " đến " . Carbon::parse($dateTo)->format('d/m/Y')]);
            fputcsv($file, ['Xuất lúc:', Carbon::now()->format('H:i:s d/m/Y')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Xuất báo cáo PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $dateFrom = $request->get('from_date');
            $dateTo = $request->get('to_date');
            $search = $request->get('search');
            $category = $request->get('category_filter');

            if (!$dateFrom) {
                $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
            }
            if (!$dateTo) {
                $dateTo = Carbon::now()->format('Y-m-d');
            }

            $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category);
            $stats = $this->getFilteredInventoryStats($dateFrom, $dateTo, $search, $category);

            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('reports.pdf-template', compact(
                'reportData',
                'stats', 
                'dateFrom',
                'dateTo',
                'search',
                'category'
            ));

            // Set paper size and orientation
            $pdf->setPaper('A4', 'landscape');
            
            // Set PDF options
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'debugKeepTemp' => false,
                'debugCss' => false,
                'debugLayout' => false,
                'debugLayoutLines' => false,
                'debugLayoutBlocks' => false,
                'debugLayoutInline' => false,
                'debugLayoutPaddingBox' => false,
            ]);

            $filename = 'bao_cao_vat_tu_' . date('Y_m_d_H_i_s') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF Export Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất PDF: ' . $e->getMessage(),
                'suggestion' => 'Vui lòng thử lại hoặc sử dụng tính năng xuất Excel.'
            ], 500);
        }
    }


} 