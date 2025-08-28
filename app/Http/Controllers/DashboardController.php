<?php

namespace App\Http\Controllers;

use App\Models\WarehouseMaterial;
use App\Models\Testing;
use App\Models\TestingItem;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        // 1. Thống kê vật tư
        $materialStats = $this->getMaterialStats();
        
        // 2. Thống kê thành phẩm  
        $productStats = $this->getProductStats();
        
        // 3. Thống kê hàng hóa
        $goodStats = $this->getGoodStats();

        return response()->json([
            'materials' => $materialStats,
            'products' => $productStats, 
            'goods' => $goodStats
        ]);
    }

    /**
     * Get material statistics
     */
    private function getMaterialStats()
    {
        // Tổng nhập kho vật tư - lấy từ lịch sử nhập kho thực tế trong kỳ hiện tại (tháng hiện tại)
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        
        $totalImport = DB::table('inventory_import_materials')
            ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->where('inventory_import_materials.item_type', 'material')
            ->where('inventory_import_materials.material_id', '>', 0) // Đảm bảo có material_id
            ->whereDate('inventory_imports.import_date', '>=', $currentMonthStart)
            ->whereDate('inventory_imports.import_date', '<=', $currentMonthEnd)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'inventory_import_materials.material_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('inventory_import_materials.quantity');

        // Tổng xuất kho vật tư - lấy từ lịch sử xuất kho thực tế trong kỳ hiện tại (tháng hiện tại)
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        
        $totalExport = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->where('dispatch_items.item_id', '>', 0) // Đảm bảo có item_id
            ->whereDate('dispatches.dispatch_date', '>=', $currentMonthStart)
            ->whereDate('dispatches.dispatch_date', '<=', $currentMonthEnd)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('dispatch_items.quantity');

        // Tổng hư hỏng - tính dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
        $totalDamaged = $this->calculateDamagedMaterials($currentMonthStart, $currentMonthEnd);

        return [
            'total_import' => $totalImport,
            'total_export' => $totalExport,
            'total_damaged' => $totalDamaged
        ];
    }

    /**
     * Get product statistics
     */
    private function getProductStats() 
    {
        // Lấy thời gian hiện tại (tháng hiện tại)
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        
        // Tổng nhập kho thành phẩm - chỉ tính từ các kho active và thành phẩm active
        $totalImport = WarehouseMaterial::where('item_type', 'product')
            ->whereHas('warehouse', function($query) {
                $query->where('status', 'active');
            })
            ->whereHas('product', function($query) {
                $query->where('status', '!=', 'deleted');
            })
            ->sum('quantity');

        // Tổng xuất kho thành phẩm
        $totalExport = DB::table('dispatch_items')
            ->where('item_type', 'product')
            ->sum('quantity');

        // Tổng hư hỏng - tính dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
        $totalDamaged = $this->calculateDamagedProducts($currentMonthStart, $currentMonthEnd);

        return [
            'total_import' => $totalImport,
            'total_export' => $totalExport,
            'total_damaged' => $totalDamaged
        ];
    }

    /**
     * Get good statistics
     */
    private function getGoodStats()
    {
        // Lấy thời gian hiện tại (tháng hiện tại)
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        
        // Tổng nhập kho hàng hóa - chỉ tính từ các kho active và hàng hóa active
        $totalImport = WarehouseMaterial::where('item_type', 'good')
            ->whereHas('warehouse', function($query) {
                $query->where('status', 'active');
            })
            ->whereHas('good', function($query) {
                $query->where('status', '!=', 'deleted');
            })
            ->sum('quantity');

        // Tổng xuất kho hàng hóa  
        $totalExport = DB::table('dispatch_items')
            ->where('item_type', 'good')
            ->sum('quantity');

        // Tổng hư hỏng - tính dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
        $totalDamaged = $this->calculateDamagedGoods($currentMonthStart, $currentMonthEnd);

        return [
            'total_import' => $totalImport,
            'total_export' => $totalExport,
            'total_damaged' => $totalDamaged
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ tổng quan nhập/xuất/hư hỏng theo thời gian
     */
    public function getInventoryOverviewChart(Request $request)
    {
        try {
            // Lấy loại dữ liệu (materials, products, goods)
            $category = $request->input('category', 'materials');
            
            // Lấy thông tin thời gian từ request
            $timeRangeType = $request->input('time_range_type', 'month');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            Log::info('Getting inventory overview chart data', [
                'category' => $category,
                'time_range_type' => $timeRangeType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'request_url' => $request->fullUrl()
            ]);
            
            // Tạo mảng nhãn thời gian và dữ liệu theo loại thời gian
            $labels = [];
            $data = [];
            
            if ($startDate && $endDate) {
                // Sử dụng khoảng thời gian được chỉ định
                $data = $this->getChartDataByTimeRange($category, $timeRangeType, $startDate, $endDate);
                $labels = $data['labels'];
                $chartData = $data['data'];
                
                Log::info('Chart data generated', [
                    'category' => $category,
                    'time_range_type' => $timeRangeType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'labels_count' => count($labels),
                    'data_keys' => array_keys($chartData)
                ]);
            } else {
                // Mặc định là 6 tháng gần nhất
                $months = 6;
                $currentDate = now();
                
                for ($i = $months - 1; $i >= 0; $i--) {
                    $date = clone $currentDate;
                    $date->subMonths($i);
                    $labels[] = 'Tháng ' . $date->format('n');
                }
                
                // Lấy dữ liệu theo loại
                switch ($category) {
                    case 'materials':
                        $chartData = $this->getMaterialsChartData($months);
                        break;
                    case 'products':
                        $chartData = $this->getProductsChartData($months);
                        break;
                    case 'goods':
                        $chartData = $this->getGoodsChartData($months);
                        break;
                    default:
                        $chartData = $this->getMaterialsChartData($months);
                }
            }
            
            // Kiểm tra xem có dữ liệu không
            $hasData = false;
            foreach (['import', 'export', 'damaged'] as $key) {
                if (isset($chartData[$key]) && array_sum($chartData[$key]) > 0) {
                    $hasData = true;
                    break;
                }
            }
            
            // Nếu không có dữ liệu, trả về dữ liệu rỗng
            if (!$hasData) {
                Log::warning('No data found for chart, returning empty data', [
                    'category' => $category,
                    'time_range_type' => $timeRangeType
                ]);
                
                // Trả về dữ liệu rỗng
                $labelCount = count($labels);
                $chartData = [
                    'import' => array_fill(0, $labelCount, 0),
                    'export' => array_fill(0, $labelCount, 0),
                    'damaged' => array_fill(0, $labelCount, 0)
                ];
            }
            
            $response = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Nhập kho',
                        'data' => $chartData['import'],
                        'backgroundColor' => '#10b981',
                    ],
                    [
                        'label' => 'Xuất kho',
                        'data' => $chartData['export'],
                        'backgroundColor' => '#ef4444',
                    ],
                    [
                        'label' => 'Hư hỏng',
                        'data' => $chartData['damaged'],
                        'backgroundColor' => '#f59e0b',
                    ],
                ],
                'time_range_type' => $timeRangeType
            ];
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error getting inventory overview chart data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Có lỗi xảy ra khi lấy dữ liệu biểu đồ',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy dữ liệu biểu đồ phân loại kho
     */
    public function getInventoryCategoriesChart()
    {
        try {
            Log::info('Getting inventory categories chart data');
            
            // Lấy tổng số lượng theo loại từ cùng nguồn với hàm getStatistics
            // 1. Thống kê vật tư
            $materialStats = $this->getMaterialStats();
            
            // 2. Thống kê thành phẩm  
            $productStats = $this->getProductStats();
            
            // 3. Thống kê hàng hóa
            $goodStats = $this->getGoodStats();
            
            // Sử dụng số lượng nhập kho tương ứng với số liệu thống kê trên đầu trang
            $materialCount = $materialStats['total_import'];
            $productCount = $productStats['total_import'];
            $goodCount = $goodStats['total_import'];
            
            Log::info('Inventory counts from stats functions', [
                'material_count' => $materialCount,
                'product_count' => $productCount,
                'good_count' => $goodCount
            ]);
            
            // Tính phần trăm
            $total = $materialCount + $productCount + $goodCount;
            $materialPercent = $total > 0 ? round(($materialCount / $total) * 100) : 0;
            $productPercent = $total > 0 ? round(($productCount / $total) * 100) : 0;
            $goodPercent = $total > 0 ? round(($goodCount / $total) * 100) : 0;
            
            // Điều chỉnh để tổng bằng 100%
            $sum = $materialPercent + $productPercent + $goodPercent;
            if ($sum > 0 && $sum != 100) {
                $diff = 100 - $sum;
                if ($materialPercent > 0) {
                    $materialPercent += $diff;
                } elseif ($productPercent > 0) {
                    $productPercent += $diff;
                } elseif ($goodPercent > 0) {
                    $goodPercent += $diff;
                }
            }
            
            // Nếu tổng là 0, trả về dữ liệu rỗng
            if ($total == 0) {
                Log::warning('No inventory data found, returning empty data');
                return response()->json([
                    'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                    'data' => [0, 0, 0]
                ]);
            }
            
            Log::info('Inventory categories chart data generated successfully', [
                'material_percent' => $materialPercent,
                'product_percent' => $productPercent,
                'good_percent' => $goodPercent
            ]);
            
            return response()->json([
                'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                'data' => [$materialPercent, $productPercent, $goodPercent]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getInventoryCategoriesChart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data in case of error
            return response()->json([
                'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                'data' => [0, 0, 0]
            ]);
        }
    }
    
    /**
     * Lấy dữ liệu biểu đồ phân bố theo kho
     */
    public function getWarehouseDistributionChart(Request $request)
    {
        try {
            Log::info('Getting warehouse distribution chart data', [
                'filters' => $request->all()
            ]);
            
            // Lấy tham số lọc (nếu có)
            $itemType = $request->input('item_type'); // 'material', 'product', 'good' hoặc null (tất cả)
            $warehouseIds = $request->input('warehouse_ids'); // mảng id kho hoặc null (tất cả)
            
            if ($warehouseIds && is_string($warehouseIds)) {
                $warehouseIds = explode(',', $warehouseIds);
            }
            
            // Lấy danh sách kho (có thể lọc theo ID)
            $warehousesQuery = Warehouse::with(['warehouseMaterials']);
            
            if ($warehouseIds) {
                $warehousesQuery->whereIn('id', $warehouseIds);
            }
            
            $warehouses = $warehousesQuery->get();
            
            Log::info('Fetched warehouses for distribution chart', [
                'count' => $warehouses->count(),
                'warehouse_ids' => $warehouseIds ? implode(',', $warehouseIds) : 'all',
                'item_type' => $itemType ?: 'all'
            ]);
            
            $data = [];
            $labels = [];
            $details = [];
            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1'];
            
            // Tính tổng số lượng cho từng kho
            $warehouseData = [];
            $total = 0;
            
            foreach ($warehouses as $warehouse) {
                // Khởi tạo query để lấy dữ liệu
                $query = $warehouse->warehouseMaterials();
                
                // Áp dụng lọc theo loại nếu có
                if ($itemType) {
                    $query->where('item_type', $itemType);
                }
                
                // Tính tổng số lượng theo loại vật tư
                $materialCount = $itemType ? ($itemType == 'material' ? $query->sum('quantity') : 0) : $query->where('item_type', 'material')->sum('quantity');
                $productCount = $itemType ? ($itemType == 'product' ? $query->sum('quantity') : 0) : $query->where('item_type', 'product')->sum('quantity');
                $goodCount = $itemType ? ($itemType == 'good' ? $query->sum('quantity') : 0) : $query->where('item_type', 'good')->sum('quantity');
                
                $warehouseTotal = $materialCount + $productCount + $goodCount;
                $total += $warehouseTotal;
                
                // Thêm tất cả kho, kể cả kho trống
                $warehouseData[] = [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'material_count' => $materialCount,
                    'product_count' => $productCount,
                    'good_count' => $goodCount,
                    'total' => $warehouseTotal,
                    'address' => $warehouse->address
                ];
                
                Log::info("Warehouse {$warehouse->name} counts", [
                    'id' => $warehouse->id,
                    'material_count' => $materialCount,
                    'product_count' => $productCount,
                    'good_count' => $goodCount,
                    'total' => $warehouseTotal
                ]);
            }
            
            // Sắp xếp kho theo số lượng giảm dần
            usort($warehouseData, function($a, $b) {
                return $b['total'] <=> $a['total'];
            });
            
            // Gộp những kho có phần trăm quá nhỏ để tránh đè lên nhau trên biểu đồ
            $minPercentThreshold = 1.0; // Chỉ hiển thị riêng những kho có phần trăm >= 1%
            $warehouseDataFiltered = [];
            $othersData = [
                'id' => 'others',
                'name' => 'Kho khác',
                'material_count' => 0,
                'product_count' => 0,
                'good_count' => 0,
                'total' => 0
            ];
            $mergedWarehouses = []; // Danh sách tên các kho đã được gộp
            
            foreach ($warehouseData as $warehouse) {
                if ($total > 0) {
                    $percent = round(($warehouse['total'] / $total) * 100, 2);
                    
                    if ($percent >= $minPercentThreshold) {
                        // Giữ nguyên những kho có phần trăm >= 1%
                        $warehouseDataFiltered[] = $warehouse;
                    } else {
                        // Gộp những kho có phần trăm < 1% vào "Kho khác"
                        $othersData['material_count'] += $warehouse['material_count'];
                        $othersData['product_count'] += $warehouse['product_count'];
                        $othersData['good_count'] += $warehouse['good_count'];
                        $othersData['total'] += $warehouse['total'];
                        $mergedWarehouses[] = $warehouse['name'];
                    }
                } else {
                    // Nếu không có dữ liệu, chỉ hiển thị những kho có số lượng > 0
                    if ($warehouse['total'] > 0) {
                        $warehouseDataFiltered[] = $warehouse;
                    }
                }
            }
            
            // Thêm mục "Kho khác" nếu có dữ liệu
            if ($othersData['total'] > 0) {
                $mergedCount = count($mergedWarehouses);
                if ($mergedCount > 0) {
                    $othersData['name'] = "Kho khác ({$mergedCount} kho)";
                    // Thêm thông tin chi tiết về các kho đã gộp
                    $othersData['merged_details'] = $mergedWarehouses;
                }
                $warehouseDataFiltered[] = $othersData;
            }
            
            // Sắp xếp lại theo số lượng giảm dần
            usort($warehouseDataFiltered, function($a, $b) {
                return $b['total'] <=> $a['total'];
            });
            
            // Tính phần trăm cho mỗi kho (sử dụng dữ liệu đã được lọc)
            foreach ($warehouseDataFiltered as $warehouse) {
                if ($total > 0) {
                    $percent = round(($warehouse['total'] / $total) * 100, 2);
                    // Hiển thị tất cả kho, kể cả kho trống (percent = 0)
                    $data[] = $percent;
                    $labels[] = $warehouse['name'];
                    $details[] = [
                        'id' => $warehouse['id'],
                        'name' => $warehouse['name'],
                        'material_count' => $warehouse['material_count'],
                        'product_count' => $warehouse['product_count'],
                        'good_count' => $warehouse['good_count'],
                        'total' => $warehouse['total'],
                        'percent' => $percent,
                        'address' => $warehouse['address'] ?? null,
                        'merged_details' => $warehouse['merged_details'] ?? null
                    ];
                } else {
                    // Nếu không có dữ liệu nào, hiển thị tất cả kho với percent = 0
                    $data[] = 0;
                    $labels[] = $warehouse['name'];
                    $details[] = [
                        'id' => $warehouse['id'],
                        'name' => $warehouse['name'],
                        'material_count' => $warehouse['material_count'],
                        'product_count' => $warehouse['product_count'],
                        'good_count' => $warehouse['good_count'],
                        'total' => $warehouse['total'],
                        'percent' => 0,
                        'address' => $warehouse['address'] ?? null,
                        'merged_details' => $warehouse['merged_details'] ?? null
                    ];
                }
            }
            
            // Nếu không có dữ liệu, trả về dữ liệu rỗng
            if (empty($labels)) {
                Log::warning('No warehouse data found, returning empty data');
                return response()->json([
                    'labels' => [],
                    'data' => [],
                    'colors' => [],
                    'details' => []
                ]);
            }
            
            // Điều chỉnh để tổng phần trăm bằng 100%
            $sum = array_sum($data);
            if ($sum != 100 && $sum > 0) {
                $diff = 100 - $sum;
                // Thêm phần chênh lệch vào giá trị lớn nhất
                $maxIndex = array_search(max($data), $data);
                $data[$maxIndex] += $diff;
                $details[$maxIndex]['percent'] += $diff;
            }
            
            // Đảm bảo đủ màu sắc cho tất cả các phần
            while (count($colors) < count($data)) {
                $colors = array_merge($colors, $colors);
            }
            
            Log::info('Warehouse distribution chart data generated successfully', [
                'labels_count' => count($labels),
                'total_percent' => array_sum($data),
                'item_type' => $itemType ?: 'all',
                'merged_warehouses_count' => count($mergedWarehouses),
                'merged_warehouses' => $mergedWarehouses
            ]);
            
            return response()->json([
                'labels' => $labels,
                'data' => $data,
                'colors' => array_slice($colors, 0, count($labels)),
                'details' => $details,
                'total_quantity' => $total,
                'filters' => [
                    'item_type' => $itemType,
                    'warehouse_ids' => $warehouseIds
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getWarehouseDistributionChart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data in case of error
            return response()->json([
                'labels' => [],
                'data' => [],
                'colors' => [],
                'details' => [],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Lấy dữ liệu vật tư cho biểu đồ
     */
    private function getMaterialsChartData($months)
    {
        try {
            $import = [];
            $export = [];
            $damaged = [];
            
            // Lấy dữ liệu cho 6 tháng gần nhất
            for ($i = $months - 1; $i >= 0; $i--) {
                $startDate = now()->startOfMonth()->subMonths($i);
                $endDate = clone $startDate;
                $endDate = $endDate->endOfMonth();
                
                // Debug thông tin ngày tháng
                Log::info("Processing date range for materials, month {$i}", [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'is_current_month' => ($i == 0) ? 'yes' : 'no'
                ]);
                
                // Nếu là tháng hiện tại, lấy tổng số từ bảng thống kê để đảm bảo khớp với số liệu hiển thị ở trên
                if ($i == 0) {
                    // Tổng nhập kho vật tư - lấy giống hàm getMaterialStats
                    $importCount = WarehouseMaterial::where('item_type', 'material')
                        ->whereHas('warehouse', function($query) {
                            $query->where('status', 'active');
                        })
                        ->whereHas('material', function($query) {
                            $query->where('status', '!=', 'deleted');
                        })
                        ->sum('quantity');
                    
                    // Tổng xuất kho (số lượng đã sử dụng trong lắp ráp)
                    $exportCount = DB::table('assembly_materials')->sum('quantity');
                    
                    // Tổng hư hỏng - sử dụng logic mới giống như thống kê bên trên
                    $damagedCount = $this->calculateDamagedMaterials($startDate, $endDate);
                    
                    Log::info("Current month material totals from stats", [
                        'import' => $importCount,
                        'export' => $exportCount,
                        'damaged' => $damagedCount
                    ]);
                } else {
                    // Số lượng nhập kho - Thử nhiều cách để lấy dữ liệu nhập kho
                    // Cách 1: Thông qua bảng inventory_import_materials
                    $importCount = DB::table('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                        ->where('inventory_import_materials.item_type', 'material')
                        ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                        ->sum('inventory_import_materials.quantity');
                    
                    // Log để debug
                    Log::info("Import count from inventory_import_materials for month {$startDate->format('Y-m')}", [
                        'month' => $startDate->format('Y-m'),
                        'count' => $importCount
                    ]);
                    
                    // Nếu không có dữ liệu, thử cách 2: Kiểm tra trong warehouse_materials
                    if ($importCount == 0) {
                        $warehouseCount = DB::table('warehouse_materials')
                            ->join('warehouses', 'warehouses.id', '=', 'warehouse_materials.warehouse_id')
                            ->join('materials', 'materials.id', '=', 'warehouse_materials.material_id')
                            ->where('warehouse_materials.item_type', 'material')
                            ->where('warehouses.status', 'active')
                            ->where('materials.status', '!=', 'deleted')
                            ->where('warehouse_materials.created_at', '>=', $startDate)
                            ->where('warehouse_materials.created_at', '<=', $endDate)
                            ->sum('warehouse_materials.quantity');
                        
                        if ($warehouseCount > 0) {
                            $importCount = $warehouseCount;
                            Log::info("Found import count in warehouse_materials for month {$startDate->format('Y-m')}", [
                                'count' => $importCount
                            ]);
                        }
                    }
                    
                    // Số lượng xuất kho (sử dụng trong lắp ráp)
                    $exportCount = DB::table('assembly_materials')
                        ->join('assemblies', 'assemblies.id', '=', 'assembly_materials.assembly_id')
                        ->whereBetween('assemblies.created_at', [$startDate, $endDate])
                        ->sum('assembly_materials.quantity');
                    
                    // Số lượng hư hỏng - sử dụng logic mới
                    $damagedCount = $this->calculateDamagedMaterials($startDate, $endDate);
                }
                
                $import[] = $importCount;
                $export[] = $exportCount;
                $damaged[] = $damagedCount;
            }
            
            Log::info('Materials chart data generated successfully', [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ]);
            
            return [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ];
        } catch (\Exception $e) {
            Log::error('Error in getMaterialsChartData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data in case of error
            return [
                'import' => array_fill(0, $months, 0),
                'export' => array_fill(0, $months, 0),
                'damaged' => array_fill(0, $months, 0)
            ];
        }
    }
    
    /**
     * Lấy dữ liệu thành phẩm cho biểu đồ
     */
    private function getProductsChartData($months)
    {
        try {
            $import = [];
            $export = [];
            $damaged = [];
            
            // Lấy dữ liệu cho 6 tháng gần nhất
            for ($i = $months - 1; $i >= 0; $i--) {
                $startDate = now()->startOfMonth()->subMonths($i);
                $endDate = clone $startDate;
                $endDate = $endDate->endOfMonth();
                
                // Debug thông tin ngày tháng
                Log::info("Processing date range for products, month {$i}", [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'is_current_month' => ($i == 0) ? 'yes' : 'no'
                ]);
                
                // Nếu là tháng hiện tại, lấy tổng số từ bảng thống kê để đảm bảo khớp với số liệu hiển thị ở trên
                if ($i == 0) {
                    // Tổng nhập kho thành phẩm - lấy giống hàm getProductStats
                    $importCount = WarehouseMaterial::where('item_type', 'product')
                        ->whereHas('warehouse', function($query) {
                            $query->where('status', 'active');
                        })
                        ->whereHas('product', function($query) {
                            $query->where('status', '!=', 'deleted');
                        })
                        ->sum('quantity');
                    
                    // Tổng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->where('item_type', 'product')
                        ->sum('quantity');
                    
                    // Tổng hư hỏng - sử dụng logic mới giống như thống kê bên trên
                    $damagedCount = $this->calculateDamagedProducts($startDate, $endDate);
                    
                    Log::info("Current month product totals from stats", [
                        'import' => $importCount,
                        'export' => $exportCount,
                        'damaged' => $damagedCount
                    ]);
                } else {
                    // Số lượng nhập kho
                    $importCount = DB::table('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                        ->where('inventory_import_materials.item_type', 'product')
                        ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                        ->sum('inventory_import_materials.quantity');
                    
                    // Log để debug
                    Log::info("Import count for products, month {$startDate->format('Y-m')}", [
                        'count' => $importCount
                    ]);
                    
                    // Nếu không có dữ liệu, thử kiểm tra trong warehouse_materials
                    if ($importCount == 0) {
                        $warehouseCount = DB::table('warehouse_materials')
                            ->join('warehouses', 'warehouses.id', '=', 'warehouse_materials.warehouse_id')
                            ->join('products', 'products.id', '=', 'warehouse_materials.material_id')
                            ->where('warehouse_materials.item_type', 'product')
                            ->where('warehouses.status', 'active')
                            ->where('products.status', '!=', 'deleted')
                            ->where('warehouse_materials.created_at', '>=', $startDate)
                            ->where('warehouse_materials.created_at', '<=', $endDate)
                            ->sum('warehouse_materials.quantity');
                        
                        if ($warehouseCount > 0) {
                            $importCount = $warehouseCount;
                            Log::info("Found product import count in warehouse_materials for month {$startDate->format('Y-m')}", [
                                'count' => $importCount
                            ]);
                        }
                    }
                    
                    // Số lượng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                        ->where('dispatch_items.item_type', 'product')
                        ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                        ->sum('dispatch_items.quantity');
                    
                    // Số lượng hư hỏng - sử dụng logic mới
                    $damagedCount = $this->calculateDamagedProducts($startDate, $endDate);
                }
                
                $import[] = $importCount;
                $export[] = $exportCount;
                $damaged[] = $damagedCount;
            }
            
            Log::info('Products chart data generated successfully', [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ]);
            
            return [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ];
        } catch (\Exception $e) {
            Log::error('Error in getProductsChartData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data in case of error
            return [
                'import' => array_fill(0, $months, 0),
                'export' => array_fill(0, $months, 0),
                'damaged' => array_fill(0, $months, 0)
            ];
        }
    }
    
    /**
     * Lấy dữ liệu hàng hóa cho biểu đồ
     */
    private function getGoodsChartData($months)
    {
        try {
            $import = [];
            $export = [];
            $damaged = [];
            
            // Lấy dữ liệu cho 6 tháng gần nhất
            for ($i = $months - 1; $i >= 0; $i--) {
                $startDate = now()->startOfMonth()->subMonths($i);
                $endDate = clone $startDate;
                $endDate = $endDate->endOfMonth();
                
                // Debug thông tin ngày tháng
                Log::info("Processing date range for goods, month {$i}", [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'is_current_month' => ($i == 0) ? 'yes' : 'no'
                ]);
                
                // Nếu là tháng hiện tại, lấy tổng số từ bảng thống kê để đảm bảo khớp với số liệu hiển thị ở trên
                if ($i == 0) {
                    // Tổng nhập kho hàng hóa - lấy giống hàm getGoodStats
                    $importCount = WarehouseMaterial::where('item_type', 'good')
                        ->whereHas('warehouse', function($query) {
                            $query->where('status', 'active');
                        })
                        ->whereHas('good', function($query) {
                            $query->where('status', '!=', 'deleted');
                        })
                        ->sum('quantity');
                    
                    // Tổng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->where('item_type', 'good')
                        ->sum('quantity');
                    
                    // Tổng hư hỏng - sử dụng logic mới giống như thống kê bên trên
                    $damagedCount = $this->calculateDamagedGoods($startDate, $endDate);
                    
                    Log::info("Current month goods totals from stats", [
                        'import' => $importCount,
                        'export' => $exportCount,
                        'damaged' => $damagedCount
                    ]);
                } else {
                    // Số lượng nhập kho
                    $importCount = DB::table('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                        ->where('inventory_import_materials.item_type', 'good')
                        ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                        ->sum('inventory_import_materials.quantity');
                    
                    // Log để debug
                    Log::info("Import count for goods, month {$startDate->format('Y-m')}", [
                        'count' => $importCount
                    ]);
                    
                    // Nếu không có dữ liệu, thử kiểm tra trong warehouse_materials
                    if ($importCount == 0) {
                        $warehouseCount = DB::table('warehouse_materials')
                            ->join('warehouses', 'warehouses.id', '=', 'warehouse_materials.warehouse_id')
                            ->join('goods', 'goods.id', '=', 'warehouse_materials.material_id')
                            ->where('warehouse_materials.item_type', 'good')
                            ->where('warehouses.status', 'active')
                            ->where('goods.status', '!=', 'deleted')
                            ->where('warehouse_materials.created_at', '>=', $startDate)
                            ->where('warehouse_materials.created_at', '<=', $endDate)
                            ->sum('warehouse_materials.quantity');
                        
                        if ($warehouseCount > 0) {
                            $importCount = $warehouseCount;
                            Log::info("Found goods import count in warehouse_materials for month {$startDate->format('Y-m')}", [
                                'count' => $importCount
                            ]);
                        }
                    }
                    
                    // Số lượng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                        ->where('dispatch_items.item_type', 'good')
                        ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                        ->sum('dispatch_items.quantity');
                    
                    // Số lượng hư hỏng - sử dụng logic mới
                    $damagedCount = $this->calculateDamagedGoods($startDate, $endDate);
                }
                
                $import[] = $importCount;
                $export[] = $exportCount;
                $damaged[] = $damagedCount;
            }
            
            Log::info('Goods chart data generated successfully', [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ]);
            
            return [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ];
        } catch (\Exception $e) {
            Log::error('Error in getGoodsChartData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data in case of error
            return [
                'import' => array_fill(0, $months, 0),
                'export' => array_fill(0, $months, 0),
                'damaged' => array_fill(0, $months, 0)
            ];
        }
    }

    /**
     * Lấy dữ liệu biểu đồ gia tăng dự án
     */
    public function getProjectGrowthChart()
    {
        try {
            Log::info('Getting project growth chart data');
            
            $months = 6;
            $labels = [];
            $data = [];
            $currentDate = now();
            
            // Tạo mảng nhãn thời gian
            for ($i = 0; $i < $months; $i++) {
                $date = clone $currentDate;
                $date->subMonths($months - 1 - $i);
                $labels[] = 'Tháng ' . $date->format('n');
            }
            
            // Tính số lượng dự án tích lũy theo tháng
            $cumulativeProjects = 0;
            
            for ($i = 0; $i < $months; $i++) {
                $startDate = clone $currentDate;
                $startDate = $startDate->subMonths($months - 1 - $i)->startOfMonth();
                
                // Nếu là tháng đầu tiên, lấy tất cả dự án được tạo trước đó
                if ($i == 0) {
                    $cumulativeProjects = \App\Models\Project::where('created_at', '<', $startDate)
                        ->count();
                }
                
                // Đếm số dự án mới trong tháng
                $endDate = clone $startDate;
                $endDate = $endDate->endOfMonth();
                
                $newProjects = \App\Models\Project::where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate)
                    ->count();
                
                // Cộng dồn số lượng dự án
                $cumulativeProjects += $newProjects;
                $data[] = $cumulativeProjects;
                
                Log::info("Project count for month {$startDate->format('Y-m')}", [
                    'month' => $startDate->format('Y-m'),
                    'new_projects' => $newProjects,
                    'cumulative_projects' => $cumulativeProjects
                ]);
            }
            
            Log::info('Project growth chart data generated successfully', [
                'labels' => $labels,
                'data' => $data
            ]);
            
            return response()->json([
                'labels' => $labels,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getProjectGrowthChart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data in case of error
            return response()->json([
                'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                'data' => [0, 0, 0, 0, 0, 0]
            ]);
        }
    }

    /**
     * Hiển thị trang dashboard
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Tìm kiếm thông tin trong dashboard
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            $category = $request->input('category', 'all');
            $filters = $request->isMethod('post') ? $request->input('filters', []) : $request->all();
            
            // Mặc định luôn tìm kiếm cả sản phẩm có tồn kho = 0 (theo yêu cầu mới)
            // Chỉ loại trừ những mục có tồn kho = 0 nếu người dùng chọn tùy chọn "exclude_out_of_stock"
            $includeOutOfStock = !isset($filters['exclude_out_of_stock']) || $filters['exclude_out_of_stock'] !== 'true';
            
            Log::info('Dashboard search request', [
                'method' => $request->method(),
                'query' => $query,
                'category' => $category,
                'filters' => $filters,
                'includeOutOfStock' => $includeOutOfStock,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập từ khóa tìm kiếm',
                    'count' => 0,
                    'results' => []
                ]);
            }
            
            $results = [];
            $count = 0;
            
            // Tìm kiếm dựa vào loại
            switch ($category) {
                case 'materials':
                    $results = $this->searchMaterials($query, $filters, $includeOutOfStock);
                    break;
                case 'finished':
                    $results = $this->searchProducts($query, $filters, $includeOutOfStock);
                    break;
                case 'goods':
                    $results = $this->searchGoods($query, $filters, $includeOutOfStock);
                    break;
                case 'projects':
                    $projectResults = $this->searchProjects($query, $filters);
                    $rentalResults = $this->searchRentals($query, $filters);
                    $results = array_merge($projectResults, $rentalResults);
                    
                    Log::info('Projects category search results', [
                        'projects_count' => count($projectResults),
                        'rentals_count' => count($rentalResults),
                        'total_count' => count($results)
                    ]);
                    break;
                case 'customers':
                    $results = $this->searchCustomers($query, $filters);
                    break;
                case 'rentals':
                    $results = $this->searchRentals($query, $filters);
                    break;
                default:
                    // Tìm kiếm tất cả
                    Log::info('Searching all categories with query: ' . $query);
                    
                    $materialResults = $this->searchMaterials($query, $filters, $includeOutOfStock);
                    $productResults = $this->searchProducts($query, $filters, $includeOutOfStock);
                    $goodResults = $this->searchGoods($query, $filters, $includeOutOfStock);
                    $projectResults = $this->searchProjects($query, $filters);
                    $customerResults = $this->searchCustomers($query, $filters);
                    $rentalResults = $this->searchRentals($query, $filters);
                    
                    $results = array_merge(
                        $materialResults,
                        $productResults,
                        $goodResults,
                        $projectResults,
                        $customerResults,
                        $rentalResults
                    );
                    
                    Log::info('Combined search results', [
                        'materials_count' => count($materialResults),
                        'products_count' => count($productResults),
                        'goods_count' => count($goodResults),
                        'projects_count' => count($projectResults),
                        'customers_count' => count($customerResults),
                        'rentals_count' => count($rentalResults),
                        'total_count' => count($results)
                    ]);
                    
                    // Giới hạn kết quả
                    $results = array_slice($results, 0, 50);
            }
            
            $count = count($results);
            
            Log::info('Search completed', [
                'query' => $query,
                'category' => $category,
                'result_count' => $count
            ]);
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error in dashboard search', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $request->input('query'),
                'category' => $request->input('category', 'all')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(),
                'count' => 0,
                'results' => []
            ], 500);
        }
    }
    
    /**
     * Tìm kiếm vật tư
     */
    private function searchMaterials($query, $filters = [], $includeOutOfStock = false)
    {
        try {
            Log::info('Searching materials with query: ' . $query, [
                'filters' => $filters,
                'includeOutOfStock' => $includeOutOfStock
            ]);
            
            $materials = Material::where(function($q) use ($query) {
                    $q->where('code', 'like', "%{$query}%")
                      ->orWhere('name', 'like', "%{$query}%");
                      // Đã loại bỏ tìm kiếm theo notes
                });
                
            // Chỉ lấy vật tư có trong kho (số lượng > 0) nếu không bao gồm hàng ngoài kho
            if (!$includeOutOfStock) {
                $materials->whereHas('warehouseMaterials', function($q) {
                    $q->where('quantity', '>', 0)
                      ->whereHas('warehouse', function($warehouseQuery) {
                          $warehouseQuery->where('status', 'active');
                      });
                });
            }
            
            // Chỉ lấy vật tư có trạng thái không phải deleted và không bị ẩn
            $materials->where('status', '!=', 'deleted')
                     ->where('is_hidden', false);
                
            // Áp dụng các bộ lọc
            if (!empty($filters['warehouse_id'])) {
                $materials->whereHas('warehouseMaterials', function($q) use ($filters, $includeOutOfStock) {
                    $q->where('warehouse_id', $filters['warehouse_id'])
                      ->whereHas('warehouse', function($warehouseQuery) {
                          $warehouseQuery->where('status', 'active');
                      });
                    if (!$includeOutOfStock) {
                        $q->where('quantity', '>', 0);
                    }
                });
            }
            
            // Chỉ áp dụng bộ lọc trạng thái nếu cột status tồn tại trong bảng materials và không phải là 'all'
            if (!empty($filters['status']) && $filters['status'] !== 'all' && Schema::hasColumn('materials', 'status')) {
                $materials->where('status', $filters['status']);
            }
            
            $materials = $materials->limit(20)->get();
            
            Log::info('Found ' . $materials->count() . ' materials matching the query');
            
            return $materials->map(function($material) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material')
                    ->whereHas('warehouse', function($query) {
                        $query->where('status', 'active');
                    });
                    
                if (!$includeOutOfStock) {
                    $warehouseQuery->where('quantity', '>', 0);
                }
                
                $warehouseInfo = $warehouseQuery->first();
                    
                $warehouseName = '';
                if ($warehouseInfo && $warehouseInfo->warehouse) {
                    $warehouseName = $warehouseInfo->warehouse->name;
                }
                
                $status = property_exists($material, 'status') ? $material->status : 'active';
                
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'category' => 'materials',
                    'categoryName' => 'Vật tư',
                    'serial' => $material->code, // Sử dụng code làm serial
                    'date' => $material->created_at->format('d/m/Y'),
                    'location' => $warehouseName,
                    'status' => $status,
                    'detailUrl' => route('materials.show', $material->id),
                    'additionalInfo' => [
                        'supplier' => $material->supplier ? $material->supplier->name : 'N/A',
                        'quantity' => WarehouseMaterial::where('material_id', $material->id)
                            ->where('item_type', 'material')
                            ->whereHas('warehouse', function($query) {
                                $query->where('status', 'active');
                            })
                            ->sum('quantity'),
                        'unit' => $material->unit
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error in searchMaterials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Tìm kiếm thành phẩm
     */
    private function searchProducts($query, $filters = [], $includeOutOfStock = false)
    {
        try {
            Log::info('Searching products with query: ' . $query, [
                'filters' => $filters,
                'includeOutOfStock' => $includeOutOfStock
            ]);
            
            $products = Product::where(function($q) use ($query) {
                    $q->where('code', 'like', "%{$query}%")
                      ->orWhere('name', 'like', "%{$query}%");
                      // Đã loại bỏ tìm kiếm theo description
                });
                
            // Chỉ lấy thành phẩm có trong kho (số lượng > 0) nếu không bao gồm hàng ngoài kho
            if (!$includeOutOfStock) {
                $products->whereHas('warehouseMaterials', function($q) {
                    $q->where('quantity', '>', 0)
                      ->whereHas('warehouse', function($warehouseQuery) {
                          $warehouseQuery->where('status', 'active');
                      });
                });
            }
            
            // Chỉ lấy thành phẩm có trạng thái không phải deleted và không bị ẩn
            $products->where('status', '!=', 'deleted')
                     ->where('is_hidden', false);
                
            // Áp dụng các bộ lọc
            if (!empty($filters['warehouse_id'])) {
                $products->whereHas('warehouseMaterials', function($q) use ($filters, $includeOutOfStock) {
                    $q->where('warehouse_id', $filters['warehouse_id'])
                      ->whereHas('warehouse', function($warehouseQuery) {
                          $warehouseQuery->where('status', 'active');
                      });
                    if (!$includeOutOfStock) {
                        $q->where('quantity', '>', 0);
                    }
                });
            }
            
            // Chỉ áp dụng bộ lọc trạng thái nếu cột status tồn tại trong bảng products và không phải là 'all'
            if (!empty($filters['status']) && $filters['status'] !== 'all' && Schema::hasColumn('products', 'status')) {
                $products->where('status', $filters['status']);
            }
            
            $products = $products->limit(20)->get();
            
            Log::info('Found ' . $products->count() . ' products matching the query');
            
            return $products->map(function($product) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $product->id)
                    ->where('item_type', 'product')
                    ->whereHas('warehouse', function($query) {
                        $query->where('status', 'active');
                    });
                    
                if (!$includeOutOfStock) {
                    $warehouseQuery->where('quantity', '>', 0);
                }
                
                $warehouseInfo = $warehouseQuery->first();
                    
                $warehouseName = '';
                if ($warehouseInfo && $warehouseInfo->warehouse) {
                    $warehouseName = $warehouseInfo->warehouse->name;
                }
                
                $status = property_exists($product, 'status') ? $product->status : 'active';
                
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'category' => 'finished',
                    'categoryName' => 'Thành phẩm',
                    'serial' => $product->code, // Sử dụng code làm serial
                    'date' => $product->created_at->format('d/m/Y'),
                    'location' => $warehouseName,
                    'status' => $status,
                    'detailUrl' => route('products.show', $product->id),
                    'additionalInfo' => [
                        'manufactureDate' => $product->created_at->format('d/m/Y'),
                        'quantity' => WarehouseMaterial::where('material_id', $product->id)
                            ->where('item_type', 'product')
                            ->whereHas('warehouse', function($query) {
                                $query->where('status', 'active');
                            })
                            ->sum('quantity'),
                        'project' => 'N/A' // Có thể cập nhật nếu có thông tin dự án
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error in searchProducts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Tìm kiếm hàng hóa
     */
    private function searchGoods($query, $filters = [], $includeOutOfStock = false)
    {
        try {
            Log::info('Searching goods with query: ' . $query, [
                'filters' => $filters,
                'includeOutOfStock' => $includeOutOfStock
            ]);
            
            $goods = Good::where(function($q) use ($query) {
                    $q->where('code', 'like', "%{$query}%")
                      ->orWhere('name', 'like', "%{$query}%");
                      // Đã loại bỏ tìm kiếm theo serial và notes
                });
                
            // Chỉ lấy hàng hóa có trong kho (số lượng > 0) nếu không bao gồm hàng ngoài kho
            if (!$includeOutOfStock) {
                $goods->whereHas('warehouseMaterials', function($q) {
                    $q->where('quantity', '>', 0)
                      ->whereHas('warehouse', function($warehouseQuery) {
                          $warehouseQuery->where('status', 'active');
                      });
                });
            }
            
            // Chỉ lấy hàng hóa có trạng thái không phải deleted và không bị ẩn
            $goods->where('is_hidden', false)
                  ->where('status', '!=', 'deleted');
                
            // Áp dụng các bộ lọc
            if (!empty($filters['warehouse_id'])) {
                $goods->whereHas('warehouseMaterials', function($q) use ($filters, $includeOutOfStock) {
                    $q->where('warehouse_id', $filters['warehouse_id'])
                      ->whereHas('warehouse', function($warehouseQuery) {
                          $warehouseQuery->where('status', 'active');
                      });
                    if (!$includeOutOfStock) {
                        $q->where('quantity', '>', 0);
                    }
                });
            }
            
            // Chỉ áp dụng bộ lọc trạng thái nếu cột status tồn tại trong bảng goods và không phải là 'all'
            if (!empty($filters['status']) && $filters['status'] !== 'all' && Schema::hasColumn('goods', 'status')) {
                $goods->where('status', $filters['status']);
            }
            
            $goods = $goods->limit(20)->get();
            
            Log::info('Found ' . $goods->count() . ' goods matching the query');
            
            return $goods->map(function($good) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $good->id)
                    ->where('item_type', 'good')
                    ->whereHas('warehouse', function($query) {
                        $query->where('status', 'active');
                    });
                    
                if (!$includeOutOfStock) {
                    $warehouseQuery->where('quantity', '>', 0);
                }
                
                $warehouseInfo = $warehouseQuery->first();
                    
                $warehouseName = '';
                if ($warehouseInfo && $warehouseInfo->warehouse) {
                    $warehouseName = $warehouseInfo->warehouse->name;
                }
                
                $status = property_exists($good, 'status') ? $good->status : 'active';
                
                return [
                    'id' => $good->id,
                    'code' => $good->code,
                    'name' => $good->name,
                    'category' => 'goods',
                    'categoryName' => 'Hàng hóa',
                    'serial' => $good->serial ?: $good->code,
                    'date' => $good->created_at->format('d/m/Y'),
                    'location' => $warehouseName,
                    'status' => $status,
                    'detailUrl' => route('goods.show', $good->id),
                    'additionalInfo' => [
                        'distributor' => $good->supplier ? $good->supplier->name : 'N/A',
                        'price' => 'Liên hệ',
                        'quantity' => WarehouseMaterial::where('material_id', $good->id)
                            ->where('item_type', 'good')
                            ->whereHas('warehouse', function($query) {
                                $query->where('status', 'active');
                            })
                            ->sum('quantity')
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error in searchGoods', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Tìm kiếm dự án
     */
    private function searchProjects($query, $filters = [])
    {
        try {
            Log::info('Searching projects with query: ' . $query, [
                'filters' => $filters
            ]);
            
            // Escape ký tự đặc biệt trong chuỗi tìm kiếm
            $searchQuery = str_replace(['%', '_'], ['\%', '\_'], $query);
            
            $projects = Project::where(function($q) use ($searchQuery) {
                    $q->where('project_code', 'like', "%{$searchQuery}%")
                      ->orWhere('project_name', 'like', "%{$searchQuery}%")
                      ->orWhere(DB::raw('LOWER(project_code)'), 'like', '%' . strtolower($searchQuery) . '%')
                      ->orWhere(DB::raw('LOWER(project_name)'), 'like', '%' . strtolower($searchQuery) . '%');
                });
                
            // Áp dụng các bộ lọc
            if (!empty($filters['status']) && Schema::hasColumn('projects', 'status')) {
                $projects->where('status', $filters['status']);
            }
            
            if (!empty($filters['customer_id'])) {
                $projects->where('customer_id', $filters['customer_id']);
            }
            
            $projects = $projects->limit(20)->get();
            
            Log::info('Found ' . $projects->count() . ' projects matching the query');
            
            return $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'code' => $project->project_code,
                    'name' => $project->project_name,
                    'category' => 'projects',
                    'categoryName' => 'Dự án',
                    'serial' => 'PRJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT),
                    'date' => $project->created_at->format('d/m/Y'),
                    'location' => $project->description ?? 'N/A',
                    'status' => $project->status ?? 'active',
                    'detailUrl' => route('projects.show', $project->id),
                    'additionalInfo' => [
                        'customer' => $project->customer ? $project->customer->name : 'N/A',
                        'startDate' => $project->start_date ? date('d/m/Y', strtotime($project->start_date)) : 'N/A',
                        'endDate' => $project->end_date ? date('d/m/Y', strtotime($project->end_date)) : 'N/A',
                        'warrantyPeriod' => $project->warranty_period . ' tháng',
                        'remainingWarrantyDays' => $project->remaining_warranty_days,
                        'employee' => $project->employee ? $project->employee->name : 'N/A'
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error in searchProjects', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Tìm kiếm khách hàng
     */
    private function searchCustomers($query, $filters = [])
    {
        try {
            Log::info('Searching customers with query: ' . $query, [
                'filters' => $filters
            ]);
            
            // Escape ký tự đặc biệt trong chuỗi tìm kiếm
            $searchQuery = str_replace(['%', '_'], ['\%', '\_'], $query);
            
            $customers = Customer::where(function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere(DB::raw('LOWER(name)'), 'like', '%' . strtolower($searchQuery) . '%')
                      ->orWhere('phone', 'like', "%{$searchQuery}%")
                      ->orWhere('company_phone', 'like', "%{$searchQuery}%")
                      ->orWhere('email', 'like', "%{$searchQuery}%")
                      ->orWhere('company_name', 'like', "%{$searchQuery}%");
                });
                
            $customers = $customers->limit(20)->get();
            
            Log::info('Found ' . $customers->count() . ' customers matching the query');
            
            return $customers->map(function($customer) {
                // Lấy danh sách dự án của khách hàng
                $projects = $customer->projects()->limit(5)->get()->map(function($project) {
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'startDate' => $project->start_date ? date('d/m/Y', strtotime($project->start_date)) : 'N/A',
                        'status' => $project->status
                    ];
                })->toArray();
                
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'category' => 'customers',
                    'categoryName' => 'Khách hàng',
                    'serial' => 'CUS-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
                    'date' => $customer->created_at->format('d/m/Y'),
                    'location' => $customer->address,
                    'status' => $customer->has_account ? ($customer->is_locked ? 'Khóa' : 'Hoạt động') : 'Chưa có tài khoản',
                    'detailUrl' => route('customers.show', $customer->id),
                    'additionalInfo' => [
                        'phone' => $customer->phone,
                        'companyPhone' => $customer->company_phone,
                        'email' => $customer->email,
                        'companyName' => $customer->company_name,
                        'address' => $customer->address,
                        'relatedProjects' => $projects
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error in searchCustomers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Tìm kiếm phiếu cho thuê
     */
    private function searchRentals($query, $filters = [])
    {
        try {
            Log::info('Searching rentals with query: ' . $query, [
                'filters' => $filters
            ]);
            
            // Escape ký tự đặc biệt trong chuỗi tìm kiếm
            $searchQuery = str_replace(['%', '_'], ['\%', '\_'], $query);
            
            $rentals = Rental::where(function($q) use ($searchQuery) {
                    $q->where('rental_code', 'like', "%{$searchQuery}%")
                      ->orWhere('rental_name', 'like', "%{$searchQuery}%")
                      ->orWhere(DB::raw('LOWER(rental_code)'), 'like', '%' . strtolower($searchQuery) . '%')
                      ->orWhere(DB::raw('LOWER(rental_name)'), 'like', '%' . strtolower($searchQuery) . '%');
                })
                ->with(['customer', 'employee']);
                
            // Áp dụng các bộ lọc
            if (!empty($filters['rental_status'])) {
                switch ($filters['rental_status']) {
                    case 'overdue':
                        // Lấy những phiếu cho thuê quá hạn
                        $rentals->where('due_date', '<', now());
                        break;
                    case 'active':
                        // Lấy những phiếu cho thuê đang hoạt động (chưa quá hạn)
                        $rentals->where('due_date', '>=', now());
                        break;
                    case 'completed':
                        // Có thể thêm logic cho phiếu đã hoàn thành nếu cần
                        break;
                }
            }
            
            if (!empty($filters['customer_id'])) {
                $rentals->where('customer_id', $filters['customer_id']);
            }
            
            $rentals = $rentals->limit(20)->get();
            
            Log::info('Found ' . $rentals->count() . ' rentals matching the query');
            
            return $rentals->map(function($rental) {
                return [
                    'id' => $rental->id,
                    'code' => $rental->rental_code,
                    'name' => $rental->rental_name,
                    'category' => 'rentals',
                    'categoryName' => 'Phiếu cho thuê',
                    'serial' => $rental->rental_code,
                    'date' => $rental->rental_date ? date('d/m/Y', strtotime($rental->rental_date)) : 'N/A',
                    'location' => 'N/A', // Phiếu cho thuê không có vị trí cụ thể
                    'status' => $rental->isOverdue() ? 'Quá hạn' : 'Đang hoạt động',
                    'detailUrl' => route('rentals.show', $rental->id),
                    'additionalInfo' => [
                        'customer' => $rental->customer ? $rental->customer->name : 'N/A',
                        'employee' => $rental->employee ? $rental->employee->name : 'N/A',
                        'rentalDate' => $rental->rental_date ? date('d/m/Y', strtotime($rental->rental_date)) : 'N/A',
                        'dueDate' => $rental->due_date ? date('d/m/Y', strtotime($rental->due_date)) : 'N/A',
                        'daysRemaining' => $rental->daysRemaining(),
                        'isOverdue' => $rental->isOverdue()
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error in searchRentals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Lấy dữ liệu biểu đồ theo khoảng thời gian và loại thời gian
     */
    private function getChartDataByTimeRange($category, $timeRangeType, $startDate, $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        $labels = [];
        $importData = [];
        $exportData = [];
        $damagedData = [];
        
        switch ($timeRangeType) {
            case 'day':
                // Hiển thị theo từng ngày - hiển thị tất cả ngày trong khoảng thời gian
                $current = $start->copy();
                while ($current->lte($end)) {
                    $labels[] = $current->format('d/m/Y');
                    
                    $dayImports = $this->getImportsForDate($category, $current);
                    $dayExports = $this->getExportsForDate($category, $current);
                    $dayDamaged = $this->getDamagedForDate($category, $current);
                    
                    $importData[] = $dayImports;
                    $exportData[] = $dayExports;
                    $damagedData[] = $dayDamaged;
                    
                    $current->addDay();
                }
                break;
                
            case 'week':
                // Hiển thị theo từng tuần
                $current = $start->copy();
                $weekCount = 1;
                while ($current->lte($end)) {
                    $weekStart = $current->copy()->startOfWeek();
                    $weekEnd = $current->copy()->endOfWeek();
                    
                    // Đảm bảo không vượt quá ngày kết thúc
                    if ($weekEnd->gt($end)) {
                        $weekEnd = $end;
                    }
                    
                    $labels[] = "Tuần {$weekCount} ({$weekStart->format('d/m/Y')} đến {$weekEnd->format('d/m/Y')})";
                    
                    $weekImports = $this->getImportsForPeriod($category, $weekStart, $weekEnd);
                    $weekExports = $this->getExportsForPeriod($category, $weekStart, $weekEnd);
                    $weekDamaged = $this->getDamagedForPeriod($category, $weekStart, $weekEnd);
                    
                    $importData[] = $weekImports;
                    $exportData[] = $weekExports;
                    $damagedData[] = $weekDamaged;
                    
                    $current->addWeek();
                    $weekCount++;
                }
                break;
                
            case 'month':
                // Hiển thị theo từng tháng
                $current = $start->copy();
                while ($current->lte($end)) {
                    $monthStart = $current->copy()->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();
                    
                    // Đảm bảo không vượt quá ngày kết thúc
                    if ($monthEnd->gt($end)) {
                        $monthEnd = $end;
                    }
                    
                    $labels[] = "Tháng {$current->format('m/Y')} ({$monthStart->format('d/m/Y')} đến {$monthEnd->format('d/m/Y')})";
                    
                    $monthImports = $this->getImportsForPeriod($category, $monthStart, $monthEnd);
                    $monthExports = $this->getExportsForPeriod($category, $monthStart, $monthEnd);
                    $monthDamaged = $this->getDamagedForPeriod($category, $monthStart, $monthEnd);
                    
                    $importData[] = $monthImports;
                    $exportData[] = $monthExports;
                    $damagedData[] = $monthDamaged;
                    
                    $current->addMonth();
                }
                break;
                
            case 'year':
                // Hiển thị theo từng năm
                $current = $start->copy();
                while ($current->lte($end)) {
                    $yearStart = $current->copy()->startOfYear();
                    $yearEnd = $current->copy()->endOfYear();
                    
                    // Đảm bảo không vượt quá ngày kết thúc
                    if ($yearEnd->gt($end)) {
                        $yearEnd = $end;
                    }
                    
                    $labels[] = "Năm {$current->format('Y')} ({$yearStart->format('d/m/Y')} đến {$yearEnd->format('d/m/Y')})";
                    
                    $yearImports = $this->getImportsForPeriod($category, $yearStart, $yearEnd);
                    $yearExports = $this->getExportsForPeriod($category, $yearStart, $yearEnd);
                    $yearDamaged = $this->getDamagedForPeriod($category, $yearStart, $yearEnd);
                    
                    $importData[] = $yearImports;
                    $exportData[] = $yearExports;
                    $damagedData[] = $yearDamaged;
                    
                    $current->addYear();
                }
                break;
                
            default:
                // Mặc định là tháng
                $current = $start->copy();
                while ($current->lte($end)) {
                    $monthStart = $current->copy()->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();
                    
                    if ($monthEnd->gt($end)) {
                        $monthEnd = $end;
                    }
                    
                    $labels[] = "Tháng {$current->format('m/Y')}";
                    
                    $monthImports = $this->getImportsForPeriod($category, $monthStart, $monthEnd);
                    $monthExports = $this->getExportsForPeriod($category, $monthStart, $monthEnd);
                    $monthDamaged = $this->getDamagedForPeriod($category, $monthStart, $monthEnd);
                    
                    $importData[] = $monthImports;
                    $exportData[] = $monthExports;
                    $damagedData[] = $monthDamaged;
                    
                    $current->addMonth();
                }
                break;
        }
        
        return [
            'labels' => $labels,
            'data' => [
                'import' => $importData,
                'export' => $exportData,
                'damaged' => $damagedData
            ]
        ];
    }

    /**
     * Lấy dữ liệu nhập kho cho một ngày cụ thể
     */
    private function getImportsForDate($category, $date)
    {
        $itemType = $this->getItemTypeByCategory($category);
        
        $query = DB::table('warehouse_materials')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_materials.warehouse_id')
            ->where('warehouse_materials.item_type', $itemType)
            ->where('warehouses.status', 'active')
            ->whereDate('warehouse_materials.created_at', $date);
            
        // Thêm join và điều kiện theo loại item
        if ($itemType === 'material') {
            $query->join('materials', 'materials.id', '=', 'warehouse_materials.material_id')
                  ->where('materials.status', '!=', 'deleted');
        } elseif ($itemType === 'product') {
            $query->join('products', 'products.id', '=', 'warehouse_materials.material_id')
                  ->where('products.status', '!=', 'deleted');
        } elseif ($itemType === 'good') {
            $query->join('goods', 'goods.id', '=', 'warehouse_materials.material_id')
                  ->where('goods.status', '!=', 'deleted');
        }
        
        return $query->sum('warehouse_materials.quantity');
    }

    /**
     * Lấy dữ liệu xuất kho cho một ngày cụ thể
     */
    private function getExportsForDate($category, $date)
    {
        $itemType = $this->getItemTypeByCategory($category);
        
        if ($itemType === 'material') {
            return DB::table('assembly_materials')
                ->whereDate('created_at', $date)
                ->sum('quantity');
        } else {
            return DB::table('dispatch_items')
                ->where('item_type', $itemType)
                ->whereDate('created_at', $date)
                ->sum('quantity');
        }
    }

    /**
     * Lấy dữ liệu hư hỏng cho một ngày cụ thể
     */
    private function getDamagedForDate($category, $date)
    {
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();
        
        switch ($category) {
            case 'materials':
                return $this->calculateDamagedMaterials($startDate, $endDate);
            case 'products':
                return $this->calculateDamagedProducts($startDate, $endDate);
            case 'goods':
                return $this->calculateDamagedGoods($startDate, $endDate);
            default:
                return 0;
        }
    }

    /**
     * Lấy dữ liệu nhập kho cho một khoảng thời gian
     */
    private function getImportsForPeriod($category, $startDate, $endDate)
    {
        $itemType = $this->getItemTypeByCategory($category);
        
        $query = DB::table('warehouse_materials')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_materials.warehouse_id')
            ->where('warehouse_materials.item_type', $itemType)
            ->where('warehouses.status', 'active')
            ->whereBetween('warehouse_materials.created_at', [$startDate, $endDate]);
            
        // Thêm join và điều kiện theo loại item
        if ($itemType === 'material') {
            $query->join('materials', 'materials.id', '=', 'warehouse_materials.material_id')
                  ->where('materials.status', '!=', 'deleted');
        } elseif ($itemType === 'product') {
            $query->join('products', 'products.id', '=', 'warehouse_materials.material_id')
                  ->where('products.status', '!=', 'deleted');
        } elseif ($itemType === 'good') {
            $query->join('goods', 'goods.id', '=', 'warehouse_materials.material_id')
                  ->where('goods.status', '!=', 'deleted');
        }
        
        return $query->sum('warehouse_materials.quantity');
    }

    /**
     * Lấy dữ liệu xuất kho cho một khoảng thời gian
     */
    private function getExportsForPeriod($category, $startDate, $endDate)
    {
        $itemType = $this->getItemTypeByCategory($category);
        
        if ($itemType === 'material') {
            return DB::table('assembly_materials')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');
        } else {
            return DB::table('dispatch_items')
                ->where('item_type', $itemType)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');
        }
    }

    /**
     * Lấy dữ liệu hư hỏng cho một khoảng thời gian
     */
    private function getDamagedForPeriod($category, $startDate, $endDate)
    {
        switch ($category) {
            case 'materials':
                return $this->calculateDamagedMaterials($startDate, $endDate);
            case 'products':
                return $this->calculateDamagedProducts($startDate, $endDate);
            case 'goods':
                return $this->calculateDamagedGoods($startDate, $endDate);
            default:
                return 0;
        }
    }

    /**
     * Lấy item_type dựa trên category
     */
    private function getItemTypeByCategory($category)
    {
        switch ($category) {
            case 'materials':
                return 'material';
            case 'products':
                return 'product';
            case 'goods':
                return 'good';
            default:
                return 'material';
        }
    }

    /**
     * Tính tổng hư hỏng vật tư dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
     */
    private function calculateDamagedMaterials($startDate, $endDate)
    {
        try {
            $totalDamaged = 0;
            
            // Lấy tất cả vật tư
            $materials = Material::where('status', '!=', 'deleted')
                               ->where('is_hidden', false)
                               ->get();
            
            foreach ($materials as $material) {
                $materialDamaged = $this->calculateItemDamaged('material', $material->id, $startDate, $endDate);
                $totalDamaged += $materialDamaged;
            }
            
            Log::info('Calculated damaged materials', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_damaged' => $totalDamaged
            ]);
            
            return $totalDamaged;
        } catch (\Exception $e) {
            Log::error('Error calculating damaged materials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Tính tổng hư hỏng thành phẩm dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
     */
    private function calculateDamagedProducts($startDate, $endDate)
    {
        try {
            $totalDamaged = 0;
            
            // Lấy tất cả thành phẩm
            $products = Product::where('status', '!=', 'deleted')
                             ->where('is_hidden', false)
                             ->get();
            
            foreach ($products as $product) {
                $productDamaged = $this->calculateItemDamaged('product', $product->id, $startDate, $endDate);
                $totalDamaged += $productDamaged;
            }
            
            Log::info('Calculated damaged products', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_damaged' => $totalDamaged
            ]);
            
            return $totalDamaged;
        } catch (\Exception $e) {
            Log::error('Error calculating damaged products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Tính tổng hư hỏng hàng hóa dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
     */
    private function calculateDamagedGoods($startDate, $endDate)
    {
        try {
            $totalDamaged = 0;
            
            // Lấy tất cả hàng hóa
            $goods = Good::where('status', '!=', 'deleted')
                       ->where('is_hidden', false)
                       ->get();
            
            foreach ($goods as $good) {
                $goodDamaged = $this->calculateItemDamaged('good', $good->id, $startDate, $endDate);
                $totalDamaged += $goodDamaged;
            }
            
            Log::info('Calculated damaged goods', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_damaged' => $totalDamaged
            ]);
            
            return $totalDamaged;
        } catch (\Exception $e) {
            Log::error('Error calculating damaged goods', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Tính số lượng hư hỏng cho một item cụ thể dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
     */
    private function calculateItemDamaged($itemType, $itemId, $startDate, $endDate)
    {
        try {
            // Lấy thông tin về kho nào dùng để tính tồn kho cho item này
            $item = null;
            $inventoryWarehouses = [];
            
            switch ($itemType) {
                case 'material':
                    $item = Material::find($itemId);
                    break;
                case 'product':
                    $item = Product::find($itemId);
                    break;
                case 'good':
                    $item = Good::find($itemId);
                    break;
            }
            
            if (!$item) {
                return 0;
            }
            
            // Lấy danh sách kho dùng để tính tồn kho
            if (is_array($item->inventory_warehouses) && !empty($item->inventory_warehouses)) {
                if (in_array('all', $item->inventory_warehouses)) {
                    // Nếu là 'all', tất cả kho đều dùng để tính tồn kho
                    return 0; // Không có hư hỏng
                } else {
                    $inventoryWarehouses = $item->inventory_warehouses;
                }
            } else {
                // Nếu không có cấu hình, tất cả kho đều dùng để tính tồn kho
                return 0; // Không có hư hỏng
            }
            
            // Tính tổng nhập vào các kho không dùng để tính tồn kho
            // Sử dụng bảng inventory_import_materials để theo dõi nhập kho
            $importToNonInventoryWarehouses = DB::table('inventory_import_materials')
                ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                ->join('warehouses', 'warehouses.id', '=', 'inventory_imports.warehouse_id')
                ->where('inventory_import_materials.item_type', $itemType)
                ->where('inventory_import_materials.material_id', $itemId)
                ->where('warehouses.status', 'active')
                ->whereNotIn('warehouses.id', $inventoryWarehouses)
                ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                ->sum('inventory_import_materials.quantity');
            
            // Tính tổng xuất khỏi các kho không dùng để tính tồn kho
            // Sử dụng bảng dispatch_items để theo dõi xuất kho
            $exportFromNonInventoryWarehouses = DB::table('dispatch_items')
                ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                ->join('warehouses', 'warehouses.id', '=', 'dispatches.warehouse_id')
                ->where('dispatch_items.item_type', $itemType)
                ->where('dispatch_items.item_id', $itemId)
                ->where('dispatches.status', 'in', ['approved', 'completed'])
                ->where('warehouses.status', 'active')
                ->whereNotIn('warehouses.id', $inventoryWarehouses)
                ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                ->sum('dispatch_items.quantity');
            
            // Số lượng hư hỏng = Nhập vào kho không tính tồn kho - Xuất khỏi kho không tính tồn kho
            $damaged = $importToNonInventoryWarehouses - $exportFromNonInventoryWarehouses;
            
            // Đảm bảo không âm
            return max(0, $damaged);
            
        } catch (\Exception $e) {
            Log::error('Error calculating item damaged', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }
} 