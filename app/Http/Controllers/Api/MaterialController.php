<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    /**
     * Get available warehouses for a material that have stock and serials
     */
    public function getAvailableWarehouses(Material $material)
    {
        try {
            // Get warehouses that have this material with stock and serials
            $warehouses = Warehouse::whereHas('warehouseMaterials', function ($query) use ($material) {
                $query->where('material_id', $material->id)
                    ->where('quantity', '>', 0)
                    ->whereNotNull('serial_number');
            })->with(['warehouseMaterials' => function ($query) use ($material) {
                $query->where('material_id', $material->id);
            }])->get();

            // Format warehouse data with stock quantity
            $formattedWarehouses = $warehouses->map(function ($warehouse) {
                $quantity = $warehouse->warehouseMaterials->sum('quantity');
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'quantity' => $quantity
                ];
            });

            return response()->json([
                'success' => true,
                'warehouses' => $formattedWarehouses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching warehouses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available serials for a material in a specific warehouse
     */
    public function getAvailableSerials(Material $material, Warehouse $warehouse)
    {
        try {
            // Validate input parameters
            if (!$material || !$warehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material or warehouse not found',
                    'serials' => []
                ], 404);
            }

            // Get available serials for this material in the warehouse
            $serials = WarehouseMaterial::where('material_id', $material->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('quantity', '>', 0)
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '[]')
                ->where('serial_number', '!=', 'null')
                ->where('serial_number', '!=', '')
                ->pluck('serial_number')
                ->filter(function($serial) {
                    return !empty(trim($serial));
                })
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'serials' => $serials,
                'count' => count($serials)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching serials for material ' . $material->id . ' in warehouse ' . $warehouse->id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tải danh sách serial. Vui lòng thử lại.',
                'serials' => []
            ], 500);
        }
    }

    /**
     * Get available serials for multiple materials in a specific warehouse (batch API)
     */
    public function getBatchSerials(Request $request)
    {
        try {
            $materialIds = $request->input('material_ids', []);
            $warehouseId = $request->input('warehouse_id');

            // Validate and clean material IDs
            if (empty($materialIds) || !$warehouseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material IDs and warehouse ID are required',
                    'data' => []
                ], 400);
            }

            // Ensure material IDs are integers and filter out invalid ones
            $materialIds = array_filter(array_map('intval', (array)$materialIds), function($id) {
                return $id > 0;
            });

            if (empty($materialIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid material IDs provided',
                    'data' => []
                ], 400);
            }

            // Validate warehouse exists
            $warehouse = Warehouse::find($warehouseId);
            if (!$warehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warehouse not found',
                    'data' => []
                ], 404);
            }

            // Get serials for all materials in one query
            $serialsData = WarehouseMaterial::whereIn('material_id', $materialIds)
                ->where('warehouse_id', $warehouseId)
                ->where('quantity', '>', 0)
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '[]')
                ->where('serial_number', '!=', 'null')
                ->where('serial_number', '!=', '')
                ->select('material_id', 'serial_number')
                ->get()
                ->groupBy('material_id')
                ->map(function($serials) {
                    return $serials->pluck('serial_number')
                        ->filter(function($serial) {
                            return !empty(trim($serial));
                        })
                        ->values()
                        ->toArray();
                });

            // Ensure all requested materials have an entry (even if empty)
            $result = [];
            foreach ($materialIds as $materialId) {
                $result[$materialId] = $serialsData->get($materialId, []);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'warehouse_id' => $warehouseId,
                'total_materials' => count($materialIds)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching batch serials: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tải danh sách serial hàng loạt. Vui lòng thử lại.',
                'data' => []
            ], 500);
        }
    }
} 