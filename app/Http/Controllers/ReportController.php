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
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Parse date từ nhiều format khác nhau sang Y-m-d
     * Hỗ trợ: d/m/Y, Y-m-d
     */
    private function parseDateToYmd(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Nếu đã là format Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Thử parse từ d/m/Y
        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Hiển thị báo cáo tổng hợp xuất nhập tồn kho (trang chính)
     */
    public function index(Request $request)
    {
        // Lấy tham số lọc và chuyển đổi định dạng ngày
        $dateFrom = $this->parseDateToYmd($request->get('from_date'));
        $dateTo = $this->parseDateToYmd($request->get('to_date'));
        $search = $request->get('search');
        $category = $request->get('category_filter');
        $timeFilter = $request->get('time_filter');

        // Xử lý filter thời gian
        if ($timeFilter && !$dateFrom && !$dateTo) {
            $today = now();
            
            switch ($timeFilter) {
                case 'quarter':
                    // Tính quý hiện tại
                    $currentQuarter = ceil(($today->month) / 3);
                    $quarterStart = $today->copy()->startOfYear()->addMonths(($currentQuarter - 1) * 3);
                    $quarterEnd = $quarterStart->copy()->endOfQuarter();
                    
                    $dateFrom = $quarterStart->format('Y-m-d');
                    $dateTo = $quarterEnd->format('Y-m-d');
                    break;
                    
                case 'year':
                    // Tính năm hiện tại
                    $dateFrom = $today->copy()->startOfYear()->format('Y-m-d');
                    $dateTo = $today->copy()->endOfYear()->format('Y-m-d');
                    break;
            }
        }

        // Nếu không có ngày, mặc định là tháng hiện tại
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        // Lấy dữ liệu báo cáo (chỉ vật tư)
        $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category, request('sort_column'), request('sort_direction'));

        // Thống kê tổng quan (sử dụng cùng logic với filter)
        $stats = $this->getFilteredInventoryStats($dateFrom, $dateTo, $search, $category);

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

        // Mặc định timeFilter là null
        $timeFilter = null;

        // Chuyển đổi date về format d/m/Y để hiển thị trong view
        $dateFromDisplay = Carbon::parse($dateFrom)->format('d/m/Y');
        $dateToDisplay = Carbon::parse($dateTo)->format('d/m/Y');

        return view('reports.index', [
            'reportData' => $reportData,
            'stats' => $stats,
            'chartData' => $chartData,
            'dateFrom' => $dateFromDisplay,
            'dateTo' => $dateToDisplay,
            'search' => $search,
            'category' => $category,
            'categories' => $categories,
            'timeFilter' => $timeFilter,
        ]);
    }

    /**
     * Ajax: Lấy dữ liệu báo cáo theo bộ lọc
     */
    public function filterAjax(Request $request)
    {
        try {
            // Lấy tham số lọc và chuyển đổi định dạng ngày
            $dateFrom = $this->parseDateToYmd($request->get('from_date'));
            $dateTo = $this->parseDateToYmd($request->get('to_date'));
            $search = $request->get('search');
            $category = $request->get('category_filter');
            $timeFilter = $request->get('time_filter');

            // Xử lý filter thời gian
            if ($timeFilter && !$dateFrom && !$dateTo) {
                $today = now();
                
                switch ($timeFilter) {
                    case 'quarter':
                        // Tính quý hiện tại
                        $currentQuarter = ceil(($today->month) / 3);
                        $quarterStart = $today->copy()->startOfYear()->addMonths(($currentQuarter - 1) * 3);
                        $quarterEnd = $quarterStart->copy()->endOfQuarter();
                        
                        $dateFrom = $quarterStart->format('Y-m-d');
                        $dateTo = $quarterEnd->format('Y-m-d');
                        break;
                        
                    case 'year':
                        // Tính năm hiện tại
                        $dateFrom = $today->copy()->startOfYear()->format('Y-m-d');
                        $dateTo = $today->copy()->endOfYear()->format('Y-m-d');
                        break;
                }
            }

            // Debug log
            \Illuminate\Support\Facades\Log::info('Filter request:', [
                'from_date' => $dateFrom,
                'to_date' => $dateTo,
                'search' => $search,
                'category_filter' => $category,
                'time_filter' => $timeFilter,
                'sort_column' => request('sort_column'),
                'sort_direction' => request('sort_direction')
            ]);

            // Nếu không có ngày, mặc định là tháng hiện tại
            if (!$dateFrom) {
                $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
            }
            if (!$dateTo) {
                $dateTo = Carbon::now()->format('Y-m-d');
            }

            // Lấy dữ liệu báo cáo
            $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category, request('sort_column'), request('sort_direction'));

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
                'stats' => $stats,
                'chartData' => $chartData,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Filter error: ' . $e->getMessage());
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
     * Lấy số lượng vật tư hư hỏng từ kiểm thử
     */
    private function getDamagedMaterialsQuantity($materialId, $dateFrom, $dateTo)
    {
        $testingItems = \App\Models\TestingItem::join('testings', 'testing_items.testing_id', '=', 'testings.id')
            ->where('testing_items.material_id', $materialId)
            ->where('testing_items.item_type', 'material')
            ->where('testings.status', 'completed')
            ->whereDate('testings.test_date', '>=', $dateFrom)
            ->whereDate('testings.test_date', '<=', $dateTo)
            ->select('testing_items.serial_results', 'testing_items.quantity')
            ->get();

        $totalDamaged = 0;

        foreach ($testingItems as $item) {
            if (!empty($item->serial_results)) {
                try {
                    // Parse JSON serial_results
                    $serialResults = json_decode($item->serial_results, true);
                    
                    if (is_array($serialResults)) {
                        // Đếm số lượng "fail" trong serial_results
                        $failCount = 0;
                        foreach ($serialResults as $serial => $result) {
                            if ($result === 'fail') {
                                $failCount++;
                            }
                        }
                        
                        // Nếu có fail, cộng vào tổng số hư hỏng
                        if ($failCount > 0) {
                            $totalDamaged += $failCount;
                        }
                    }
                } catch (\Exception $e) {
                    // Nếu JSON không hợp lệ, bỏ qua
                    continue;
                }
            }
        }

        return $totalDamaged;
    }

    /**
     * Batch query: Lấy số lượng vật tư hư hỏng cho nhiều material cùng lúc
     */
    private function getBatchDamagedMaterialsQuantity(array $materialIds, $dateFrom, $dateTo)
    {
        $result = [];
        foreach ($materialIds as $id) {
            $result[$id] = 0;
        }

        $testingItems = \App\Models\TestingItem::join('testings', 'testing_items.testing_id', '=', 'testings.id')
            ->whereIn('testing_items.material_id', $materialIds)
            ->where('testing_items.item_type', 'material')
            ->where('testings.status', 'completed')
            ->whereDate('testings.test_date', '>=', $dateFrom)
            ->whereDate('testings.test_date', '<=', $dateTo)
            ->select('testing_items.material_id', 'testing_items.serial_results', 'testing_items.quantity')
            ->get();

        foreach ($testingItems as $item) {
            if (!empty($item->serial_results)) {
                try {
                    $serialResults = json_decode($item->serial_results, true);
                    if (is_array($serialResults)) {
                        foreach ($serialResults as $serial => $resultValue) {
                            if ($resultValue === 'fail') {
                                $result[$item->material_id] = ($result[$item->material_id] ?? 0) + 1;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * Batch query: Lấy xuất gián tiếp qua assembly trong kỳ
     */
    private function getBatchIndirectExports(array $materialIds, array $warehouseIds, $dateFrom, $dateTo)
    {
        $result = [];
        foreach ($materialIds as $id) {
            $result[$id] = 0;
        }

        // Lấy tất cả phiếu xuất thành phẩm trong kỳ
        $productExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'product')
            ->whereIn('dispatch_items.warehouse_id', $warehouseIds)
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
            ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
            ->select('dispatch_items.item_id as product_id', 'dispatch_items.quantity')
            ->get();

        if ($productExports->isEmpty()) {
            return $result;
        }

        $productIds = $productExports->pluck('product_id')->unique()->toArray();

        // Lấy tất cả assembly materials cho các product này
        $assemblyMaterials = AssemblyMaterial::join('assembly_products', 'assembly_materials.assembly_id', '=', 'assembly_products.assembly_id')
            ->whereIn('assembly_products.product_id', $productIds)
            ->whereIn('assembly_materials.material_id', $materialIds)
            ->select('assembly_products.product_id', 'assembly_materials.material_id', 'assembly_materials.quantity')
            ->get()
            ->groupBy('product_id');

        foreach ($productExports as $export) {
            $productMaterials = $assemblyMaterials->get($export->product_id, collect());
            foreach ($productMaterials as $mat) {
                $result[$mat->material_id] = ($result[$mat->material_id] ?? 0) + ($mat->quantity * $export->quantity);
            }
        }

        return $result;
    }

    /**
     * Batch query: Lấy xuất gián tiếp qua assembly từ ngày cụ thể đến hiện tại
     */
    private function getBatchIndirectExportsAfterDate(array $materialIds, array $warehouseIds, $dateFrom)
    {
        $result = [];
        foreach ($materialIds as $id) {
            $result[$id] = 0;
        }

        $productExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'product')
            ->whereIn('dispatch_items.warehouse_id', $warehouseIds)
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
            ->select('dispatch_items.item_id as product_id', 'dispatch_items.quantity')
            ->get();

        if ($productExports->isEmpty()) {
            return $result;
        }

        $productIds = $productExports->pluck('product_id')->unique()->toArray();

        $assemblyMaterials = AssemblyMaterial::join('assembly_products', 'assembly_materials.assembly_id', '=', 'assembly_products.assembly_id')
            ->whereIn('assembly_products.product_id', $productIds)
            ->whereIn('assembly_materials.material_id', $materialIds)
            ->select('assembly_products.product_id', 'assembly_materials.material_id', 'assembly_materials.quantity')
            ->get()
            ->groupBy('product_id');

        foreach ($productExports as $export) {
            $productMaterials = $assemblyMaterials->get($export->product_id, collect());
            foreach ($productMaterials as $mat) {
                $result[$mat->material_id] = ($result[$mat->material_id] ?? 0) + ($mat->quantity * $export->quantity);
            }
        }

        return $result;
    }

    /**
     * Lấy dữ liệu báo cáo vật tư (đã tối ưu performance)
     */
    private function getMaterialsReportData($dateFrom, $dateTo, $search = null, $category = null, $sortColumn = null, $sortDirection = null)
    {
        $reportData = [];

        // Lấy danh sách vật tư
        $materialsQuery = Material::where('status', 'active')
            ->where('is_hidden', false);

        // Áp dụng tìm kiếm
        if ($search) {
            $materialsQuery->where(function ($q) use ($search) {
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
        $materialIds = $materials->pluck('id')->toArray();
        
        if (empty($materialIds)) {
            return collect([]);
        }

        // Cache danh sách warehouses (chỉ query 1 lần)
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();
        $warehouseIds = $warehouses->pluck('id')->toArray();

        // Batch query: Tồn kho hiện tại theo material
        $currentStockByMaterial = WarehouseMaterial::whereIn('material_id', $materialIds)
            ->where('item_type', 'material')
            ->whereIn('warehouse_id', $warehouseIds)
            ->select('material_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('material_id')
            ->pluck('total_quantity', 'material_id')
            ->toArray();

        // Batch query: Nhập trong kỳ theo material
        $importsInPeriod = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereIn('inventory_import_materials.material_id', $materialIds)
            ->where('inventory_import_materials.item_type', 'material')
            ->whereIn('inventory_import_materials.warehouse_id', $warehouseIds)
            ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
            ->whereDate('inventory_imports.import_date', '<=', $dateTo)
            ->select('inventory_import_materials.material_id', DB::raw('SUM(inventory_import_materials.quantity) as total_quantity'))
            ->groupBy('inventory_import_materials.material_id')
            ->pluck('total_quantity', 'material_id')
            ->toArray();

        // Batch query: Nhập từ ngày dateFrom đến hiện tại (để tính tồn đầu kỳ)
        $importsAfterDate = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereIn('inventory_import_materials.material_id', $materialIds)
            ->where('inventory_import_materials.item_type', 'material')
            ->whereIn('inventory_import_materials.warehouse_id', $warehouseIds)
            ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
            ->select('inventory_import_materials.material_id', DB::raw('SUM(inventory_import_materials.quantity) as total_quantity'))
            ->groupBy('inventory_import_materials.material_id')
            ->pluck('total_quantity', 'material_id')
            ->toArray();

        // Batch query: Xuất trực tiếp trong kỳ theo material
        $directExportsInPeriod = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->whereIn('dispatch_items.item_id', $materialIds)
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatch_items.warehouse_id', $warehouseIds)
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
            ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
            ->select('dispatch_items.item_id as material_id', DB::raw('SUM(dispatch_items.quantity) as total_quantity'))
            ->groupBy('dispatch_items.item_id')
            ->pluck('total_quantity', 'material_id')
            ->toArray();

        // Batch query: Xuất trực tiếp từ ngày dateFrom đến hiện tại
        $directExportsAfterDate = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->whereIn('dispatch_items.item_id', $materialIds)
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatch_items.warehouse_id', $warehouseIds)
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
            ->select('dispatch_items.item_id as material_id', DB::raw('SUM(dispatch_items.quantity) as total_quantity'))
            ->groupBy('dispatch_items.item_id')
            ->pluck('total_quantity', 'material_id')
            ->toArray();

        // Batch query: Vật tư hư hỏng trong kỳ
        $damagedByMaterial = $this->getBatchDamagedMaterialsQuantity($materialIds, $dateFrom, $dateTo);

        // Tính xuất gián tiếp qua assembly (batch query)
        $indirectExportsInPeriod = $this->getBatchIndirectExports($materialIds, $warehouseIds, $dateFrom, $dateTo);
        $indirectExportsAfterDate = $this->getBatchIndirectExportsAfterDate($materialIds, $warehouseIds, $dateFrom);

        foreach ($materials as $material) {
            $materialId = $material->id;
            
            // Tồn kho hiện tại
            $totalCurrentStock = $currentStockByMaterial[$materialId] ?? 0;
            
            // Nhập trong kỳ
            $totalImports = $importsInPeriod[$materialId] ?? 0;
            
            // Xuất trong kỳ (trực tiếp + gián tiếp)
            $totalExports = ($directExportsInPeriod[$materialId] ?? 0) + ($indirectExportsInPeriod[$materialId] ?? 0);
            
            // Tính tồn đầu kỳ: Tồn hiện tại - Nhập từ dateFrom + Xuất từ dateFrom
            $importsAfter = $importsAfterDate[$materialId] ?? 0;
            $exportsAfter = ($directExportsAfterDate[$materialId] ?? 0) + ($indirectExportsAfterDate[$materialId] ?? 0);
            $totalOpeningStock = $totalCurrentStock - $importsAfter + $exportsAfter;

            // Tính tồn cuối kỳ theo công thức
            $calculatedClosingStock = $totalOpeningStock + $totalImports - $totalExports;

            // Số lượng vật tư hư hỏng
            $damagedQuantity = $damagedByMaterial[$materialId] ?? 0;

            // Kiểm tra xem vật tư có hoạt động thực sự liên quan đến kỳ được chọn không
            $hasActivityBeforeOrInPeriod = false;

            if ($totalImports > 0 || $totalExports > 0) {
                $hasActivityBeforeOrInPeriod = true;
            } elseif ($totalOpeningStock > 0) {
                // Có tồn đầu kỳ nghĩa là có giao dịch trước đó
                if ($importsAfter > 0 || $exportsAfter > 0) {
                    $hasActivityBeforeOrInPeriod = true;
                }
            } elseif ($damagedQuantity > 0) {
                $hasActivityBeforeOrInPeriod = true;
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
                    'closing_stock' => $calculatedClosingStock,
                    'current_stock' => $totalCurrentStock,
                    'damaged_quantity' => $damagedQuantity,
                ];
            }
        }

        // Sắp xếp dữ liệu theo tham số được truyền vào
        $collection = collect($reportData);
        
        // Nếu có tham số sắp xếp từ request hoặc được truyền vào
        if ((request()->has('sort_column') && request()->has('sort_direction')) || ($sortColumn !== null && $sortDirection !== null)) {
            $sortColumn = $sortColumn ?? request('sort_column', 'item_name');
            $sortDirection = $sortDirection ?? request('sort_direction', 'asc');
            
            // Map tên cột từ frontend sang tên field trong data
            $columnMap = [
                'item_code' => 'item_code',
                'item_name' => 'item_name',
                'opening_stock' => 'opening_stock',
                'imports' => 'imports',
                'exports' => 'exports',
                'closing_stock' => 'closing_stock',
                'current_stock' => 'current_stock',
                'damaged_quantity' => 'damaged_quantity',
            ];

            $sortField = $columnMap[$sortColumn] ?? 'item_name';

            if ($sortDirection === 'desc') {
                $collection = $collection->sortByDesc($sortField);
                } else {
                $collection = $collection->sortBy($sortField);
            }
        } else {
            // Sắp xếp mặc định theo tên vật tư
            $collection = $collection->sortBy('item_name');
        }
        
        return $collection->values();
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
        // Kiểm tra vật tư có active và không bị ẩn không
        if ($itemType === 'material') {
            $material = Material::find($itemId);
            if (!$material || $material->status !== 'active' || $material->is_hidden) {
                return 0; // Không tính xuất kho cho vật tư bị ẩn/xóa
            }
        }

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
        // Kiểm tra vật tư có active và không bị ẩn không
        if ($itemType === 'material') {
            $material = Material::find($itemId);
            if (!$material || $material->status !== 'active' || $material->is_hidden) {
                return 0; // Không tính xuất kho cho vật tư bị ẩn/xóa
            }
        }

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
        return match ($type) {
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
     * Tính tổng nhập kho vật tư trong khoảng thời gian với cùng điều kiện (đồng bộ cho mọi nơi)
     */
    private function sumMaterialImports(Carbon|string $from, Carbon|string $to, ?array $materialIds = null)
    {
        // Chuẩn hóa ngày
        $fromDate = Carbon::parse($from)->format('Y-m-d');
        $toDate = Carbon::parse($to)->format('Y-m-d');

        // Nếu chưa truyền danh sách vật tư, lấy tất cả vật tư active & không ẩn
        if ($materialIds === null) {
            $materialIds = Material::where('status', 'active')
                ->where('is_hidden', false)
                ->pluck('id')
                ->toArray();
        }

        return InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereIn('inventory_import_materials.material_id', $materialIds)
            ->where('inventory_import_materials.item_type', 'material')
            ->whereDate('inventory_imports.import_date', '>=', $fromDate)
            ->whereDate('inventory_imports.import_date', '<=', $toDate)
            // Kho active & không ẩn
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('warehouses')
                    ->whereColumn('warehouses.id', 'inventory_import_materials.warehouse_id')
                    ->where('warehouses.status', 'active')
                    ->where('warehouses.is_hidden', false);
            })
            ->sum('inventory_import_materials.quantity');
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
            $materialsQuery->where(function ($q) use ($search) {
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

        // Số lượng vật tư có hoạt động (có nhập/xuất hoặc tồn kho)
        $materialsWithActivity = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->where(function ($query) use ($dateFrom, $dateTo, $materialIds) {
                // Có nhập trong kỳ
                $query->whereExists(function ($subQuery) use ($dateFrom, $dateTo, $materialIds) {
                    $subQuery->select(DB::raw(1))
                        ->from('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                        ->whereRaw('inventory_import_materials.material_id = materials.id')
                        ->whereIn('inventory_import_materials.material_id', $materialIds)
                        ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
                        ->whereDate('inventory_imports.import_date', '<=', $dateTo);
                })
                    // Hoặc có xuất trong kỳ
                ->orWhereExists(function ($subQuery) use ($dateFrom, $dateTo, $materialIds) {
                        $subQuery->select(DB::raw(1))
                            ->from('dispatch_items')
                            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                            ->whereRaw('dispatch_items.item_id = materials.id')
                            ->where('dispatch_items.item_type', 'material')
                        ->whereIn('dispatch_items.item_id', $materialIds)
                            ->whereIn('dispatches.status', ['approved', 'completed'])
                            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                            ->whereDate('dispatches.dispatch_date', '<=', $dateTo);
                    })
                // Hoặc có vật tư hư hỏng trong kỳ
                ->orWhereExists(function ($subQuery) use ($dateFrom, $dateTo, $materialIds) {
                        $subQuery->select(DB::raw(1))
                        ->from('testing_items')
                        ->join('testings', 'testing_items.testing_id', '=', 'testings.id')
                        ->whereRaw('testing_items.material_id = materials.id')
                        ->where('testing_items.item_type', 'material')
                        ->whereIn('testing_items.material_id', $materialIds)
                        ->where('testings.status', 'completed')
                        ->whereDate('testings.test_date', '>=', $dateFrom)
                        ->whereDate('testings.test_date', '<=', $dateTo)
                        ->where('testing_items.fail_quantity', '>', 0);
                    });
            })
            ->count();

        // Số lượng danh mục vật tư (categories)
        $totalCategories = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNotNull('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->orWhere('notes', 'LIKE', "%{$search}%");
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->distinct()
            ->count('category');

        // Nhập vật tư trong kỳ (đồng bộ với mọi nơi)
        $imports = $this->sumMaterialImports($dateFrom, $dateTo, $materialIds);

        // Xuất vật tư trong kỳ (chỉ xuất trực tiếp, không tính xuất gián tiếp qua assembly)
        $exports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->whereIn('dispatch_items.item_id', $materialIds)
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
            ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('dispatch_items.quantity');

        // Tính số lượng vật tư hư hỏng trong kỳ (chỉ materials đã filter)
        $damagedQuantity = 0;
        $testingItems = \App\Models\TestingItem::join('testings', 'testing_items.testing_id', '=', 'testings.id')
            ->whereIn('testing_items.material_id', $materialIds)
            ->where('testing_items.item_type', 'material')
            ->where('testings.status', 'completed')
            ->whereDate('testings.test_date', '>=', $dateFrom)
            ->whereDate('testings.test_date', '<=', $dateTo)
            ->select('testing_items.serial_results', 'testing_items.quantity')
            ->get();

        foreach ($testingItems as $item) {
            if (!empty($item->serial_results)) {
                try {
                    // Parse JSON serial_results
                    $serialResults = json_decode($item->serial_results, true);
                    
                    if (is_array($serialResults)) {
                        // Đếm số lượng "fail" trong serial_results
                        foreach ($serialResults as $serial => $result) {
                            if ($result === 'fail') {
                                $damagedQuantity++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Nếu JSON không hợp lệ, bỏ qua
                    continue;
                }
            }
        }

        // Tính phần trăm thay đổi so với kỳ trước (30 ngày trước)
        $previousFromDate = Carbon::parse($dateFrom)->subDays(30)->format('Y-m-d');
        $previousToDate = Carbon::parse($dateTo)->subDays(30)->format('Y-m-d');

        $previousImports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->whereIn('inventory_import_materials.material_id', $materialIds)
            ->where('inventory_import_materials.item_type', 'material')
            ->whereDate('inventory_imports.import_date', '>=', $previousFromDate)
            ->whereDate('inventory_imports.import_date', '<=', $previousToDate)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'inventory_import_materials.material_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('warehouses')
                    ->whereColumn('warehouses.id', 'inventory_import_materials.warehouse_id')
                    ->where('warehouses.status', 'active')
                    ->where('warehouses.is_hidden', false);
            })
            ->sum('inventory_import_materials.quantity');

        // Xuất vật tư kỳ trước (chỉ xuất trực tiếp, không tính xuất gián tiếp qua assembly)
        $previousExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $previousFromDate)
            ->whereDate('dispatches.dispatch_date', '<=', $previousToDate)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('dispatch_items.quantity');

        $importsChange = $previousImports > 0 ? (($imports - $previousImports) / $previousImports) * 100 : 0;
        $exportsChange = $previousExports > 0 ? (($exports - $previousExports) / $previousExports) * 100 : 0;

        return [
            'total_items' => $materialsWithActivity,
            'total_categories' => $totalCategories,
            'imports' => $imports,
            'exports' => $exports,
            'damaged_quantity' => $damagedQuantity,
            'imports_change' => round($importsChange, 1),
            'exports_change' => round($exportsChange, 1),
        ];
    }

    /**
     * Lấy thống kê tổng quan
     */
    private function getInventoryStats($dateFrom, $dateTo)
    {
        // Số lượng vật tư có hoạt động (có nhập/xuất hoặc tồn kho)
        $materialsWithActivity = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->where(function ($query) use ($dateFrom, $dateTo) {
                // Có nhập trong kỳ
                $query->whereExists(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->select(DB::raw(1))
                            ->from('inventory_import_materials')
                            ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
                            ->whereRaw('inventory_import_materials.material_id = materials.id')
                            ->whereDate('inventory_imports.import_date', '>=', $dateFrom)
                            ->whereDate('inventory_imports.import_date', '<=', $dateTo);
                    })
                    // Hoặc có xuất trong kỳ
                    ->orWhereExists(function ($subQuery) use ($dateFrom, $dateTo) {
                        $subQuery->select(DB::raw(1))
                            ->from('dispatch_items')
                            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                            ->whereRaw('dispatch_items.item_id = materials.id')
                            ->where('dispatch_items.item_type', 'material')
                            ->whereIn('dispatches.status', ['approved', 'completed'])
                            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
                            ->whereDate('dispatches.dispatch_date', '<=', $dateTo);
                })
                // Hoặc có vật tư hư hỏng trong kỳ
                ->orWhereExists(function ($subQuery) use ($dateFrom, $dateTo) {
                    $subQuery->select(DB::raw(1))
                        ->from('testing_items')
                        ->join('testings', 'testing_items.testing_id', '=', 'testings.id')
                        ->whereRaw('testing_items.material_id = materials.id')
                        ->where('testing_items.item_type', 'material')
                        ->where('testings.status', 'completed')
                        ->whereDate('testings.test_date', '>=', $dateFrom)
                        ->whereDate('testings.test_date', '<=', $dateTo)
                        ->where('testing_items.fail_quantity', '>', 0);
                    });
            })
            ->count();

        // Số lượng danh mục vật tư (categories)
        $totalCategories = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNotNull('category')
            ->distinct()
            ->count('category');

        // Nhập vật tư trong kỳ (đồng bộ với mọi nơi)
        $imports = $this->sumMaterialImports($dateFrom, $dateTo);

        // Xuất vật tư trong kỳ (chỉ xuất trực tiếp, không tính xuất gián tiếp qua assembly)
        $exports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $dateFrom)
            ->whereDate('dispatches.dispatch_date', '<=', $dateTo)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('dispatch_items.quantity');

        // Tính số lượng vật tư hư hỏng trong kỳ
        $damagedQuantity = 0;
        $testingItems = \App\Models\TestingItem::join('testings', 'testing_items.testing_id', '=', 'testings.id')
            ->where('testing_items.item_type', 'material')
            ->where('testings.status', 'completed')
            ->whereDate('testings.test_date', '>=', $dateFrom)
            ->whereDate('testings.test_date', '<=', $dateTo)
            ->select('testing_items.serial_results', 'testing_items.quantity')
            ->get();

        foreach ($testingItems as $item) {
            if (!empty($item->serial_results)) {
                try {
                    // Parse JSON serial_results
                    $serialResults = json_decode($item->serial_results, true);
                    
                    if (is_array($serialResults)) {
                        // Đếm số lượng "fail" trong serial_results
                        foreach ($serialResults as $serial => $result) {
                            if ($result === 'fail') {
                                $damagedQuantity++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Nếu JSON không hợp lệ, bỏ qua
                    continue;
                }
            }
        }

        // Tính phần trăm thay đổi so với kỳ trước (30 ngày trước)
        $previousFromDate = Carbon::parse($dateFrom)->subDays(30)->format('Y-m-d');
        $previousToDate = Carbon::parse($dateTo)->subDays(30)->format('Y-m-d');

        $previousImports = InventoryImportMaterial::join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->where('inventory_import_materials.item_type', 'material')
            ->whereDate('inventory_imports.import_date', '>=', $previousFromDate)
            ->whereDate('inventory_imports.import_date', '<=', $previousToDate)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'inventory_import_materials.material_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('inventory_import_materials.quantity');

        // Xuất vật tư kỳ trước (chỉ xuất trực tiếp, không tính xuất gián tiếp qua assembly)
        $previousExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->whereDate('dispatches.dispatch_date', '>=', $previousFromDate)
            ->whereDate('dispatches.dispatch_date', '<=', $previousToDate)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('dispatch_items.quantity');

        $importsChange = $previousImports > 0 ? (($imports - $previousImports) / $previousImports) * 100 : 0;
        $exportsChange = $previousExports > 0 ? (($exports - $previousExports) / $previousExports) * 100 : 0;

        return [
            'total_items' => $materialsWithActivity,
            'total_categories' => $totalCategories,
            'imports' => $imports,
            'exports' => $exports,
            'damaged_quantity' => $damagedQuantity,
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
            $materialsQuery->where(function ($q) use ($search) {
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

        // Lấy các tháng nằm trong khoảng filter và cắt theo giao [dateFrom, dateTo]
        $filterStart = Carbon::parse($dateFrom)->startOfDay();
        $filterEnd = Carbon::parse($dateTo)->endOfDay();
        $cursor = Carbon::parse($dateFrom)->startOfMonth();
        $endCursor = Carbon::parse($dateTo)->endOfMonth();

        $months = [];
        $importsData = [];
        $exportsData = [];

        while ($cursor <= $endCursor) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            // Khoảng giao giữa tháng hiện tại và khoảng filter
            $periodStart = $monthStart->greaterThan($filterStart) ? $monthStart : $filterStart;
            $periodEnd = $monthEnd->lessThan($filterEnd) ? $monthEnd : $filterEnd;

            $months[] = $cursor->format('M');

            // Nhập theo tháng (áp dụng filter + giao khoảng) - dùng helper chung
            $monthImports = $this->sumMaterialImports($periodStart, $periodEnd, $materialIds);

            // Xuất theo tháng (áp dụng filter + giao khoảng)
            $monthExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->where('dispatch_items.item_type', 'material')
                ->whereIn('dispatch_items.item_id', $materialIds)
                ->whereDate('dispatches.dispatch_date', '>=', $periodStart)
                ->whereDate('dispatches.dispatch_date', '<=', $periodEnd)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('warehouses')
                        ->whereColumn('warehouses.id', 'dispatch_items.warehouse_id')
                        ->where('warehouses.status', 'active')
                        ->where('warehouses.is_hidden', false);
                })
                ->sum('dispatch_items.quantity');

            $importsData[] = $monthImports;
            $exportsData[] = $monthExports;

            $cursor->addMonth();
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
        // Danh sách vật tư active & không ẩn
        $materialIds = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->pluck('id')
            ->toArray();

        // Chuẩn hóa khoảng thời gian và lặp theo tháng trong giao khoảng
        $filterStart = Carbon::parse($dateFrom)->startOfDay();
        $filterEnd = Carbon::parse($dateTo)->endOfDay();
        $cursor = Carbon::parse($dateFrom)->startOfMonth();
        $endCursor = Carbon::parse($dateTo)->endOfMonth();

        $months = [];
        $importsData = [];
        $exportsData = [];

        while ($cursor <= $endCursor) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $periodStart = $monthStart->greaterThan($filterStart) ? $monthStart : $filterStart;
            $periodEnd = $monthEnd->lessThan($filterEnd) ? $monthEnd : $filterEnd;

            $months[] = $cursor->format('M');

            // Nhập theo tháng: dùng helper chung
            $monthImports = $this->sumMaterialImports($periodStart, $periodEnd, $materialIds);

            // Xuất theo tháng: đồng bộ điều kiện
            $monthExports = DispatchItem::join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->where('dispatch_items.item_type', 'material')
                ->whereIn('dispatch_items.item_id', $materialIds)
                ->whereDate('dispatches.dispatch_date', '>=', $periodStart)
                ->whereDate('dispatches.dispatch_date', '<=', $periodEnd)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('warehouses')
                        ->whereColumn('warehouses.id', 'dispatch_items.warehouse_id')
                        ->where('warehouses.status', 'active')
                        ->where('warehouses.is_hidden', false);
                })
                ->sum('dispatch_items.quantity');

            $importsData[] = $monthImports;
            $exportsData[] = $monthExports;

            $cursor->addMonth();
        }

        // Top 5 vật tư có tồn kho cao nhất (chỉ materials đã filter)
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
        $dateFrom = $this->parseDateToYmd($request->get('from_date')) ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $this->parseDateToYmd($request->get('to_date')) ?? Carbon::now()->format('Y-m-d');
        $search = $request->get('search');
        $category = $request->get('category_filter');

        $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category, request('sort_column'), request('sort_direction'));

        // Tạo file Excel thực sự
        $filename = 'bao_cao_vat_tu_' . date('Y_m_d_H_i_s') . '.xlsx';
        
        try {
            // Tạo workbook mới
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Thiết lập tiêu đề
            $sheet->setCellValue('A1', 'BÁO CÁO VẬT TƯ');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Thông tin thời gian
            $sheet->setCellValue('A2', 'Thời gian: Từ ' . Carbon::parse($dateFrom)->format('d/m/Y') . ' đến ' . Carbon::parse($dateTo)->format('d/m/Y'));
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Headers
            $headers = [
                'A4' => 'STT',
                'B4' => 'Mã vật tư',
                'C4' => 'Tên vật tư',
                'D4' => 'Đơn vị',
                'E4' => 'Tồn đầu kỳ',
                'F4' => 'Nhập trong kỳ',
                'G4' => 'Xuất trong kỳ',
                'H4' => 'Tồn cuối kỳ',
                'I4' => 'Tồn hiện tại',
                'J4' => 'Chênh lệch'
            ];
            
            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
            
            // Data
            $row = 5;
            foreach ($reportData as $index => $item) {
                $difference = $item['current_stock'] - $item['closing_stock'];
                
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item['item_code']);
                $sheet->setCellValue('C' . $row, $item['item_name']);
                $sheet->setCellValue('D' . $row, $item['item_unit']);
                $sheet->setCellValue('E' . $row, $item['opening_stock']);
                $sheet->setCellValue('F' . $row, $item['imports']);
                $sheet->setCellValue('G' . $row, $item['exports']);
                $sheet->setCellValue('H' . $row, $item['closing_stock']);
                $sheet->setCellValue('I' . $row, $item['current_stock']);
                $sheet->setCellValue('J' . $row, $difference);
                
                // Định dạng số
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
                
                // Màu cho chênh lệch
                if ($difference >= 0) {
                    $sheet->getStyle('J' . $row)->getFont()->getColor()->setRGB('008000'); // Xanh lá
                } else {
                    $sheet->getStyle('J' . $row)->getFont()->getColor()->setRGB('FF0000'); // Đỏ
                }
                
                $row++;
            }
            
            // Tổng cộng
            $totalRow = $row;
            $sheet->setCellValue('A' . $totalRow, 'TỔNG CỘNG');
            $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
            $sheet->setCellValue('E' . $totalRow, $reportData->sum('opening_stock'));
            $sheet->setCellValue('F' . $totalRow, $reportData->sum('imports'));
            $sheet->setCellValue('G' . $totalRow, $reportData->sum('exports'));
            $sheet->setCellValue('H' . $totalRow, $reportData->sum('closing_stock'));
            $sheet->setCellValue('I' . $totalRow, $reportData->sum('current_stock'));
            $sheet->setCellValue('J' . $totalRow, $reportData->sum('current_stock') - $reportData->sum('closing_stock'));
            
            // Định dạng tổng cộng
            $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
            $sheet->getStyle('E' . $totalRow . ':J' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Thông tin xuất
            $sheet->setCellValue('A' . ($totalRow + 2), 'Xuất lúc: ' . Carbon::now()->format('H:i:s d/m/Y'));
            $sheet->mergeCells('A' . ($totalRow + 2) . ':J' . ($totalRow + 2));
            $sheet->getStyle('A' . ($totalRow + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . ($totalRow + 2))->getFont()->setItalic(true);
            
            // Tự động điều chỉnh độ rộng cột
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Border cho bảng dữ liệu
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A4:J' . ($totalRow))->applyFromArray($styleArray);
            
            // Tạo file Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Lưu vào buffer
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();
            
            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'max-age=0');
                
        } catch (\Exception $e) {
            Log::error('Export Excel Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Có lỗi khi xuất file Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xuất báo cáo PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $dateFrom = $this->parseDateToYmd($request->get('from_date')) ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $this->parseDateToYmd($request->get('to_date')) ?? Carbon::now()->format('Y-m-d');
            $search = $request->get('search');
            $category = $request->get('category_filter');

            $reportData = $this->getMaterialsReportData($dateFrom, $dateTo, $search, $category, request('sort_column'), request('sort_direction'));
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
