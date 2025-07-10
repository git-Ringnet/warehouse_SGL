<?php

namespace App\Imports;

use App\Models\Material;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Supplier;

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

                // Parse supplier IDs
                $supplierIds = $this->parseSupplierIds($row['nha_cung_cap'] ?? '');
                
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

                // Associate with suppliers using the pivot table
                if (is_array($supplierIds) && !empty($supplierIds)) {
                    if (in_array('all', $supplierIds)) {
                        // If "all" is specified, get all supplier IDs and attach them
                        $allSupplierIds = Supplier::pluck('id')->toArray();
                        if (!empty($allSupplierIds)) {
                            $material->suppliers()->attach($allSupplierIds);
                        }
                    } else {
                        // Create relationships in pivot table for specific suppliers
                        $material->suppliers()->attach($supplierIds);
                    }
                }
                // Nếu mảng rỗng: không liên kết với nhà cung cấp nào

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

        // If specific warehouse codes are provided (comma separated)
        $warehouseCodes = explode(',', $value);
        $warehouseCodes = array_map('trim', $warehouseCodes);
        $warehouseCodes = array_filter($warehouseCodes);

        return empty($warehouseCodes) ? ['all'] : $warehouseCodes;
    }

    protected function parseSupplierIds($value)
    {
        // Nếu trống (không phải "all"), trả về mảng rỗng
        if (empty($value)) {
            return [];
        }
        
        // Nếu là "all", trả về ['all']
        if (strtolower($value) === 'all') {
            return ['all'];
        }
        
        // Lấy danh sách tất cả nhà cung cấp, sắp xếp theo ID
        $allSuppliers = Supplier::orderBy('id')->get();
        
        // Chuyển STT thành mảng
        $supplierNumbers = explode(',', $value);
        $supplierNumbers = array_map('trim', $supplierNumbers);
        $supplierNumbers = array_filter($supplierNumbers, function($num) {
            return is_numeric($num) && $num > 0;
        });
        
        // Nếu không có STT hợp lệ, trả về mảng rỗng
        if (empty($supplierNumbers)) {
            return [];
        }
        
        // Lấy ID của nhà cung cấp dựa trên STT (STT bắt đầu từ 1)
        $supplierIds = [];
        foreach ($supplierNumbers as $number) {
            $index = (int)$number - 1; // STT bắt đầu từ 1, index bắt đầu từ 0
            if (isset($allSuppliers[$index])) {
                $supplierIds[] = $allSuppliers[$index]->id;
            }
        }
        
        return $supplierIds;
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
