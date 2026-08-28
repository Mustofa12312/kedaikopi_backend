<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kopi = \App\Models\Category::where('name', 'Kopi')->first();
        if ($kopi) {
            \App\Models\Product::create([
                'category_id' => $kopi->id,
                'name' => 'Es Kopi Susu',
                'slug' => 'es-kopi-susu',
                'description' => 'Kopi susu creamy dengan perpaduan espresso dan susu.',
                'price' => 15000,
                'stock' => 50,
            ]);
        }

        $jus = \App\Models\Category::where('name', 'Jus')->first();
        if ($jus) {
            \App\Models\Product::create([
                'category_id' => $jus->id,
                'name' => 'Jus Alpukat',
                'slug' => 'jus-alpukat',
                'description' => 'Jus alpukat segar dan manis.',
                'price' => 15000,
                'stock' => 30,
            ]);
        }
    }
}
