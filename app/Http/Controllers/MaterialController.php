<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    /**
     * Display a listing of the materials.
     */
    public function index()
    {
        $materials = Material::all();
        return view('materials.index', compact('materials'));
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
            'status' => 'required'
        ]);

        Material::create($request->all());

        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được thêm thành công.');
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material)
    {
        return view('materials.show', compact('material'));
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
            'status' => 'required'
        ]);

        $material->update($request->all());

        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được cập nhật thành công.');
    }

    /**
     * Remove the specified material from storage.
     */
    public function destroy(Material $material)
    {
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được xóa thành công.');
    }
} 