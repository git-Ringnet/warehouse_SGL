<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialImage;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaterialsExport;

class MaterialController extends Controller
{
    /**
     * Display a listing of the materials.
     */
    public function index()
    {
        // Get all materials
        $materials = Material::all();
        
        // Initialize grand totals
        $grandTotalQuantity = 0;
        $grandInventoryQuantity = 0;
        
        // For each material, calculate total quantity and warehouse quantity
        foreach ($materials as $material) {
            // Total quantity across all locations (warehouses, projects, rentals, repairs, etc.)
            $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material')
                ->sum('quantity');
            
            // Total quantity only in warehouses based on inventory_warehouses setting
            $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material');
                
            // Check if inventory_warehouses is an array and contains specific warehouses
            if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
            }
            
            $material->inventory_quantity = $warehouseQuery->sum('quantity');
                
            // Add to grand totals
            $grandTotalQuantity += $material->total_quantity;
            $grandInventoryQuantity += $material->inventory_quantity;
        }
        
        return view('materials.index', compact('materials', 'grandTotalQuantity', 'grandInventoryQuantity'));
    }

    /**
     * Show the form for creating a new material.
     */
    public function create()
    {
        // Fetch unique categories from the database
        $categories = Material::select('category')->distinct()->pluck('category')->toArray();
        
        // Sort categories alphabetically
        sort($categories);
        
        return view('materials.create', compact('categories'));
    }

    /**
     * Store a newly created material in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:materials,code',
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'inventory_warehouses' => 'nullable',
        ]);

        $materialData = $request->except(['images', 'image']);

        // Create the material
        $material = Material::create($materialData);

        // Handle multiple image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('materials', 'public');
                
                MaterialImage::create([
                    'material_id' => $material->id,
                    'image_path' => $imagePath,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được thêm thành công.');
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material)
    {
        // Get all warehouses for the dropdown
        $warehouses = Warehouse::all();
        
        // Load material images
        $material->load('images');
        
        // Calculate total quantity across all locations
        $totalQuantity = WarehouseMaterial::where('material_id', $material->id)
            ->where('item_type', 'material')
            ->sum('quantity');
        
        // Calculate total inventory based on configuration
        $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
            ->where('item_type', 'material');
            
        if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
            $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
        }
        
        $inventoryQuantity = $warehouseQuery->sum('quantity');
        
        return view('materials.show', compact('material', 'warehouses', 'totalQuantity', 'inventoryQuantity'));
    }

    /**
     * Show the form for editing the specified material.
     */
    public function edit(Material $material)
    {
        // Fetch unique categories from the database
        $categories = Material::select('category')->distinct()->pluck('category')->toArray();
        
        // Sort categories alphabetically
        sort($categories);
        
        // Load material images
        $material->load('images');
        
        return view('materials.edit', compact('material', 'categories'));
    }

    /**
     * Update the specified material in storage.
     */
    public function update(Request $request, Material $material)
    {
        $request->validate([
            'code' => 'required|unique:materials,code,'.$material->id,
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'inventory_warehouses' => 'nullable'
        ]);

        $materialData = $request->except(['images', 'image', 'deleted_images']);

        // Update the material
        $material->update($materialData);

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImages = explode(',', $request->input('deleted_images'));
            
            foreach ($deletedImages as $imageId) {
                if (!empty($imageId)) {
                    $image = MaterialImage::find($imageId);
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
            $lastOrder = $material->images()->max('sort_order') ?? -1;
            
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('materials', 'public');
                
                MaterialImage::create([
                    'material_id' => $material->id,
                    'image_path' => $imagePath,
                    'sort_order' => $lastOrder + $index + 1
                ]);
            }
        }

        return redirect()->route('materials.show', $material->id)
            ->with('success', 'Vật tư đã được cập nhật thành công.');
    }

    /**
     * Remove the specified material from storage.
     */
    public function destroy(Material $material)
    {
        // Delete all associated images
        foreach ($material->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        // Delete the material (images will be cascade deleted due to foreign key constraint)
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được xóa thành công.');
    }

    /**
     * Delete a specific material image
     */
    public function deleteImage($id)
    {
        try {
            $image = MaterialImage::findOrFail($id);
            $materialId = $image->material_id;
            
            // Delete the file from storage
            Storage::disk('public')->delete($image->image_path);
            
            // Delete the record
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ảnh thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting material image: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search materials API endpoint
     */
    public function searchMaterials(Request $request)
    {
        try {
            $searchTerm = $request->input('term');
            
            if (empty($searchTerm)) {
                return response()->json([]);
            }
            
            // Make search case-insensitive and more comprehensive
            $materials = Material::where(function($query) use ($searchTerm) {
                $query->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(category) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(serial) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->limit(10)
            ->get(['id', 'code', 'name', 'category', 'serial']);
            
            return response()->json($materials);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Material search error: ' . $e->getMessage());
            
            // Return error with more details for debugging
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get inventory quantity for a specific material across all or specific warehouse
     */
    public function getInventoryQuantity(Request $request)
    {
        try {
            $materialId = $request->input('material_id');
            $warehouseId = $request->input('warehouse_id');
            
            if (!$materialId) {
                return response()->json(['error' => 'Material ID is required'], 400);
            }
            
            $material = Material::find($materialId);
            if (!$material) {
                return response()->json(['error' => 'Material not found'], 404);
            }
            
            $query = WarehouseMaterial::where('material_id', $materialId)
                                      ->where('item_type', 'material');
            
            // If warehouse ID is provided and not 'all', filter by warehouse
            if ($warehouseId && $warehouseId !== 'all') {
                $query->where('warehouse_id', $warehouseId);
            } 
            // If no warehouse ID is provided, use the material's configured inventory_warehouses setting
            else if (!$warehouseId && is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $query->whereIn('warehouse_id', $material->inventory_warehouses);
            }
            
            $totalQuantity = $query->sum('quantity');
            
            return response()->json([
                'quantity' => $totalQuantity
            ]);
        } catch (\Exception $e) {
            Log::error('Get inventory quantity error: ' . $e->getMessage());
            
            return response()->json([
                'error' => true, 
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get material images API endpoint
     */
    public function getMaterialImages($id)
    {
        try {
            $material = Material::findOrFail($id);
            $images = $material->images()->orderBy('sort_order')->get();
            
            $formattedImages = $images->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_path),
                    'sort_order' => $image->sort_order
                ];
            });
            
            return response()->json([
                'material_id' => $material->id,
                'material_name' => $material->name,
                'images' => $formattedImages
            ]);
        } catch (\Exception $e) {
            Log::error('Get material images error: ' . $e->getMessage());
            
            return response()->json([
                'error' => true, 
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export materials list to PDF
     */
    public function exportPDF()
    {
        try {
            $materials = Material::all();
            
            // Calculate quantities for each material
            foreach ($materials as $material) {
                // Total quantity across all locations
                $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material')
                    ->sum('quantity');
                
                // Total inventory based on configuration
                $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                    ->where('item_type', 'material');
                    
                if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                    $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
                }
                
                $material->inventory_quantity = $warehouseQuery->sum('quantity');
            }
            
            $pdf = PDF::loadView('materials.pdf', compact('materials'));
            
            return $pdf->download('danh-sach-vat-tu-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Export PDF error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Export materials list to Excel
     */
    public function exportExcel()
    {
        
    }
} 