<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialSupplierMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting material-supplier data migration...');
        
        DB::beginTransaction();
        
        try {
            $materials = Material::all();
            $migratedCount = 0;
            $errorCount = 0;
            
            foreach ($materials as $material) {
                $supplierIds = [];
                
                // Collect supplier IDs from both fields
                
                // From supplier_id (single supplier - legacy)
                if (!empty($material->supplier_id)) {
                    $supplierIds[] = $material->supplier_id;
                }
                
                // From supplier_ids (JSON array)
                if (!empty($material->supplier_ids) && is_array($material->supplier_ids)) {
                    $supplierIds = array_merge($supplierIds, $material->supplier_ids);
                }
                
                // Remove duplicates and filter out invalid IDs
                $supplierIds = array_unique($supplierIds);
                $supplierIds = array_filter($supplierIds, function($id) {
                    return !empty($id) && is_numeric($id);
                });
                
                if (!empty($supplierIds)) {
                    // Verify that supplier IDs exist
                    $validSupplierIds = Supplier::whereIn('id', $supplierIds)->pluck('id')->toArray();
                    
                    if (!empty($validSupplierIds)) {
                        // Create relationships in pivot table
                        $material->suppliers()->sync($validSupplierIds);
                        $migratedCount++;
                        
                        $this->command->info("Migrated material {$material->code} with " . count($validSupplierIds) . " suppliers");
                    } else {
                        $this->command->warn("Material {$material->code} has invalid supplier IDs: " . implode(',', $supplierIds));
                        $errorCount++;
                    }
                } else {
                    $this->command->info("Material {$material->code} has no suppliers to migrate");
                }
            }
            
            DB::commit();
            
            $this->command->info("Migration completed successfully!");
            $this->command->info("Materials migrated: {$migratedCount}");
            $this->command->info("Materials with errors: {$errorCount}");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error("Migration failed: " . $e->getMessage());
            Log::error('Material supplier migration failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
