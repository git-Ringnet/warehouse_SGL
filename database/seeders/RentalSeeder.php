<?php

namespace Database\Seeders;

use App\Models\Rental;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(3)->get();
        
        if ($customers->count() > 0) {
            foreach ($customers as $index => $customer) {
                Rental::create([
                    'rental_code' => 'RNT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'rental_name' => 'Hợp đồng cho thuê thiết bị ' . ($index + 1),
                    'customer_id' => $customer->id,
                    'rental_date' => now()->subDays(rand(30, 90)),
                    'due_date' => now()->addMonths(rand(6, 24)),
                    'notes' => 'Hợp đồng cho thuê với khách hàng ' . $customer->name
                ]);
            }
        }
    }
} 