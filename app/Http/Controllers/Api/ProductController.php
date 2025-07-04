<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Material;
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
            // Check if user is authenticated
            if (!Auth::check()) {
                Log::error('Unauthenticated user tried to create product from assembly');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            
            Log::info('Creating product from assembly', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
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
            $newName = $originalProduct->name . " (Modified)";

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
                if (!empty($material['id']) && !empty($material['quantity'])) {
                    // Verify material exists
                    $materialExists = Material::find($material['id']);
                    if ($materialExists) {
                        $materialsData[$material['id']] = [
                            'quantity' => $material['quantity'],
                            'notes' => $material['notes'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            }

            // Attach materials to product
            if (!empty($materialsData)) {
                $newProduct->materials()->attach($materialsData);
            }

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

            // Load materials relationship for the response
            $newProduct->load('materials');

            return response()->json([
                'success' => true,
                'message' => 'Thành phẩm mới đã được tạo thành công.',
                'product' => $newProduct
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating product from assembly: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thành phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
} 