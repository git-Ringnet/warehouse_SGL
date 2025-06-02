<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
} 