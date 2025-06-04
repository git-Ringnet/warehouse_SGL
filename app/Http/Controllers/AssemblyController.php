<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyMaterial;
use App\Models\Material;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssemblyController extends Controller
{
    /**
     * Display a listing of the assemblies.
     */
    public function index()
    {
        $assemblies = Assembly::with('product')->get();
        return view('assemble.index', compact('assemblies'));
    }

    /**
     * Show the form for creating a new assembly.
     */
    public function create()
    {
        // Get all products and materials for the form
        $products = Product::all();
        $materials = Material::all();
        $warehouses = Warehouse::all();
        
        return view('assemble.create', compact('products', 'materials', 'warehouses'));
    }

    /**
     * Store a newly created assembly in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'assembly_code' => 'required|unique:assemblies,code',
            'assembly_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'assigned_to' => 'required',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Create the assembly record
            $assembly = Assembly::create([
                'code' => $request->assembly_code,
                'date' => $request->assembly_date,
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'assigned_to' => $request->assigned_to,
                'status' => 'completed',
                'notes' => $request->assembly_note,
            ]);

            // Create the assembly materials
            foreach ($request->components as $component) {
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'quantity' => $component['quantity'],
                    'serial' => $component['serial'] ?? null,
                    'note' => $component['note'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được tạo thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified assembly.
     */
    public function show(Assembly $assembly)
    {
        $assembly->load(['product', 'materials.material']);
        return view('assemble.show', compact('assembly'));
    }

    /**
     * Show the form for editing the specified assembly.
     */
    public function edit(Assembly $assembly)
    {
        $assembly->load(['product', 'materials.material']);
        $products = Product::all();
        $materials = Material::all();
        $warehouses = Warehouse::all();
        
        return view('assemble.edit', compact('assembly', 'products', 'materials', 'warehouses'));
    }

    /**
     * Update the specified assembly in storage.
     */
    public function update(Request $request, Assembly $assembly)
    {
        $request->validate([
            'assembly_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'assigned_to' => 'required',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update the assembly record
            $assembly->update([
                'date' => $request->assembly_date,
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'assigned_to' => $request->assigned_to,
                'notes' => $request->assembly_note,
            ]);

            // Delete existing materials
            $assembly->materials()->delete();
            
            // Create new assembly materials
            foreach ($request->components as $component) {
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'quantity' => $component['quantity'],
                    'serial' => $component['serial'] ?? null,
                    'note' => $component['note'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified assembly from storage.
     */
    public function destroy(Assembly $assembly)
    {
        DB::beginTransaction();
        try {
            // Delete related materials first
            $assembly->materials()->delete();
            
            // Delete the assembly
            $assembly->delete();
            
            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được xóa thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()]);
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
            
            // Add a debug flag to check if search is working
            $result = [
                'success' => true,
                'count' => $materials->count(),
                'data' => $materials
            ];
            
            return response()->json($materials);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Material search error: ' . $e->getMessage());
            
            // Return error with more details for debugging
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
} 