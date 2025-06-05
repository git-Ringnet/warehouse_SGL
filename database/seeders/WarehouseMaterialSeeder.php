<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Database\Seeder;

class WarehouseMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all warehouses and materials
        $warehouses = Warehouse::all();
        $materials = Material::all();
        
        if ($warehouses->isEmpty() || $materials->isEmpty()) {
            $this->command->info('No warehouses or materials found. Please run the required seeders first.');
            return;
        }
        
        // Clear existing data
        WarehouseMaterial::truncate();
        
        // Create random warehouse materials
        foreach ($warehouses as $warehouse) {
            // Randomly select materials for this warehouse
            $selectedMaterials = $materials->random(min(rand(5, 10), $materials->count()));
            
            foreach ($selectedMaterials as $material) {
                WarehouseMaterial::create([
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $material->id,
                    'quantity' => rand(10, 100),
                ]);
            }
        }
        
        $this->command->info('Warehouse materials have been added successfully.');
    }
} 