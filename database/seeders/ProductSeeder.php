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
        
        // Generate 10 products
        for ($i = 1; $i <= 10; $i++) {            
            // Generate product code
            $code = 'SP' . '-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            // Generate product name
            $brand = $brands[array_rand($brands)];
            $nameTemplate = $productNameTemplates[array_rand($productNameTemplates)] ?? 'thành phẩm %s';
            $name = sprintf($nameTemplate, $brand . ' ' . Str::random(3) . '-' . rand(100, 999));
            
            // Generate description
            $description = $brand;
            
            Product::create([
                'code' => $code,
                'name' => $name,
                'description' => $description,
            ]);
        }
    }
} 