<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Create a new product from assembly.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFromAssembly(Request $request)
    {
        try {
            $request->validate([
                'original_product_id' => 'required|exists:products,id',
                'components' => 'required|array',
                'components.*.id' => 'required|exists:materials,id',
                'components.*.quantity' => 'required|numeric|min:0.01',
                'components.*.notes' => 'nullable|string',
            ]);

            // Get original product
            $originalProduct = Product::findOrFail($request->original_product_id);

            // Generate new product code and name
            $timestamp = now()->format('YmdHis');
            $newCode = $originalProduct->code . "-M" . $timestamp;
            $newName = $originalProduct->name . " Modified";

            DB::beginTransaction();

            // Create new product
            $newProduct = Product::create([
                'code' => $newCode,
                'name' => $newName,
                'description' => $originalProduct->description,
                'inventory_warehouses' => $originalProduct->inventory_warehouses,
                'status' => 'active',
                'is_hidden' => false
            ]);

            // Attach materials with new quantities
            $materialsData = [];
            foreach ($request->components as $material) {
                $materialsData[$material['id']] = [
                    'quantity' => $material['quantity'],
                    'notes' => $material['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $newProduct->materials()->attach($materialsData);

            // Log activity
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'products',
                    'Tạo mới thành phẩm từ assembly: ' . $newProduct->name,
                    null,
                    $newProduct->toArray()
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thành phẩm mới đã được tạo thành công.',
                'product' => $newProduct->load('materials')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating product from assembly: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thành phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
} 