<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'product_name' => 'Test Product 1',
            'barcode' => 'TEST123456',
            // add other required fields as per your model
        ]);

        Product::create([
            'product_name' => 'Test Product 2',
            'barcode' => 'TEST789012',
        ]);
    }
}
