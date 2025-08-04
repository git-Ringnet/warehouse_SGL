<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    protected $importResults = [
        'total_rows' => 0,
        'success_count' => 0,
        'error_count' => 0,
        'duplicate_count' => 0,
        'errors' => [],
        'duplicates' => [],
        'created_products' => []
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
                if (empty($row['ma_thanh_pham'])) {
                    $errors[] = 'Mã thành phẩm là bắt buộc';
                }
                if (empty($row['ten_thanh_pham'])) {
                    $errors[] = 'Tên thành phẩm là bắt buộc';
                }
                
                if (!empty($errors)) {
                    $this->importResults['error_count']++;
                    $this->importResults['errors'][] = [
                        'row' => $rowNumber,
                        'code' => $row['ma_thanh_pham'] ?? 'N/A',
                        'name' => $row['ten_thanh_pham'] ?? 'N/A',
                        'message' => implode(', ', $errors)
                    ];
                    continue;
                }
                
                // Note: Products can have duplicate codes, so we don't check for duplicates
                
                // Parse inventory warehouses
                $inventoryWarehouses = $this->parseInventoryWarehouses($row['kho_dung_de_tinh_ton_kho'] ?? 'all');
                
                // Create new product
                $product = Product::create([
                    'code' => $row['ma_thanh_pham'],
                    'name' => $row['ten_thanh_pham'],
                    'description' => $row['mo_ta'] ?? null,
                    'inventory_warehouses' => $inventoryWarehouses,
                    'status' => 'active',
                    'is_hidden' => false
                ]);
                
                $this->importResults['success_count']++;
                $this->importResults['created_products'][] = [
                    'row' => $rowNumber,
                    'code' => $product->code,
                    'name' => $product->name,
                    'id' => $product->id
                ];
                
            } catch (\Exception $e) {
                $this->importResults['error_count']++;
                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'code' => $row['ma_thanh_pham'] ?? 'N/A',
                    'name' => $row['ten_thanh_pham'] ?? 'N/A',
                    'message' => $e->getMessage()
                ];
            }
        }
    }
    
    public function getImportResults()
    {
        return $this->importResults;
    }
    
    protected function parseInventoryWarehouses($value)
    {
        if (empty($value) || strtolower($value) === 'all') {
            return ['all'];
        }

        // If specific warehouse codes are provided (comma separated)
        $warehouseCodes = explode(',', $value);
        $warehouseCodes = array_map('trim', $warehouseCodes);
        $warehouseCodes = array_filter($warehouseCodes);

        if (empty($warehouseCodes)) {
            return ['all'];
        }

        // Lấy danh sách kho theo mã và chưa bị xóa
        $warehouses = Warehouse::whereIn('code', $warehouseCodes)
            ->where('status', '!=', 'deleted')
            ->get();

        // Kiểm tra các mã kho không hợp lệ
        $existingCodes = $warehouses->pluck('code')->toArray();
        $invalidCodes = array_diff($warehouseCodes, $existingCodes);
        if (!empty($invalidCodes)) {
            throw new \Exception('Mã kho không tồn tại hoặc đã bị xóa: ' . implode(', ', $invalidCodes));
        }

        // Trả về mảng ID của các kho
        return $warehouses->pluck('id')->toArray();
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
            'created_products' => []
        ];
    }
} 