<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        // Create 20 products using the factory
        Product::factory()->count(20)->create();
    }
}

