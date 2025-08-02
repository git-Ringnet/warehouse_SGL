<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Material;
use App\Models\Good;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function checkStock($itemType, $itemId)
    {
        try {
            $item = null;
            $stockInfo = [];
            
            switch ($itemType) {
                case 'product':
                    $item = Product::where('status', 'active')
                        ->where('is_hidden', false)
                        ->find($itemId);
                    break;
                case 'material':
                    $item = Material::where('status', 'active')
                        ->where('is_hidden', false)
                        ->find($itemId);
                    break;
                case 'good':
                    $item = Good::where('status', 'active')
                        ->where('is_hidden', false)
                        ->find($itemId);
                    break;
                default:
                    return response()->json(['error' => 'Loại item không hợp lệ'], 400);
            }
            
            if (!$item) {
                return response()->json(['error' => 'Không tìm thấy item'], 404);
            }
            
            // Lấy thông tin tồn kho từ WarehouseMaterial
            $warehouseMaterials = WarehouseMaterial::with('warehouse')
                ->where('material_id', $itemId)
                ->where('quantity', '>', 0)
                ->whereHas('warehouse', function($q) {
                    $q->where('status', 'active')->where('is_hidden', false);
                })
                ->get();
            
            // Nếu không tìm thấy trong WarehouseMaterial, có thể là Product hoặc Good
            if ($warehouseMaterials->isEmpty() && ($itemType === 'product' || $itemType === 'good')) {
                // Trả về thông tin item nhưng không có tồn kho
                return response()->json([
                    'success' => true,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'total_stock' => 0,
                    'warehouses' => [],
                    'has_stock' => false
                ]);
            }
            
            $totalStock = $warehouseMaterials->sum('quantity');
            
            foreach ($warehouseMaterials as $wm) {
                $stockInfo[] = [
                    'warehouse_name' => $wm->warehouse->name,
                    'quantity' => $wm->quantity
                ];
            }
            
            return response()->json([
                'success' => true,
                'item_name' => $item->name,
                'item_code' => $item->code,
                'total_stock' => $totalStock,
                'warehouses' => $stockInfo,
                'has_stock' => $totalStock > 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi kiểm tra tồn kho: ' . $e->getMessage()], 500);
        }
    }
} 