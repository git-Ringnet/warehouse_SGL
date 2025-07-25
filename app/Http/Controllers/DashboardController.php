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
        // Tổng nhập kho vật tư
        $totalImport = WarehouseMaterial::where('item_type', 'material')
            ->sum('quantity');

        // Tổng xuất kho vật tư (số lượng đã sử dụng trong lắp ráp)
        $totalExport = DB::table('assembly_materials')
            ->sum('quantity');

        // Tổng hư hỏng - số lượng vật tư trong kiểm thử có kết quả là fail
        $totalDamaged = DB::table('testing_items')
            ->where('item_type', 'material')
            ->where('result', 'fail')
            ->sum('quantity');

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
        // Tổng nhập kho thành phẩm
        $totalImport = WarehouseMaterial::where('item_type', 'product')
            ->sum('quantity');

        // Tổng xuất kho thành phẩm
        $totalExport = DB::table('dispatch_items')
            ->where('item_type', 'product')
            ->sum('quantity');

        // Tổng hư hỏng - lấy từ testing.fail_quantity với test_type là product hoặc finished_product
        $totalDamaged = DB::table('testings')
            ->whereIn('test_type', ['product', 'finished_product'])
            ->sum('fail_quantity');

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
        // Tổng nhập kho hàng hóa
        $totalImport = WarehouseMaterial::where('item_type', 'good')
            ->sum('quantity');

        // Tổng xuất kho hàng hóa  
        $totalExport = DB::table('dispatch_items')
            ->where('item_type', 'good')
            ->sum('quantity');

        // Tổng hư hỏng - số lượng hàng hóa kiểm thử không đạt
        // Trong db, hàng hóa được kiểm thử dưới test_type = 'finished_product'
        // và item_type = 'finished_product' trong bảng testing_items
        $totalDamaged = DB::table('testing_items')
            ->join('testings', 'testings.id', '=', 'testing_items.testing_id')
            ->where('testing_items.item_type', 'finished_product')
            ->where('testing_items.result', 'fail')
            ->whereNotNull('testing_items.good_id')
            ->sum('testing_items.quantity');

        // Log thông tin tính toán số lượng hàng hóa hư hỏng
        Log::info('Thống kê hàng hóa hư hỏng', [
            'total_damaged' => $totalDamaged,
            'query' => 'SELECT SUM(quantity) FROM testing_items WHERE item_type = "finished_product" AND result = "fail" AND good_id IS NOT NULL'
        ]);

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
            
            Log::info('Getting inventory overview chart data', [
                'category' => $category,
                'request_url' => $request->fullUrl()
            ]);
            
            // Lấy khoảng thời gian (mặc định là 6 tháng gần nhất)
            $period = $request->input('period', 'month');
            $months = 6;
            
            // Tạo mảng nhãn thời gian
            $labels = [];
            $currentDate = now();
            
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->subMonths($i);
                $labels[] = 'Tháng ' . $date->format('n');
            }
            
            // Lấy dữ liệu theo loại
            switch ($category) {
                case 'materials':
                    $data = $this->getMaterialsChartData($months);
                    break;
                case 'products':
                    $data = $this->getProductsChartData($months);
                    break;
                case 'goods':
                    $data = $this->getGoodsChartData($months);
                    break;
                default:
                    $data = $this->getMaterialsChartData($months);
            }
            
            // Kiểm tra xem có dữ liệu không
            $hasData = false;
            foreach (['import', 'export', 'damaged'] as $key) {
                if (isset($data[$key]) && array_sum($data[$key]) > 0) {
                    $hasData = true;
                    break;
                }
            }
            
            // Nếu không có dữ liệu, tạo dữ liệu mẫu
            if (!$hasData) {
                Log::warning('No data found for chart, returning sample data', [
                    'category' => $category
                ]);
                
                // Dữ liệu mẫu theo loại
                switch ($category) {
                    case 'materials':
                        $data = [
                            'import' => [450, 500, 550, 600, 650, 700],
                            'export' => [300, 350, 400, 450, 500, 550],
                            'damaged' => [50, 45, 60, 55, 65, 70]
                        ];
                        break;
                    case 'products':
                        $data = [
                            'import' => [200, 220, 240, 260, 280, 300],
                            'export' => [180, 200, 220, 240, 260, 280],
                            'damaged' => [20, 25, 30, 35, 40, 45]
                        ];
                        break;
                    case 'goods':
                        $data = [
                            'import' => [350, 370, 390, 410, 430, 450],
                            'export' => [320, 340, 360, 380, 400, 420],
                            'damaged' => [30, 35, 40, 45, 50, 55]
                        ];
                        break;
                    default:
                        $data = [
                            'import' => [450, 500, 550, 600, 650, 700],
                            'export' => [300, 350, 400, 450, 500, 550],
                            'damaged' => [50, 45, 60, 55, 65, 70]
                        ];
                }
            }
            
            $response = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Nhập kho',
                        'data' => $data['import'],
                        'backgroundColor' => '#10b981',
                    ],
                    [
                        'label' => 'Xuất kho',
                        'data' => $data['export'],
                        'backgroundColor' => '#ef4444',
                    ],
                    [
                        'label' => 'Hư hỏng',
                        'data' => $data['damaged'],
                        'backgroundColor' => '#f59e0b',
                    ]
                ]
            ];
            
            Log::info('Inventory overview chart data generated successfully', [
                'category' => $category,
                'labels' => $labels,
                'data_sample' => [
                    'import' => array_slice($data['import'], 0, 2),
                    'export' => array_slice($data['export'], 0, 2),
                    'damaged' => array_slice($data['damaged'], 0, 2),
                ]
            ]);
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error in getInventoryOverviewChart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category' => $request->input('category', 'materials')
            ]);
            
            // Return sample data in case of error
            return response()->json([
                'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                'datasets' => [
                    [
                        'label' => 'Nhập kho',
                        'data' => [450, 500, 550, 600, 650, 700],
                        'backgroundColor' => '#10b981',
                    ],
                    [
                        'label' => 'Xuất kho',
                        'data' => [300, 350, 400, 450, 500, 550],
                        'backgroundColor' => '#ef4444',
                    ],
                    [
                        'label' => 'Hư hỏng',
                        'data' => [50, 45, 60, 55, 65, 70],
                        'backgroundColor' => '#f59e0b',
                    ]
                ]
            ]);
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
            
            // Nếu tổng là 0, trả về dữ liệu mẫu
            if ($total == 0) {
                Log::warning('No inventory data found, returning sample data');
                return response()->json([
                    'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                    'data' => [60, 30, 10]
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
            
            // Return sample data in case of error
            return response()->json([
                'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                'data' => [60, 30, 10]
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
                
                // Chỉ thêm kho có số lượng > 0
                if ($warehouseTotal > 0) {
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
            }
            
            // Sắp xếp kho theo số lượng giảm dần
            usort($warehouseData, function($a, $b) {
                return $b['total'] <=> $a['total'];
            });
            
            // Giới hạn số lượng kho hiển thị (nếu có quá nhiều kho)
            $maxWarehouses = 10;
            if (count($warehouseData) > $maxWarehouses) {
                // Tính tổng các kho phụ còn lại
                $othersTotal = 0;
                $othersDetail = [
                    'id' => 'others',
                    'name' => 'Kho khác',
                    'material_count' => 0,
                    'product_count' => 0,
                    'good_count' => 0,
                    'total' => 0
                ];
                
                for ($i = $maxWarehouses - 1; $i < count($warehouseData); $i++) {
                    $othersTotal += $warehouseData[$i]['total'];
                    $othersDetail['material_count'] += $warehouseData[$i]['material_count'];
                    $othersDetail['product_count'] += $warehouseData[$i]['product_count'];
                    $othersDetail['good_count'] += $warehouseData[$i]['good_count'];
                }
                
                $othersDetail['total'] = $othersTotal;
                
                // Cắt mảng và thêm mục "Kho khác"
                $warehouseData = array_slice($warehouseData, 0, $maxWarehouses - 1);
                if ($othersTotal > 0) {
                    $warehouseData[] = $othersDetail;
                }
            }
            
            // Tính phần trăm cho mỗi kho
            foreach ($warehouseData as $warehouse) {
                if ($total > 0) {
                    $percent = round(($warehouse['total'] / $total) * 100);
                    if ($percent > 0) {
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
                            'address' => $warehouse['address'] ?? null
                        ];
                    }
                }
            }
            
            // Nếu không có dữ liệu, trả về dữ liệu mẫu
            if (empty($labels)) {
                Log::warning('No warehouse data found, returning sample data');
                return response()->json([
                    'labels' => ['Kho chính', 'Kho phụ'],
                    'data' => [70, 30],
                    'colors' => array_slice($colors, 0, 2),
                    'details' => [
                        ['id' => 'sample1', 'name' => 'Kho chính', 'material_count' => 700, 'product_count' => 300, 'good_count' => 100, 'total' => 1100, 'percent' => 70],
                        ['id' => 'sample2', 'name' => 'Kho phụ', 'material_count' => 300, 'product_count' => 100, 'good_count' => 70, 'total' => 470, 'percent' => 30]
                    ]
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
                'item_type' => $itemType ?: 'all'
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
            
            // Return sample data in case of error
            return response()->json([
                'labels' => ['Kho A', 'Kho B', 'Kho C'],
                'data' => [50, 30, 20],
                'colors' => ['#3b82f6', '#10b981', '#f59e0b'],
                'details' => [
                    ['id' => 'sample1', 'name' => 'Kho A', 'material_count' => 500, 'product_count' => 200, 'good_count' => 100, 'total' => 800, 'percent' => 50],
                    ['id' => 'sample2', 'name' => 'Kho B', 'material_count' => 300, 'product_count' => 150, 'good_count' => 30, 'total' => 480, 'percent' => 30],
                    ['id' => 'sample3', 'name' => 'Kho C', 'material_count' => 200, 'product_count' => 100, 'good_count' => 20, 'total' => 320, 'percent' => 20]
                ],
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
                    $importCount = WarehouseMaterial::where('item_type', 'material')->sum('quantity');
                    
                    // Tổng xuất kho (số lượng đã sử dụng trong lắp ráp)
                    $exportCount = DB::table('assembly_materials')->sum('quantity');
                    
                    // Tổng hư hỏng - số lượng vật tư trong kiểm thử có kết quả là fail
                    $damagedCount = DB::table('testing_items')
                        ->where('item_type', 'material')
                        ->where('result', 'fail')
                        ->sum('quantity');
                    
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
                            ->where('item_type', 'material')
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate)
                            ->sum('quantity');
                        
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
                    
                    // Số lượng hư hỏng
                    $damagedCount = DB::table('testing_items')
                        ->join('testings', 'testings.id', '=', 'testing_items.testing_id')
                        ->where('testing_items.item_type', 'material')
                        ->where('testing_items.result', 'fail')
                        ->whereBetween('testings.test_date', [$startDate, $endDate])
                        ->sum('testing_items.quantity');
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
                    $importCount = WarehouseMaterial::where('item_type', 'product')->sum('quantity');
                    
                    // Tổng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->where('item_type', 'product')
                        ->sum('quantity');
                    
                    // Tổng hư hỏng
                    $damagedCount = DB::table('testings')
                        ->whereIn('test_type', ['product', 'finished_product'])
                        ->sum('fail_quantity');
                    
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
                            ->where('item_type', 'product')
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate)
                            ->sum('quantity');
                        
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
                    
                    // Số lượng hư hỏng
                    $damagedCount = DB::table('testings')
                        ->whereIn('test_type', ['product', 'finished_product'])
                        ->whereBetween('test_date', [$startDate, $endDate])
                        ->sum('fail_quantity');
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
                    $importCount = WarehouseMaterial::where('item_type', 'good')->sum('quantity');
                    
                    // Tổng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->where('item_type', 'good')
                        ->sum('quantity');
                    
                    // Tổng hư hỏng
                    $damagedCount = DB::table('testing_items')
                        ->join('testings', 'testings.id', '=', 'testing_items.testing_id')
                        ->where('testing_items.item_type', 'finished_product')
                        ->where('testing_items.result', 'fail')
                        ->whereNotNull('testing_items.good_id')
                        ->sum('testing_items.quantity');
                    
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
                            ->where('item_type', 'good')
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate)
                            ->sum('quantity');
                        
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
                    
                    // Số lượng hư hỏng
                    $damagedCount = DB::table('testing_items')
                        ->join('testings', 'testings.id', '=', 'testing_items.testing_id')
                        ->where('testing_items.item_type', 'finished_product')
                        ->where('testing_items.result', 'fail')
                        ->whereNotNull('testing_items.good_id')
                        ->whereBetween('testings.test_date', [$startDate, $endDate])
                        ->sum('testing_items.quantity');
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
            
            // Return sample data in case of error
            return response()->json([
                'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                'data' => [12, 19, 25, 37, 45, 56]
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
            
            // Thêm tùy chọn tìm kiếm cả sản phẩm ngoài kho
            $includeOutOfStock = isset($filters['include_out_of_stock']) && $filters['include_out_of_stock'] === 'true';
            
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
                    $results = $this->searchProjects($query, $filters);
                    break;
                case 'customers':
                    $results = $this->searchCustomers($query, $filters);
                    break;
                default:
                    // Tìm kiếm tất cả
                    Log::info('Searching all categories with query: ' . $query);
                    
                    $materialResults = $this->searchMaterials($query, $filters, $includeOutOfStock);
                    $productResults = $this->searchProducts($query, $filters, $includeOutOfStock);
                    $goodResults = $this->searchGoods($query, $filters, $includeOutOfStock);
                    $projectResults = $this->searchProjects($query, $filters);
                    $customerResults = $this->searchCustomers($query, $filters);
                    
                    $results = array_merge(
                        $materialResults,
                        $productResults,
                        $goodResults,
                        $projectResults,
                        $customerResults
                    );
                    
                    Log::info('Combined search results', [
                        'materials_count' => count($materialResults),
                        'products_count' => count($productResults),
                        'goods_count' => count($goodResults),
                        'projects_count' => count($projectResults),
                        'customers_count' => count($customerResults),
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
                    $q->where('quantity', '>', 0);
                });
            }
                
            // Áp dụng các bộ lọc
            if (!empty($filters['warehouse_id'])) {
                $materials->whereHas('warehouseMaterials', function($q) use ($filters, $includeOutOfStock) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                    if (!$includeOutOfStock) {
                        $q->where('quantity', '>', 0);
                    }
                });
            }
            
            // Chỉ áp dụng bộ lọc trạng thái nếu cột status tồn tại trong bảng materials
            if (!empty($filters['status']) && $filters['status'] !== 'all' && Schema::hasColumn('materials', 'status')) {
                $materials->where('status', $filters['status']);
            }
            
            $materials = $materials->limit(20)->get();
            
            Log::info('Found ' . $materials->count() . ' materials matching the query');
            
            return $materials->map(function($material) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material');
                    
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
                    $q->where('quantity', '>', 0);
                });
            }
                
            // Áp dụng các bộ lọc
            if (!empty($filters['warehouse_id'])) {
                $products->whereHas('warehouseMaterials', function($q) use ($filters, $includeOutOfStock) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                    if (!$includeOutOfStock) {
                        $q->where('quantity', '>', 0);
                    }
                });
            }
            
            // Chỉ áp dụng bộ lọc trạng thái nếu cột status tồn tại trong bảng products
            if (!empty($filters['status']) && $filters['status'] !== 'all' && Schema::hasColumn('products', 'status')) {
                $products->where('status', $filters['status']);
            }
            
            $products = $products->limit(20)->get();
            
            Log::info('Found ' . $products->count() . ' products matching the query');
            
            return $products->map(function($product) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $product->id)
                    ->where('item_type', 'product');
                    
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
                    $q->where('quantity', '>', 0);
                });
            }
                
            // Áp dụng các bộ lọc
            if (!empty($filters['warehouse_id'])) {
                $goods->whereHas('warehouseMaterials', function($q) use ($filters, $includeOutOfStock) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                    if (!$includeOutOfStock) {
                        $q->where('quantity', '>', 0);
                    }
                });
            }
            
            // Chỉ áp dụng bộ lọc trạng thái nếu cột status tồn tại trong bảng goods
            if (!empty($filters['status']) && $filters['status'] !== 'all' && Schema::hasColumn('goods', 'status')) {
                $goods->where('status', $filters['status']);
            }
            
            $goods = $goods->limit(20)->get();
            
            Log::info('Found ' . $goods->count() . ' goods matching the query');
            
            return $goods->map(function($good) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $good->id)
                    ->where('item_type', 'good');
                    
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
} 