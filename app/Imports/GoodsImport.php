<?php

namespace App\Imports;

use App\Models\Good;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GoodsImport implements ToCollection, WithHeadingRow
{
    protected $importResults = [
        'total_rows' => 0,
        'success_count' => 0,
        'error_count' => 0,
        'duplicate_count' => 0,
        'errors' => [],
        'duplicates' => [],
        'created_goods' => []
    ];
    
    public function collection(Collection $rows)
    {
        // Reset results at the beginning to ensure clean state
        $this->resetResults();
        
        $this->importResults['total_rows'] = $rows->count();
        
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because Excel starts from 1 and has header
            
            // Skip empty rows
            if (empty($row['ma_hang_hoa']) && empty($row['ten_hang_hoa'])) {
                continue;
            }
            
            try {
                // Validate required fields
                if (empty($row['ma_hang_hoa']) || empty($row['ten_hang_hoa']) || empty($row['loai_hang_hoa']) || empty($row['don_vi'])) {
                    throw new \Exception('Thiếu thông tin bắt buộc: Mã hàng hóa, Tên hàng hóa, Loại hàng hóa, Đơn vị là bắt buộc.');
                }
                
                // Check for duplicate code
                $existingGood = Good::where('code', $row['ma_hang_hoa'])->first();
                if ($existingGood) {
                    $this->importResults['duplicate_count']++;
                    $this->importResults['duplicates'][] = [
                        'row' => $rowNumber,
                        'code' => $row['ma_hang_hoa'],
                        'name' => $row['ten_hang_hoa'] ?? 'N/A',
                        'message' => 'Mã hàng hóa đã tồn tại. Loại hàng hóa là bắt buộc. Đơn vị là bắt buộc.'
                    ];
                    continue;
                }
                
                // Parse inventory warehouses
                $inventoryWarehouses = $this->parseInventoryWarehouses($row['kho_tinh_ton_kho'] ?? 'all');
                
                // Create new good
                $good = Good::create([
                    'code' => $row['ma_hang_hoa'],
                    'name' => $row['ten_hang_hoa'],
                    'category' => $row['loai_hang_hoa'],
                    'unit' => $row['don_vi'],
                    'description' => $row['ghi_chu'] ?? null,
                    'inventory_warehouses' => $inventoryWarehouses,
                    'status' => 'active',
                    'is_hidden' => false
                ]);
                
                $this->importResults['success_count']++;
                $this->importResults['created_goods'][] = [
                    'row' => $rowNumber,
                    'code' => $good->code,
                    'name' => $good->name,
                    'id' => $good->id
                ];
                
            } catch (\Exception $e) {
                $this->importResults['error_count']++;
                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'code' => $row['ma_hang_hoa'] ?? 'N/A',
                    'name' => $row['ten_hang_hoa'] ?? 'N/A',
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
            'created_goods' => []
        ];
    }
} 