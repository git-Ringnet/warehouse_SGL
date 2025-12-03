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
    public function getStatistics(Request $request)
    {
        // Lấy tham số ngày từ request
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $timeRangeType = $request->get('time_range_type', 'month');
        
        // Nếu không có ngày, sử dụng tháng hiện tại
        if (!$startDate || !$endDate) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Chuyển đổi string thành Carbon instance để đảm bảo tương thích
        $startDateCarbon = \Carbon\Carbon::parse($startDate);
        $endDateCarbon = \Carbon\Carbon::parse($endDate);
        
        // 1. Thống kê vật tư
        $materialStats = $this->getMaterialStats($startDateCarbon, $endDateCarbon);
        
        // 2. Thống kê thành phẩm  
        $productStats = $this->getProductStats($startDateCarbon, $endDateCarbon);
        
        // 3. Thống kê hàng hóa
        $goodStats = $this->getGoodStats($startDateCarbon, $endDateCarbon);

        return response()->json([
            'materials' => $materialStats,
            'products' => $productStats, 
            'goods' => $goodStats
        ]);
    }

    /**
     * Get material statistics
     */
    private function getMaterialStats($startDate, $endDate)
    {
        // Tổng nhập kho vật tư - lấy từ lịch sử nhập kho thực tế trong khoảng thời gian được chỉ định
        $totalImport = DB::table('inventory_import_materials')
            ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->where('inventory_import_materials.item_type', 'material')
            ->where('inventory_import_materials.material_id', '>', 0) // Đảm bảo có material_id
            ->where('inventory_imports.status', 'approved') // Chỉ tính phiếu nhập kho đã duyệt
            ->whereDate('inventory_imports.import_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('inventory_imports.import_date', '<=', $endDate->format('Y-m-d'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'inventory_import_materials.material_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('inventory_import_materials.quantity');

        // Tổng xuất kho vật tư - lấy từ lịch sử xuất kho thực tế trong khoảng thời gian được chỉ định
        $totalExport = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'material')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->where('dispatch_items.item_id', '>', 0) // Đảm bảo có item_id
            ->whereDate('dispatches.dispatch_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('dispatches.dispatch_date', '<=', $endDate->format('Y-m-d'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            })
            ->sum('dispatch_items.quantity');

        // Tổng hư hỏng - tính dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
        $totalDamaged = $this->calculateDamagedMaterials($startDate, $endDate);

        return [
            'total_import' => $totalImport ?: 0,
            'total_export' => $totalExport ?: 0,
            'total_damaged' => $totalDamaged ?: 0
        ];
    }

    /**
     * Get product statistics
     */
    private function getProductStats($startDate, $endDate) 
    {
        // Tổng nhập kho thành phẩm - lấy từ assembly_products (thành phẩm được tạo từ phiếu lắp ráp đã hoàn thành)
        // Bao gồm cả thiết bị xuất trực tiếp đến dự án (không qua kho)
        $totalImport = DB::table('assembly_products')
            ->join('assemblies', 'assemblies.id', '=', 'assembly_products.assembly_id')
            ->join('products', 'products.id', '=', 'assembly_products.product_id')
            ->where('assemblies.status', 'completed')
            ->where('products.status', '!=', 'deleted')
            ->whereDate('assemblies.date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('assemblies.date', '<=', $endDate->format('Y-m-d'))
            ->sum('assembly_products.quantity');

        // Tổng xuất kho thành phẩm - lấy từ lịch sử xuất kho thực tế trong khoảng thời gian được chỉ định
        $totalExport = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'product')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->where('dispatch_items.item_id', '>', 0) // Đảm bảo có item_id
            ->whereDate('dispatches.dispatch_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('dispatches.dispatch_date', '<=', $endDate->format('Y-m-d'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.id', 'dispatch_items.item_id')
                    ->where('products.status', '!=', 'deleted');
            })
            ->sum('dispatch_items.quantity');

        // Tổng hư hỏng - tính dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
        $totalDamaged = $this->calculateDamagedProducts($startDate, $endDate);

        return [
            'total_import' => $totalImport ?: 0,
            'total_export' => $totalExport ?: 0,
            'total_damaged' => $totalDamaged ?: 0
        ];
    }

    /**
     * Get good statistics
     */
    private function getGoodStats($startDate, $endDate)
    {
        // Tổng nhập kho hàng hóa - lấy từ lịch sử nhập kho thực tế trong khoảng thời gian được chỉ định
        $totalImport = DB::table('inventory_import_materials')
            ->join('inventory_imports', 'inventory_import_materials.inventory_import_id', '=', 'inventory_imports.id')
            ->where('inventory_import_materials.item_type', 'good')
            ->where('inventory_import_materials.material_id', '>', 0) // Sử dụng material_id để lưu trữ good_id
            ->where('inventory_imports.status', 'approved') // Chỉ tính phiếu nhập kho đã duyệt
            ->whereDate('inventory_imports.import_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('inventory_imports.import_date', '<=', $endDate->format('Y-m-d'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('goods')
                    ->whereColumn('goods.id', 'inventory_import_materials.material_id')
                    ->where('goods.status', '!=', 'deleted');
            })
            ->sum('inventory_import_materials.quantity');

        // Tổng xuất kho hàng hóa - lấy từ lịch sử xuất kho thực tế trong khoảng thời gian được chỉ định
        $totalExport = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', 'good')
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->where('dispatch_items.item_id', '>', 0) // Đảm bảo có item_id
            ->whereDate('dispatches.dispatch_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('dispatches.dispatch_date', '<=', $endDate->format('Y-m-d'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('goods')
                    ->whereColumn('goods.id', 'dispatch_items.item_id')
                    ->where('goods.status', '!=', 'deleted');
            })
            ->sum('dispatch_items.quantity');

        // Tổng hư hỏng - tính dựa trên việc nhập/xuất khỏi các kho không dùng để tính tồn kho
        $totalDamaged = $this->calculateDamagedGoods($startDate, $endDate);

        return [
            'total_import' => $totalImport ?: 0,
            'total_export' => $totalExport ?: 0,
            'total_damaged' => $totalDamaged ?: 0
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
            
            // Tạo mảng nhãn thời gian và dữ liệu theo loại thời gian
            $labels = [];
            $data = [];
            
            if ($startDate && $endDate) {
                // Sử dụng khoảng thời gian được chỉ định
                $data = $this->getChartDataByTimeRange($category, $timeRangeType, $startDate, $endDate);
                $labels = $data['labels'];
                $chartData = $data['data'];
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
            return response()->json([
                'error' => 'Có lỗi xảy ra khi lấy dữ liệu biểu đồ',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy dữ liệu biểu đồ phân loại kho
     */
    public function getInventoryCategoriesChart(Request $request)
    {
        try {
            // Lấy tham số ngày từ request
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $timeRangeType = $request->get('time_range_type', 'month');
            
            // Nếu không có ngày, sử dụng tháng hiện tại
            if (!$startDate || !$endDate) {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            } else {
                $startDate = \Carbon\Carbon::parse($startDate);
                $endDate = \Carbon\Carbon::parse($endDate);
            }
            
            // 1. Thống kê vật tư
            $materialStats = $this->getMaterialStats($startDate, $endDate);
            
            // 2. Thống kê thành phẩm  
            $productStats = $this->getProductStats($startDate, $endDate);
            
            // 3. Thống kê hàng hóa
            $goodStats = $this->getGoodStats($startDate, $endDate);
            
            // Sử dụng số lượng nhập kho trong khoảng thời gian được chọn
            $materialCount = $materialStats['total_import'];
            $productCount = $productStats['total_import'];
            $goodCount = $goodStats['total_import'];
            
            
            
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
                return response()->json([
                    'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                    'data' => [0, 0, 0]
                ]);
            }
            
            return response()->json([
                'labels' => ['Vật tư', 'Thành phẩm', 'Hàng hóa'],
                'data' => [$materialPercent, $productPercent, $goodPercent]
            ]);
        } catch (\Exception $e) {
            
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
            // Lấy tham số lọc (nếu có)
            $itemType = $request->input('item_type'); // 'material', 'product', 'good' hoặc null (tất cả)
            $warehouseIds = $request->input('warehouse_ids'); // mảng id kho hoặc null (tất cả)
            
            
            if ($warehouseIds && is_string($warehouseIds)) {
                $warehouseIds = explode(',', $warehouseIds);
            }
            
            // Lấy danh sách kho (chỉ lấy kho active và không bị ẩn/xóa)
            $warehousesQuery = Warehouse::with(['warehouseMaterials'])
                ->where('status', 'active')
                ->where('is_hidden', false)
                ->whereNull('deleted_at');
            
            if ($warehouseIds) {
                $warehousesQuery->whereIn('id', $warehouseIds);
            }
            
            $warehouses = $warehousesQuery->get();
            
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
                
                // Tính tổng số lượng theo loại vật tư (loại bỏ các item bị ẩn và dữ liệu test)
                if ($itemType) {
                    // Nếu có filter theo loại, chỉ tính loại đó
                    $warehouseTotal = $query->where('item_type', $itemType)
                        ->where('quantity', '<=', 1000000) // Loại bỏ dữ liệu test có số lượng quá lớn
                        ->whereHas($itemType, function($q) {
                            $q->where('is_hidden', false);
                        })
                        ->sum('quantity');
                    $materialCount = ($itemType == 'material') ? $warehouseTotal : 0;
                    $productCount = ($itemType == 'product') ? $warehouseTotal : 0;
                    $goodCount = ($itemType == 'good') ? $warehouseTotal : 0;
                } else {
                    // Nếu không có filter, tính tất cả các loại
                    $materialCount = $warehouse->warehouseMaterials()
                        ->where('item_type', 'material')
                        ->where('quantity', '<=', 1000000) // Loại bỏ dữ liệu test
                        ->whereHas('material', function($q) {
                            $q->where('is_hidden', false);
                        })
                        ->sum('quantity');
                    $productCount = $warehouse->warehouseMaterials()
                        ->where('item_type', 'product')
                        ->where('quantity', '<=', 1000000) // Loại bỏ dữ liệu test
                        ->whereHas('product', function($q) {
                            $q->where('is_hidden', false);
                        })
                        ->sum('quantity');
                    $goodCount = $warehouse->warehouseMaterials()
                        ->where('item_type', 'good')
                        ->where('quantity', '<=', 1000000) // Loại bỏ dữ liệu test
                        ->whereHas('good', function($q) {
                            $q->where('is_hidden', false);
                        })
                        ->sum('quantity');
                    $warehouseTotal = $materialCount + $productCount + $goodCount;
                }
                
                // Chỉ thêm kho có dữ liệu hoặc khi không có filter
                if (!$itemType || $warehouseTotal > 0) {
                    $total += $warehouseTotal;
                    
                    $warehouseData[] = [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'material_count' => $materialCount,
                        'product_count' => $productCount,
                        'good_count' => $goodCount,
                        'total' => $warehouseTotal,
                        'address' => $warehouse->address
                    ];
                }
                
            }
            
            // Sắp xếp kho theo số lượng giảm dần
            usort($warehouseData, function($a, $b) {
                return $b['total'] <=> $a['total'];
            });
            
            // Gộp những kho có phần trăm quá nhỏ để tránh đè lên nhau trên biểu đồ
            $minPercentThreshold = 0.1; // Chỉ hiển thị riêng những kho có phần trăm >= 0.1%
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
                
                
                // Tính theo công thức chuẩn cho mọi tháng
                // Nhập kho: phiếu đã duyệt trong khoảng
                    $importCount = DB::table('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                        ->where('inventory_import_materials.item_type', 'material')
                    ->where('inventory_imports.status', 'approved')
                        ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                        ->sum('inventory_import_materials.quantity');
                    
                // Xuất kho: lấy từ lịch sử xuất kho thực tế trong khoảng thời gian được chỉ định
                $exportCount = DB::table('dispatch_items')
                    ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                    ->where('dispatch_items.item_type', 'material')
                    ->whereIn('dispatches.status', ['approved', 'completed'])
                    ->where('dispatch_items.item_id', '>', 0) // Đảm bảo có item_id
                    ->whereDate('dispatches.dispatch_date', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('dispatches.dispatch_date', '<=', $endDate->format('Y-m-d'))
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('materials')
                            ->whereColumn('materials.id', 'dispatch_items.item_id')
                            ->where('materials.status', 'active')
                            ->where('materials.is_hidden', false);
                    })
                    ->sum('dispatch_items.quantity');

                // Hư hỏng
                    $damagedCount = $this->calculateDamagedMaterials($startDate, $endDate);
                
                $import[] = $importCount;
                $export[] = $exportCount;
                $damaged[] = $damagedCount;
            }
            
            
            
            return [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ];
        } catch (\Exception $e) {
            
            
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
                
                // Tổng nhập kho thành phẩm - lấy từ assembly_products (phiếu lắp ráp đã hoàn thành)
                // Bao gồm cả thiết bị xuất trực tiếp đến dự án (không qua kho)
                $importCount = DB::table('assembly_products')
                    ->join('assemblies', 'assemblies.id', '=', 'assembly_products.assembly_id')
                    ->join('products', 'products.id', '=', 'assembly_products.product_id')
                    ->where('assemblies.status', 'completed')
                    ->where('products.status', '!=', 'deleted')
                    ->whereDate('assemblies.date', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('assemblies.date', '<=', $endDate->format('Y-m-d'))
                    ->sum('assembly_products.quantity');
                
                // Tổng xuất kho thành phẩm - lấy từ dispatch_items (phiếu xuất kho đã duyệt)
                $exportCount = DB::table('dispatch_items')
                    ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                    ->where('dispatch_items.item_type', 'product')
                    ->whereIn('dispatches.status', ['approved', 'completed'])
                    ->where('dispatch_items.item_id', '>', 0)
                    ->whereDate('dispatches.dispatch_date', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('dispatches.dispatch_date', '<=', $endDate->format('Y-m-d'))
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('products')
                            ->whereColumn('products.id', 'dispatch_items.item_id')
                            ->where('products.status', '!=', 'deleted');
                    })
                    ->sum('dispatch_items.quantity');
                
                // Tổng hư hỏng
                $damagedCount = $this->calculateDamagedProducts($startDate, $endDate);
                
                $import[] = $importCount;
                $export[] = $exportCount;
                $damaged[] = $damagedCount;
            }
            
            
            return [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ];
        } catch (\Exception $e) {
            
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
                    
                } else {
                    // Số lượng nhập kho hàng hóa - lấy từ inventory_import_materials (phiếu nhập kho đã duyệt)
                    $importCount = DB::table('inventory_import_materials')
                        ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                        ->where('inventory_import_materials.item_type', 'good')
                        ->where('inventory_imports.status', 'approved') // Chỉ tính phiếu nhập kho đã duyệt
                        ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                        ->sum('inventory_import_materials.quantity');
                    
                    // Số lượng xuất kho
                    $exportCount = DB::table('dispatch_items')
                        ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                        ->where('dispatch_items.item_type', 'good')
                        ->whereIn('dispatches.status', ['approved', 'completed']) // Chỉ tính phiếu xuất kho đã duyệt
                        ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                        ->sum('dispatch_items.quantity');
                    
                    // Số lượng hư hỏng - sử dụng logic mới
                    $damagedCount = $this->calculateDamagedGoods($startDate, $endDate);
                }
                
                $import[] = $importCount;
                $export[] = $exportCount;
                $damaged[] = $damagedCount;
            }
            
            
            return [
                'import' => $import,
                'export' => $export,
                'damaged' => $damaged
            ];
        } catch (\Exception $e) {
            
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
    public function getProjectGrowthChart(Request $request)
    {
        try {
            $startInput = $request->input('start_date');
            $endInput = $request->input('end_date');

            // Chuẩn hóa ngày
            if ($startInput && strpos($startInput, '/') !== false) {
                $start = \Carbon\Carbon::createFromFormat('d/m/Y', $startInput)->startOfDay();
            } else if ($startInput) {
                $start = \Carbon\Carbon::parse($startInput)->startOfDay();
            } else {
                $start = now()->copy()->startOfMonth()->subMonths(5);
            }

            if ($endInput && strpos($endInput, '/') !== false) {
                $end = \Carbon\Carbon::createFromFormat('d/m/Y', $endInput)->endOfDay();
            } else if ($endInput) {
                $end = \Carbon\Carbon::parse($endInput)->endOfDay();
            } else {
                $end = now()->copy()->endOfMonth();
            }

            // Bảo đảm start <= end
            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            // Duyệt theo từng tháng trong khoảng
            $cursor = $start->copy()->startOfMonth();
            $labels = [];
            $data = [];

            // số tích lũy trước tháng đầu tiên
            $cumulativeProjects = \App\Models\Project::where('created_at', '<', $cursor)->count();

            while ($cursor->lte($end)) {
                $labels[] = 'Tháng ' . $cursor->format('n');
                $monthStart = $cursor->copy()->startOfMonth();
                $monthEnd = $cursor->copy()->endOfMonth();

                $newProjects = \App\Models\Project::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $cumulativeProjects += $newProjects;
                $data[] = $cumulativeProjects;
                
                $cursor->addMonth();
            }
            
            return response()->json([
                'labels' => $labels,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            
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
                    // Lấy danh sách thiết bị thuộc các dự án và phiếu cho thuê khớp từ khóa
                    $projectIds = collect($projectResults)->pluck('id')->all();
                    $rentalIds = collect($rentalResults)->pluck('id')->all();
                    $itemsFromProjects = !empty($projectIds) ? $this->searchItemsByProjectIds($projectIds) : [];
                    $itemsFromRentals = !empty($rentalIds) ? $this->searchItemsByRentalIds($rentalIds) : [];
                    $results = array_merge($projectResults, $rentalResults, $itemsFromProjects, $itemsFromRentals);
                    break;
                case 'customers':
                    $results = $this->searchCustomers($query, $filters);
                    break;
                case 'rentals':
                    $results = $this->searchRentals($query, $filters);
                    break;
                default:
                    // Tìm kiếm tất cả
                    
                    $materialResults = $this->searchMaterials($query, $filters, $includeOutOfStock);
                    $productResults = $this->searchProducts($query, $filters, $includeOutOfStock);
                    $goodResults = $this->searchGoods($query, $filters, $includeOutOfStock);
                    $projectResults = $this->searchProjects($query, $filters);
                    $customerResults = $this->searchCustomers($query, $filters);
                    $rentalResults = $this->searchRentals($query, $filters);
                    // Tìm dự án/cho thuê theo hàng hóa/thành phẩm khớp từ khóa
                    $projectsByItems = $this->searchProjectsByItemQuery($query);
                    $rentalsByItems = $this->searchRentalsByItemQuery($query);
                    // Lấy thiết bị thuộc các dự án và phiếu cho thuê tìm thấy
                    $projectIds = collect($projectResults)->pluck('id')->all();
                    $rentalIds = collect($rentalResults)->pluck('id')->all();
                    $itemsFromProjects = !empty($projectIds) ? $this->searchItemsByProjectIds($projectIds) : [];
                    $itemsFromRentals = !empty($rentalIds) ? $this->searchItemsByRentalIds($rentalIds) : [];
                    
                    $results = array_merge(
                        $materialResults,
                        $productResults,
                        $goodResults,
                        $projectResults,
                        $customerResults,
                        $rentalResults,
                        $projectsByItems,
                        $rentalsByItems,
                        $itemsFromProjects,
                        $itemsFromRentals
                    );
                    
                    
                    
                    // Giới hạn kết quả
                    $results = array_slice($results, 0, 50);
            }
            
            $count = count($results);
            
            
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            
            
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
                // Ưu tiên hiển thị vị trí theo dự án/phiếu cho thuê nếu có
                $location = $this->resolveItemLocation('material', $material->id, $warehouseName);
                
                $status = property_exists($material, 'status') ? $material->status : 'active';

                // Danh sách vị trí kho chi tiết (mỗi kho một dòng)
                $locationRows = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material')
                    ->whereHas('warehouse', function($q){ $q->where('status','active'); })
                    ->when(!$includeOutOfStock, function($q){ $q->where('quantity','>',0); })
                    ->with('warehouse')
                    ->get()
                    ->map(function($wm){
                        return [
                            'name' => $wm->warehouse ? $wm->warehouse->name : 'N/A',
                            'quantity' => (int) $wm->quantity,
                        ];
                    })->toArray();
                
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'category' => 'materials',
                    'categoryName' => 'Vật tư',
                    'serial' => $material->code,
                    'date' => $material->created_at->format('d/m/Y'),
                    'location' => $location,
                    'locations' => $locationRows,
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
                    ],
                    'quantity' => WarehouseMaterial::where('material_id', $material->id)
                        ->where('item_type', 'material')
                        ->whereHas('warehouse', function($query){ $query->where('status','active'); })
                        ->sum('quantity'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            
            return [];
        }
    }
    
    /**
     * Tìm kiếm thành phẩm
     */
    private function searchProducts($query, $filters = [], $includeOutOfStock = false)
    {
        try {
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
                // Ưu tiên hiển thị vị trí theo dự án/phiếu cho thuê nếu có
                $location = $this->resolveItemLocation('product', $product->id, $warehouseName);
                
                $status = property_exists($product, 'status') ? $product->status : 'active';

                // Kho được tính tồn của thành phẩm
                $countingWarehouses = [];
                if (is_array($product->inventory_warehouses) && !empty($product->inventory_warehouses)) {
                    if (!in_array('all', $product->inventory_warehouses)) {
                        $countingWarehouses = $product->inventory_warehouses;
                    }
                }

                $locationRows = WarehouseMaterial::where('material_id', $product->id)
                    ->where('item_type', 'product')
                    ->whereHas('warehouse', function($q){ $q->where('status','active'); })
                    // Luôn lấy tất cả vị trí để hiển thị, kể cả số lượng 0
                    ->with('warehouse')
                    ->get()
                    ->map(function($wm){
                        return [
                            'name' => $wm->warehouse ? $wm->warehouse->name : 'N/A',
                            'quantity' => (int) $wm->quantity,
                            'warehouse_id' => $wm->warehouse_id,
                        ];
                    })->toArray();

                // Xác định location ngắn gọn để hiển thị ở bảng tổng hợp
                $shortLocation = 'N/A';
                if (!empty($locationRows)) {
                    // Ưu tiên kho được tính tồn có số lượng > 0
                    $preferred = null;
                    foreach ($locationRows as $r) {
                        $isCounted = empty($countingWarehouses) ? true : in_array($r['warehouse_id'], $countingWarehouses);
                        if ($isCounted && ($r['quantity'] ?? 0) > 0) { $preferred = $r; break; }
                    }
                    $chosen = $preferred ?: $locationRows[0];
                    $shortLocation = $chosen['name'] . ' (' . ($chosen['quantity'] ?? 0) . ')';
                } elseif (!empty($warehouseName)) {
                    $shortLocation = $warehouseName;
                }
                
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'category' => 'finished',
                    'categoryName' => 'Thành phẩm',
                    'serial' => $product->code,
                    'date' => $product->created_at->format('d/m/Y'),
                    'location' => $shortLocation,
                    // Đánh dấu vị trí nào được tính tồn
                    'locations' => array_map(function($row) use ($countingWarehouses){
                        $row['counted'] = empty($countingWarehouses) ? true : in_array($row['warehouse_id'], $countingWarehouses);
                        unset($row['warehouse_id']);
                        return $row;
                    }, $locationRows),
                    'status' => $status,
                    'detailUrl' => route('products.show', $product->id),
                    'additionalInfo' => [
                        'manufactureDate' => $product->created_at->format('d/m/Y'),
                        'quantity' => WarehouseMaterial::where('material_id', $product->id)
                            ->where('item_type', 'product')
                            ->whereHas('warehouse', function($query) {
                                $query->where('status', 'active');
                            })
                            ->when(!empty($countingWarehouses), function($q) use ($countingWarehouses){
                                $q->whereIn('warehouse_id', $countingWarehouses);
                            })
                            ->sum('quantity'),
                        'project' => 'N/A' // Có thể cập nhật nếu có thông tin dự án
                    ],
                    'quantity' => WarehouseMaterial::where('material_id', $product->id)
                        ->where('item_type', 'product')
                        ->whereHas('warehouse', function($query){ $query->where('status','active'); })
                        ->when(!empty($countingWarehouses), function($q) use ($countingWarehouses){
                            $q->whereIn('warehouse_id', $countingWarehouses);
                        })
                        ->sum('quantity'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Tìm kiếm hàng hóa
     */
    private function searchGoods($query, $filters = [], $includeOutOfStock = false)
    {
        try {
            
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
            
            
            return $goods->map(function($good) use ($includeOutOfStock) {
                $warehouseQuery = WarehouseMaterial::where('material_id', $good->id)
                    ->where('item_type', 'good')
                    ->whereHas('warehouse', function($query) {
                        $query->where('status', 'active');
                    });
                    
                if (!$includeOutOfStock) {
                    $warehouseQuery->where('quantity', '>', 0);
                }
                // Áp dụng cấu hình kho tính tồn kho nếu có
                $countingWarehouses = [];
                if (is_array($good->inventory_warehouses) && !empty($good->inventory_warehouses)) {
                    if (!in_array('all', $good->inventory_warehouses)) {
                        $countingWarehouses = $good->inventory_warehouses;
                        $warehouseQuery->whereIn('warehouse_id', $countingWarehouses);
                    }
                }
                
                $warehouseInfo = $warehouseQuery->first();
                    
                $warehouseName = '';
                if ($warehouseInfo && $warehouseInfo->warehouse) {
                    $warehouseName = $warehouseInfo->warehouse->name;
                }
                // Ưu tiên hiển thị vị trí theo dự án/phiếu cho thuê nếu có
                $location = $this->resolveItemLocation('good', $good->id, $warehouseName);
                
                $status = property_exists($good, 'status') ? $good->status : 'active';

                $locationRows = WarehouseMaterial::where('material_id', $good->id)
                    ->where('item_type', 'good')
                    ->whereHas('warehouse', function($q){ $q->where('status','active'); })
                    // Luôn lấy tất cả vị trí hiện có, kể cả số lượng 0 để có thể hiển thị đầy đủ
                    ->with('warehouse')
                    ->get()
                    ->map(function($wm){
                        // Trả về tất cả vị trí để người dùng thấy đủ, sẽ đánh dấu counted ở dưới
                        return [
                            'name' => $wm->warehouse ? $wm->warehouse->name : 'N/A',
                            'quantity' => (int) $wm->quantity,
                            'warehouse_id' => $wm->warehouse_id,
                        ];
                    })->toArray();

                // Đánh dấu vị trí nào được tính tồn kho
                $locationRows = array_map(function($row) use ($countingWarehouses) {
                    $row['counted'] = empty($countingWarehouses) ? true : in_array($row['warehouse_id'], $countingWarehouses);
                    unset($row['warehouse_id']);
                    return $row;
                }, $locationRows);

                // Bổ sung dòng cho các kho được tính tồn nhưng hiện chưa có bản ghi (số lượng 0)
                if (!empty($countingWarehouses)) {
                    $existingIds = array_map(function($r){ return $r['warehouse_id'] ?? null; },
                        WarehouseMaterial::where('material_id', $good->id)
                            ->where('item_type', 'good')
                            ->pluck('warehouse_id')
                            ->toArray());

                    $missingIds = array_diff($countingWarehouses, $existingIds);
                    if (!empty($missingIds)) {
                        $names = Warehouse::whereIn('id', $missingIds)->pluck('name','id')->toArray();
                        foreach ($missingIds as $wid) {
                            $locationRows[] = [
                                'name' => $names[$wid] ?? ('Kho #' . $wid),
                                'quantity' => 0,
                                'counted' => true,
                            ];
                        }
                    }
                }
                
                return [
                    'id' => $good->id,
                    'code' => $good->code,
                    'name' => $good->name,
                    'category' => 'goods',
                    'categoryName' => 'Hàng hóa',
                    'serial' => $good->serial ?: $good->code,
                    'date' => $good->created_at->format('d/m/Y'),
                    'location' => $location,
                    'locations' => $locationRows,
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
                            ->when(!empty($countingWarehouses), function($q) use ($countingWarehouses) {
                                $q->whereIn('warehouse_id', $countingWarehouses);
                            })
                            ->sum('quantity')
                    ],
                    'quantity' => WarehouseMaterial::where('material_id', $good->id)
                        ->where('item_type', 'good')
                        ->whereHas('warehouse', function($query){ $query->where('status','active'); })
                        ->when(!empty($countingWarehouses), function($q) use ($countingWarehouses) {
                            $q->whereIn('warehouse_id', $countingWarehouses);
                        })
                        ->sum('quantity'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Tìm kiếm dự án
     */
    private function searchProjects($query, $filters = [])
    {
        try {
            
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
            
           
            
            return $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'code' => $project->project_code,
                    'name' => $project->project_name,
                    'category' => 'projects',
                    'categoryName' => 'Dự án',
                    'serial' => 'PRJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT),
                    'date' => $project->created_at->format('d/m/Y'),
                    'location' => $project->description ? ($project->project_name . ' - ' . $project->description) : $project->project_name,
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
           
            return [];
        }
    }
    
    /**
     * Tìm kiếm khách hàng
     */
    private function searchCustomers($query, $filters = [])
    {
        try {
            
            
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
           
            return [];
        }
    }
    
    /**
     * Tìm kiếm phiếu cho thuê
     */
    private function searchRentals($query, $filters = [])
    {
        try {
            
            
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
            
           
            
            return $rentals->map(function($rental) {
                return [
                    'id' => $rental->id,
                    'code' => $rental->rental_code,
                    'name' => $rental->rental_name,
                    'category' => 'rentals',
                    'categoryName' => 'Phiếu cho thuê',
                    'serial' => $rental->rental_code,
                    'date' => $rental->rental_date ? date('d/m/Y', strtotime($rental->rental_date)) : 'N/A',
                    'location' => $rental->rental_name, // Hiển thị Tên phiếu cho thuê trong cột Vị trí
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
            
            return [];
        }
    }

    /**
     * Lấy thiết bị thuộc danh sách dự án
     */
    private function searchItemsByProjectIds(array $projectIds): array
    {
        try {
            // Lấy các item đã xuất kho cho dự án
            $items = DB::table('dispatch_items')
                ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                ->leftJoin('products', function ($join) {
                    $join->on('products.id', '=', 'dispatch_items.item_id')
                        ->where('dispatch_items.item_type', 'product');
                })
                ->leftJoin('materials', function ($join) {
                    $join->on('materials.id', '=', 'dispatch_items.item_id')
                        ->where('dispatch_items.item_type', 'material');
                })
                ->leftJoin('goods', function ($join) {
                    $join->on('goods.id', '=', 'dispatch_items.item_id')
                        ->where('dispatch_items.item_type', 'good');
                })
                ->whereIn('dispatches.project_id', $projectIds)
                ->select(
                    'dispatch_items.*',
                    'dispatches.project_id',
                    DB::raw("COALESCE(products.code, materials.code, goods.code) as item_code"),
                    DB::raw("COALESCE(products.name, materials.name, goods.name) as item_name")
                )
                ->limit(50)
                ->get();

            // Lấy tên dự án để hiển thị trong cột vị trí
            $projects = Project::whereIn('id', $projectIds)->pluck('project_name', 'id');

            return $items->map(function ($row) use ($projects) {
                $category = match($row->item_type) {
                    'material' => 'materials',
                    'product' => 'finished',
                    'good' => 'goods',
                    default => 'materials'
                };

                $categoryName = match($row->item_type) {
                    'material' => 'Vật tư',
                    'product' => 'Thành phẩm',
                    'good' => 'Hàng hóa',
                    default => 'Vật tư'
                };

                $detailUrl = '#';
                if ($row->item_type === 'material') {
                    $detailUrl = route('materials.show', $row->item_id);
                } elseif ($row->item_type === 'product') {
                    $detailUrl = route('products.show', $row->item_id);
                } elseif ($row->item_type === 'good') {
                    $detailUrl = route('goods.show', $row->item_id);
                }

                return [
                    'id' => $row->item_id,
                    'code' => $row->item_code,
                    'name' => $row->item_name,
                    'category' => $category,
                    'categoryName' => $categoryName,
                    'serial' => $row->item_code,
                    'date' => optional($row->created_at)->format('d/m/Y'),
                    'location' => ($projects[$row->project_id] ?? 'Dự án') ,
                    'status' => 'Đang tại dự án',
                    'detailUrl' => $detailUrl,
                    'additionalInfo' => [
                        'project' => $projects[$row->project_id] ?? 'N/A',
                        'quantity' => $row->quantity
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            
            return [];
        }
    }

    /**
     * Lấy thiết bị thuộc danh sách phiếu cho thuê
     */
    private function searchItemsByRentalIds(array $rentalIds): array
    {
        try {
            $items = DB::table('rental_items')
                ->join('rentals', 'rentals.id', '=', 'rental_items.rental_id')
                ->leftJoin('products', function ($join) {
                    $join->on('products.id', '=', 'rental_items.item_id')
                        ->where('rental_items.item_type', 'product');
                })
                ->leftJoin('materials', function ($join) {
                    $join->on('materials.id', '=', 'rental_items.item_id')
                        ->where('rental_items.item_type', 'material');
                })
                ->leftJoin('goods', function ($join) {
                    $join->on('goods.id', '=', 'rental_items.item_id')
                        ->where('rental_items.item_type', 'good');
                })
                ->whereIn('rental_items.rental_id', $rentalIds)
                ->select(
                    'rental_items.*',
                    'rentals.rental_name',
                    DB::raw("COALESCE(products.code, materials.code, goods.code) as item_code"),
                    DB::raw("COALESCE(products.name, materials.name, goods.name) as item_name")
                )
                ->limit(50)
                ->get();

            return $items->map(function ($row) {
                $category = match($row->item_type) {
                    'product' => 'finished',
                    'material' => 'materials',
                    'good' => 'goods',
                    default => 'materials'
                };
                $categoryName = match($row->item_type) {
                    'product' => 'Thành phẩm',
                    'material' => 'Vật tư',
                    'good' => 'Hàng hóa',
                    default => 'Vật tư'
                };
                $detailUrl = match($row->item_type) {
                    'product' => route('products.show', $row->item_id),
                    'material' => route('materials.show', $row->item_id),
                    'good' => route('goods.show', $row->item_id),
                    default => '#'
                };

                return [
                    'id' => $row->item_id,
                    'code' => $row->item_code,
                    'name' => $row->item_name,
                    'category' => $category,
                    'categoryName' => $categoryName,
                    'serial' => $row->item_code,
                    'date' => optional($row->created_at)->format('d/m/Y'),
                    'location' => $row->rental_name,
                    'status' => 'Đang cho thuê',
                    'detailUrl' => $detailUrl,
                    'additionalInfo' => [
                        'rental' => $row->rental_name,
                        'quantity' => $row->quantity
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            
            return [];
        }
    }

    /**
     * Tìm dự án có chứa thiết bị khớp theo từ khóa hàng hóa/thành phẩm
     */
    private function searchProjectsByItemQuery(string $query): array
    {
        try {
            // Tìm item id theo từ khóa
            $productIds = Product::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->pluck('id');
            $materialIds = Material::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->pluck('id');

            if ($productIds->isEmpty() && $materialIds->isEmpty()) {
                return [];
            }

            $projectIds = DB::table('dispatch_items')
                ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                ->where(function($q) use ($productIds, $materialIds) {
                    if ($productIds->isNotEmpty()) {
                        $q->orWhere(function($qq) use ($productIds) {
                            $qq->where('dispatch_items.item_type', 'product')
                               ->whereIn('dispatch_items.item_id', $productIds);
                        });
                    }
                    if ($materialIds->isNotEmpty()) {
                        $q->orWhere(function($qq) use ($materialIds) {
                            $qq->where('dispatch_items.item_type', 'material')
                               ->whereIn('dispatch_items.item_id', $materialIds);
                        });
                    }
                })
                ->pluck('dispatches.project_id')
                ->unique()
                ->filter();

            if ($projectIds->isEmpty()) {
                return [];
            }

            $projects = Project::whereIn('id', $projectIds->all())->limit(20)->get();

            return $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'code' => $project->project_code,
                    'name' => $project->project_name,
                    'category' => 'projects',
                    'categoryName' => 'Dự án',
                    'serial' => 'PRJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT),
                    'date' => $project->created_at->format('d/m/Y'),
                    'location' => $project->project_name,
                    'status' => $project->status ?? 'active',
                    'detailUrl' => route('projects.show', $project->id),
                    'additionalInfo' => [
                        'foundBy' => 'item_query'
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Tìm phiếu cho thuê có chứa thiết bị khớp theo từ khóa hàng hóa/thành phẩm
     */
    private function searchRentalsByItemQuery(string $query): array
    {
        try {
            $productIds = Product::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->pluck('id');
            $materialIds = Material::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->pluck('id');

            if ($productIds->isEmpty() && $materialIds->isEmpty()) {
                return [];
            }

            $rentalIds = DB::table('rental_items')
                ->where(function($q) use ($productIds, $materialIds) {
                    if ($productIds->isNotEmpty()) {
                        $q->orWhere(function($qq) use ($productIds) {
                            $qq->where('rental_items.item_type', 'product')
                               ->whereIn('rental_items.item_id', $productIds);
                        });
                    }
                    if ($materialIds->isNotEmpty()) {
                        $q->orWhere(function($qq) use ($materialIds) {
                            $qq->where('rental_items.item_type', 'material')
                               ->whereIn('rental_items.item_id', $materialIds);
                        });
                    }
                })
                ->pluck('rental_items.rental_id')
                ->unique()
                ->filter();

            if ($rentalIds->isEmpty()) {
                return [];
            }

            $rentals = Rental::whereIn('id', $rentalIds->all())->limit(20)->get();

            return $rentals->map(function($rental) {
                return [
                    'id' => $rental->id,
                    'code' => $rental->rental_code,
                    'name' => $rental->rental_name,
                    'category' => 'rentals',
                    'categoryName' => 'Phiếu cho thuê',
                    'serial' => $rental->rental_code,
                    'date' => $rental->rental_date ? date('d/m/Y', strtotime($rental->rental_date)) : 'N/A',
                    'location' => $rental->rental_name,
                    'status' => $rental->isOverdue() ? 'Quá hạn' : 'Đang hoạt động',
                    'detailUrl' => route('rentals.show', $rental->id),
                    'additionalInfo' => [
                        'foundBy' => 'item_query'
                    ]
                ];
            })->toArray();
        } catch (\Exception $e) {
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
                $current = $start->copy()->startOfMonth();
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
                $current = $start->copy()->startOfYear();
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
        
        if ($itemType === 'material' || $itemType === 'good') {
            // Vật tư và hàng hóa: lấy từ phiếu nhập kho đã duyệt
            $query = DB::table('inventory_import_materials')
                ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                ->where('inventory_import_materials.item_type', $itemType)
                ->where('inventory_imports.status', 'approved')
                ->whereDate('inventory_imports.import_date', $date);
                
            if ($itemType === 'material') {
                $query->where('inventory_import_materials.material_id', '>', 0)
                      ->whereExists(function ($subQuery) {
                          $subQuery->select(DB::raw(1))
                              ->from('materials')
                              ->whereColumn('materials.id', 'inventory_import_materials.material_id')
                              ->where('materials.status', 'active')
                              ->where('materials.is_hidden', false);
                      });
            } elseif ($itemType === 'good') {
                $query->where('inventory_import_materials.material_id', '>', 0)
                      ->whereExists(function ($subQuery) {
                          $subQuery->select(DB::raw(1))
                              ->from('goods')
                              ->whereColumn('goods.id', 'inventory_import_materials.material_id')
                              ->where('goods.status', '!=', 'deleted');
                      });
            }
        } else {
            // Thành phẩm: lấy từ assembly_products (phiếu lắp ráp đã hoàn thành)
            // Bao gồm cả thiết bị xuất trực tiếp đến dự án (không qua kho)
            $query = DB::table('assembly_products')
                ->join('assemblies', 'assemblies.id', '=', 'assembly_products.assembly_id')
                ->join('products', 'products.id', '=', 'assembly_products.product_id')
                ->where('assemblies.status', 'completed')
                ->where('products.status', '!=', 'deleted')
                ->whereDate('assemblies.date', $date);
        }
        
        if ($itemType === 'material' || $itemType === 'good') {
            return $query->sum('inventory_import_materials.quantity');
        } else {
            return $query->sum('assembly_products.quantity');
        }
    }

    /**
     * Lấy dữ liệu xuất kho cho một ngày cụ thể
     */
    private function getExportsForDate($category, $date)
    {
        $itemType = $this->getItemTypeByCategory($category);
        
        // Tất cả loại item đều sử dụng dispatch_items cho xuất kho
        $query = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', $itemType)
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->where('dispatch_items.item_id', '>', 0)
            ->whereDate('dispatches.dispatch_date', $date);
            
        // Thêm điều kiện kiểm tra item tồn tại
        if ($itemType === 'material') {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            });
        } elseif ($itemType === 'product') {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.id', 'dispatch_items.item_id')
                    ->where('products.status', '!=', 'deleted');
            });
        } elseif ($itemType === 'good') {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('goods')
                    ->whereColumn('goods.id', 'dispatch_items.item_id')
                    ->where('goods.status', '!=', 'deleted');
            });
        }
        
        return $query->sum('dispatch_items.quantity');
    }

    /**
     * Xác định vị trí ưu tiên của item theo dự án/phiếu cho thuê nếu đang gắn
     */
    private function resolveItemLocation(string $itemType, int $itemId, string $fallbackWarehouseName = ''): string
    {
        try {
            // Kiểm tra trong phiếu cho thuê trước
            $rentalRow = DB::table('rental_items')
                ->join('rentals', 'rentals.id', '=', 'rental_items.rental_id')
                ->where('rental_items.item_type', $itemType)
                ->where('rental_items.item_id', $itemId)
                ->orderByDesc('rental_items.id')
                ->select('rentals.rental_name')
                ->first();
            if ($rentalRow && !empty($rentalRow->rental_name)) {
                return $rentalRow->rental_name;
            }

            // Kiểm tra trong xuất kho dự án
            $dispatchRow = DB::table('dispatch_items')
                ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                ->join('projects', 'projects.id', '=', 'dispatches.project_id')
                ->where('dispatch_items.item_type', $itemType)
                ->where('dispatch_items.item_id', $itemId)
                ->orderByDesc('dispatch_items.id')
                ->select('projects.project_name')
                ->first();
            if ($dispatchRow && !empty($dispatchRow->project_name)) {
                return $dispatchRow->project_name;
            }

            return $fallbackWarehouseName ?: 'N/A';
        } catch (\Exception $e) {
            return $fallbackWarehouseName ?: 'N/A';
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
        
        // Vật tư và Hàng hóa: lấy theo phiếu nhập đã duyệt trong khoảng thời gian
        if ($itemType === 'material' || $itemType === 'good') {
            $query = DB::table('inventory_import_materials')
                ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                ->where('inventory_import_materials.item_type', $itemType)
                ->where('inventory_imports.status', 'approved')
                ->whereBetween('inventory_imports.import_date', [$startDate, $endDate]);

            if ($itemType === 'material') {
                $query->join('materials', 'materials.id', '=', 'inventory_import_materials.material_id')
                      ->where('materials.status', '!=', 'deleted')
                      ->where('materials.is_hidden', false);
            } else {
                $query->join('goods', 'goods.id', '=', 'inventory_import_materials.material_id')
                      ->where('goods.status', '!=', 'deleted')
                      ->where('goods.is_hidden', false);
            }

            return $query->sum('inventory_import_materials.quantity');
        }

        // Thành phẩm: lấy từ assembly_products (phiếu lắp ráp đã hoàn thành)
        // Bao gồm cả thiết bị xuất trực tiếp đến dự án (không qua kho)
        $query = DB::table('assembly_products')
            ->join('assemblies', 'assemblies.id', '=', 'assembly_products.assembly_id')
            ->join('products', 'products.id', '=', 'assembly_products.product_id')
            ->where('assemblies.status', 'completed')
            ->where('products.status', '!=', 'deleted')
            ->whereBetween('assemblies.date', [$startDate, $endDate]);
        
        return $query->sum('assembly_products.quantity');
    }

    /**
     * Lấy dữ liệu xuất kho cho một khoảng thời gian
     */
    private function getExportsForPeriod($category, $startDate, $endDate)
    {
        $itemType = $this->getItemTypeByCategory($category);
        
        // Tất cả loại item đều sử dụng dispatch_items cho xuất kho
        $query = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->where('dispatch_items.item_type', $itemType)
            ->whereIn('dispatches.status', ['approved', 'completed'])
            ->where('dispatch_items.item_id', '>', 0)
            ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate]);
            
        // Thêm điều kiện kiểm tra item tồn tại
        if ($itemType === 'material') {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('materials')
                    ->whereColumn('materials.id', 'dispatch_items.item_id')
                    ->where('materials.status', 'active')
                    ->where('materials.is_hidden', false);
            });
        } elseif ($itemType === 'product') {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.id', 'dispatch_items.item_id')
                    ->where('products.status', '!=', 'deleted');
            });
        } elseif ($itemType === 'good') {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('goods')
                    ->whereColumn('goods.id', 'dispatch_items.item_id')
                    ->where('goods.status', '!=', 'deleted');
            });
        }
        
        return $query->sum('dispatch_items.quantity');
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
            
            return $totalDamaged;
        } catch (\Exception $e) {
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
            
            return $totalDamaged;
        } catch (\Exception $e) {
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
            
            return $totalDamaged;
        } catch (\Exception $e) {
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
            // - Với material/good: dựa vào inventory_imports
            // - Với product: dựa vào warehouse_materials (thành phẩm vào kho khi hoàn tất lắp ráp/kiểm thử)
            if ($itemType === 'product') {
                $importToNonInventoryWarehouses = DB::table('warehouse_materials')
                    ->join('warehouses', 'warehouses.id', '=', 'warehouse_materials.warehouse_id')
                    ->where('warehouse_materials.item_type', 'product')
                    ->where('warehouse_materials.material_id', $itemId)
                    ->where('warehouses.status', 'active')
                    ->whereNotIn('warehouses.id', $inventoryWarehouses)
                    ->whereBetween('warehouse_materials.created_at', [$startDate, $endDate])
                    ->sum('warehouse_materials.quantity');
            } else {
            $importToNonInventoryWarehouses = DB::table('inventory_import_materials')
                ->join('inventory_imports', 'inventory_imports.id', '=', 'inventory_import_materials.inventory_import_id')
                    // Quan trọng: lấy kho theo từng dòng chi tiết, không phải trên header phiếu
                    ->join('warehouses', 'warehouses.id', '=', 'inventory_import_materials.warehouse_id')
                ->where('inventory_import_materials.item_type', $itemType)
                ->where('inventory_import_materials.material_id', $itemId)
                    ->where('inventory_imports.status', 'approved')
                ->where('warehouses.status', 'active')
                ->whereNotIn('warehouses.id', $inventoryWarehouses)
                ->whereBetween('inventory_imports.import_date', [$startDate, $endDate])
                ->sum('inventory_import_materials.quantity');
            }
            
            // Tính tổng xuất khỏi các kho không dùng để tính tồn kho
            // Sử dụng bảng dispatch_items để theo dõi xuất kho
            $exportFromNonInventoryWarehouses = DB::table('dispatch_items')
                ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                ->join('warehouses', 'warehouses.id', '=', 'dispatch_items.warehouse_id')
                ->where('dispatch_items.item_type', $itemType)
                ->where('dispatch_items.item_id', $itemId)
                ->whereIn('dispatches.status', ['approved', 'completed'])
                ->where('warehouses.status', 'active')
                ->whereNotIn('warehouses.id', $inventoryWarehouses)
                ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                ->sum('dispatch_items.quantity');
            
            // Số lượng hư hỏng = Nhập vào kho không tính tồn kho - Xuất khỏi kho không tính tồn kho
            $damaged = $importToNonInventoryWarehouses - $exportFromNonInventoryWarehouses;
            
            // Đảm bảo không âm
            return max(0, $damaged);
            
        } catch (\Exception $e) {
            return 0;
        }
    }
} 