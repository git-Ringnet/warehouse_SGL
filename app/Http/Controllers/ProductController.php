<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:products,code',
            'name' => 'required',
            'type' => 'required',
        ]);

        Product::create([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Thành phẩm đã được thêm thành công.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'code' => 'required|unique:products,code,'.$product->id,
            'name' => 'required',
            'type' => 'required',
        ]);

        $product->update([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Thành phẩm đã được cập nhật thành công.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Thành phẩm đã được xóa thành công.');
    }

    /**
     * Get components that are typically used for a product
     * This returns any materials that have been used in previous assemblies for this product
     */
    public function getComponents($id)
    {
        $product = Product::findOrFail($id);
        
        // Query for materials associated with this product from assembly_materials table
        // through the assemblies table
        $components = DB::table('materials')
            ->join('assembly_materials', 'materials.id', '=', 'assembly_materials.material_id')
            ->join('assemblies', 'assembly_materials.assembly_id', '=', 'assemblies.id')
            ->where('assemblies.product_id', $product->id)
            ->select('materials.*', 'assembly_materials.quantity as pivot_quantity')
            ->distinct()
            ->get();
            
        // If no components found, return empty array
        if ($components->isEmpty()) {
            return response()->json([]);
        }
        
        // Add stock quantity to each component
        foreach ($components as $component) {
            // Get total stock quantity across all warehouses
            $stockQuantity = DB::table('warehouse_materials')
                ->where('material_id', $component->id)
                ->where('item_type', 'material')
                ->sum('quantity');
                
            $component->stock_quantity = $stockQuantity;
            
            // Add pivot data for consistency with relationships
            $component->pivot = (object)[
                'quantity' => $component->pivot_quantity
            ];
            unset($component->pivot_quantity);
        }
        
        return response()->json($components);
    }
} 