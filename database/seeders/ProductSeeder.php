<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Product prefixes for different types
        $productPrefixes = [
            'Mới' => ['PC', 'LT', 'PR', 'SW', 'SR'],
            'Bảo hành' => ['PCR', 'LTR', 'PRR', 'SWR', 'SRR'],
        ];
        
        // Product name templates
        $productNameTemplates = [
            'PC' => 'Máy tính để bàn %s',
            'LT' => 'Laptop %s',
            'PR' => 'Máy in %s',
            'SW' => 'Phần mềm %s',
            'SR' => 'Server %s',
            'PCR' => 'Máy tính để bàn %s (Bảo hành)',
            'LTR' => 'Laptop %s (Bảo hành)',
            'PRR' => 'Máy in %s (Bảo hành)',
            'SWR' => 'Phần mềm %s (Bảo hành)',
            'SRR' => 'Server %s (Bảo hành)',
        ];
        
        // Brand names
        $brands = ['Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Apple', 'Samsung', 'Sony', 'Microsoft', 'Canon', 'Epson'];
        
        // Description templates
        $descriptionTemplates = [
            'Mới' => [
                'thành phẩm %s mới 100%%, bảo hành chính hãng 12 tháng.',
                'thành phẩm %s chính hãng, mới 100%%, đầy đủ phụ kiện.',
                'Thiết bị %s mới nguyên seal, bảo hành 24 tháng toàn cầu.',
                'thành phẩm %s hàng chính hãng, mới 100%%, ship COD toàn quốc.',
                'Thiết bị %s cao cấp, mới 100%%, bảo hành 36 tháng.'
            ],
            'Bảo hành' => [
                'thành phẩm %s đã qua sửa chữa, bảo hành 3 tháng.',
                'Thiết bị %s đã qua bảo trì, bảo hành 6 tháng.',
                'thành phẩm %s bảo hành lại, hoạt động tốt như mới.',
                'Thiết bị %s đã qua sửa chữa, kiểm tra kỹ, bảo hành 30 ngày.',
                'thành phẩm %s bảo hành lại, kiểm tra chất lượng đạt tiêu chuẩn.'
            ]
        ];
        
        // Generate 200 products
        for ($i = 1; $i <= 200; $i++) {
            // Randomly select product type
            $type = (rand(1, 10) <= 7) ? 'Mới' : 'Bảo hành'; // 70% new, 30% warranty
            
            // Generate product code
            $prefix = $productPrefixes[$type][array_rand($productPrefixes[$type])];
            $code = $prefix . '-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            // Generate product name
            $brand = $brands[array_rand($brands)];
            $productBaseType = substr($prefix, 0, 2); // Get the first 2 characters of prefix
            $nameTemplate = $productNameTemplates[$prefix] ?? 'thành phẩm %s';
            $name = sprintf($nameTemplate, $brand . ' ' . Str::random(3) . '-' . rand(100, 999));
            
            // Generate description
            $descTemplate = $descriptionTemplates[$type][array_rand($descriptionTemplates[$type])];
            $description = sprintf($descTemplate, $brand);
            
            Product::create([
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'description' => $description,
            ]);
        }
    }
} 