<?php

namespace App\Imports;

use App\Models\Material;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MaterialsImport implements ToCollection, WithHeadingRow
{
    protected $importResults = [
        'total_rows' => 0,
        'success_count' => 0,
        'error_count' => 0,
        'duplicate_count' => 0,
        'errors' => [],
        'duplicates' => [],
        'created_materials' => []
    ];
    
    public function collection(Collection $rows)
    {
        // Reset results at the beginning to ensure clean state
        $this->resetResults();
        
        $this->importResults['total_rows'] = $rows->count();
        
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because Excel starts from 1 and has header
            
            try {
                // Validate required fields
                $errors = [];
                if (empty($row['ma_vat_tu'])) {
                    $errors[] = 'Mã vật tư là bắt buộc';
                }
                if (empty($row['ten_vat_tu'])) {
                    $errors[] = 'Tên vật tư là bắt buộc';
                }
                if (empty($row['loai_vat_tu'])) {
                    $errors[] = 'Loại vật tư là bắt buộc';
                }
                if (empty($row['don_vi'])) {
                    $errors[] = 'Đơn vị là bắt buộc';
                }
                
                if (!empty($errors)) {
                    $this->importResults['error_count']++;
                    $this->importResults['errors'][] = [
                        'row' => $rowNumber,
                        'code' => $row['ma_vat_tu'] ?? 'N/A',
                        'name' => $row['ten_vat_tu'] ?? 'N/A',
                        'message' => implode(', ', $errors)
                    ];
                    continue;
                }
                
                // Check for duplicate code - only check materials that are not deleted
                $existingMaterial = Material::where('code', $row['ma_vat_tu'])
                    ->where('status', '!=', 'deleted')
                    ->first();
                
                if ($existingMaterial) {
                    $this->importResults['duplicate_count']++;
                    $this->importResults['duplicates'][] = [
                        'row' => $rowNumber,
                        'code' => $row['ma_vat_tu'],
                        'name' => $row['ten_vat_tu'],
                        'message' => 'Mã vật tư đã tồn tại'
                    ];
                    continue;
                }
                
                // Parse inventory warehouses
                $inventoryWarehouses = $this->parseInventoryWarehouses($row['kho_tinh_ton_kho'] ?? 'all');
                
                // Create new material
                $material = Material::create([
                    'code' => $row['ma_vat_tu'],
                    'name' => $row['ten_vat_tu'],
                    'category' => $row['loai_vat_tu'],
                    'unit' => $row['don_vi'],
                    'notes' => $row['ghi_chu'] ?? null,
                    'inventory_warehouses' => $inventoryWarehouses,
                    'status' => 'active',
                    'is_hidden' => false
                ]);
                
                $this->importResults['success_count']++;
                $this->importResults['created_materials'][] = [
                    'row' => $rowNumber,
                    'code' => $material->code,
                    'name' => $material->name,
                    'id' => $material->id
                ];
                
            } catch (\Exception $e) {
                $this->importResults['error_count']++;
                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'code' => $row['ma_vat_tu'] ?? 'N/A',
                    'name' => $row['ten_vat_tu'] ?? 'N/A',
                    'message' => $e->getMessage()
                ];
            }
        }
    }
    
    protected function parseInventoryWarehouses($value)
    {
        if (empty($value) || strtolower($value) === 'all') {
            return ['all'];
        }
        
        // If specific warehouse IDs are provided (comma separated)
        $warehouseIds = explode(',', $value);
        $warehouseIds = array_map('trim', $warehouseIds);
        $warehouseIds = array_filter($warehouseIds);
        
        return empty($warehouseIds) ? ['all'] : $warehouseIds;
    }
    
    public function getImportResults()
    {
        return $this->importResults;
    }
    
    protected function resetResults()
    {
        $this->importResults = [
            'total_rows' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'duplicate_count' => 0,
            'errors' => [],
            'duplicates' => [],
            'created_materials' => []
        ];
    }
}
