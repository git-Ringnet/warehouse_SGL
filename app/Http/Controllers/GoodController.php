<?php

namespace App\Http\Controllers;

use App\Models\Good;
use App\Models\GoodImage;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoodController extends Controller
{
    /**
     * Display a listing of the goods.
     */
    public function index()
    {
        // Get all goods
        $goods = Good::all();
        
        // Initialize grand totals
        $grandTotalQuantity = 0;
        $grandInventoryQuantity = 0;
        
        // For demo purposes, add random quantities
        foreach ($goods as $good) {
            // Total quantity across all locations (warehouses, projects, rentals, repairs, etc.)
            $totalQuantity = rand(10, 100);
            $good->total_quantity = $totalQuantity;
            
            // Total quantity only in warehouses
            $inventoryQuantity = rand(5, $totalQuantity);
            $good->inventory_quantity = $inventoryQuantity;
                
            // Add to grand totals
            $grandTotalQuantity += $good->total_quantity;
            $grandInventoryQuantity += $good->inventory_quantity;
        }
        
        return view('goods.index', compact('goods', 'grandTotalQuantity', 'grandInventoryQuantity'));
    }

    /**
     * Show the form for creating a new good.
     */
    public function create()
    {
        // Fetch unique categories from the database
        $categories = Good::select('category')->distinct()->pluck('category')->toArray();
        
        // Add some default categories for demo
        if (empty($categories)) {
            $categories = ['Thực phẩm', 'Đồ uống', 'Quần áo', 'Đồ điện tử', 'Mỹ phẩm'];
        }
        
        // Sort categories alphabetically
        sort($categories);
        
        return view('goods.create', compact('categories'));
    }

    /**
     * Store a newly created good in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:goods,code',
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'inventory_warehouses' => 'nullable',
        ]);

        $goodData = $request->except(['images', 'image']);

        // Create the good
        $good = Good::create($goodData);

        // Handle multiple image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('goods', 'public');
                
                GoodImage::create([
                    'good_id' => $good->id,
                    'image_path' => $imagePath,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('goods.index')
            ->with('success', 'Hàng hóa đã được thêm thành công.');
    }

    /**
     * Display the specified good.
     */
    public function show(Good $good)
    {
        // // Get all warehouses for the dropdown
        // $warehouses = Warehouse::all();
        
        // // Load good images
        // $good->load('images');
        
        // // For demo, generate random quantities
        // $totalQuantity = rand(10, 100);
        // $inventoryQuantity = rand(5, $totalQuantity);
        
        // return view('goods.show', compact('good', 'warehouses', 'totalQuantity', 'inventoryQuantity'));
        return view('goods.show');
    }

    /**
     * Show the form for editing the specified good.
     */
    public function edit(Good $good)
    {
        // Fetch unique categories from the database
        $categories = Good::select('category')->distinct()->pluck('category')->toArray();
        
        // Add some default categories for demo
        if (empty($categories)) {
            $categories = ['Thực phẩm', 'Đồ uống', 'Quần áo', 'Đồ điện tử', 'Mỹ phẩm'];
        }
        
        // Sort categories alphabetically
        sort($categories);
        
        // Load good images
        $good->load('images');
        
        return view('goods.edit', compact('good', 'categories'));
    }

    /**
     * Update the specified good in storage.
     */
    public function update(Request $request, Good $good)
    {
        $request->validate([
            'code' => 'required|unique:goods,code,'.$good->id,
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'inventory_warehouses' => 'nullable'
        ]);

        $goodData = $request->except(['images', 'image', 'deleted_images']);

        // Update the good
        $good->update($goodData);

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImages = explode(',', $request->input('deleted_images'));
            
            foreach ($deletedImages as $imageId) {
                if (!empty($imageId)) {
                    $image = GoodImage::find($imageId);
                    if ($image) {
                        // Delete the file from storage
                        Storage::disk('public')->delete($image->image_path);
                        // Delete the record
                        $image->delete();
                    }
                }
            }
        }

        // Handle multiple image uploads if present
        if ($request->hasFile('images')) {
            $lastOrder = $good->images()->max('sort_order') ?? -1;
            
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('goods', 'public');
                
                GoodImage::create([
                    'good_id' => $good->id,
                    'image_path' => $imagePath,
                    'sort_order' => $lastOrder + $index + 1
                ]);
            }
        }

        return redirect()->route('goods.show', $good->id)
            ->with('success', 'Hàng hóa đã được cập nhật thành công.');
    }

    /**
     * Remove the specified good from storage.
     */
    public function destroy(Good $good)
    {
        // Delete all associated images
        foreach ($good->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        // Delete the good (images will be cascade deleted due to foreign key constraint)
        $good->delete();

        return redirect()->route('goods.index')
            ->with('success', 'Hàng hóa đã được xóa thành công.');
    }

    /**
     * Delete a specific good image
     */
    public function deleteImage($id)
    {
        try {
            $image = GoodImage::findOrFail($id);
            $goodId = $image->good_id;
            
            // Delete the file from storage
            Storage::disk('public')->delete($image->image_path);
            
            // Delete the record
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ảnh thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting good image: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get images for a good
     */
    public function getGoodImages($id)
    {
        try {
            $good = Good::findOrFail($id);
            $images = $good->images()->orderBy('sort_order')->get();
            
            $formattedImages = $images->map(function($image) {
                return [
                    'id' => $image->id,
                    'image_path' => asset('storage/' . $image->image_path),
                    'sort_order' => $image->sort_order,
                ];
            });
            
            return response()->json([
                'success' => true,
                'images' => $formattedImages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting good images: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy hình ảnh: ' . $e->getMessage()
            ], 500);
        }
    }
} 