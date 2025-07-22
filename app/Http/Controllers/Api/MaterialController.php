<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;

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
            // Get available serials for this material in the warehouse
            $serials = WarehouseMaterial::where('material_id', $material->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('quantity', '>', 0)
                ->whereNotNull('serial_number')
                ->pluck('serial_number')
                ->toArray();

            return response()->json([
                'success' => true,
                'serials' => $serials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching serials: ' . $e->getMessage()
            ], 500);
        }
    }
} 