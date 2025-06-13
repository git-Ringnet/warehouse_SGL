<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
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
        $products = Product::all();
        $goods = Good::all();
        
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
                    'item_type' => 'material',
                    'quantity' => rand(10, 100),
                ]);
            }
            
            // Thêm products vào kho
            if ($products->isNotEmpty()) {
                $selectedProducts = $products->random(min(rand(3, 7), $products->count()));
                
                foreach ($selectedProducts as $product) {
                    WarehouseMaterial::create([
                        'warehouse_id' => $warehouse->id,
                        'material_id' => $product->id,
                        'item_type' => 'product',
                        'quantity' => rand(5, 50),
                    ]);
                }
            }
            
            // Thêm goods vào kho
            if ($goods->isNotEmpty()) {
                $selectedGoods = $goods->random(min(rand(2, 5), $goods->count()));
                
                foreach ($selectedGoods as $good) {
                    WarehouseMaterial::create([
                        'warehouse_id' => $warehouse->id,
                        'material_id' => $good->id,
                        'item_type' => 'good',
                        'quantity' => rand(5, 30),
                    ]);
                }
            }
        }
        
        $this->command->info('Warehouse materials have been added successfully.');
    }
} 