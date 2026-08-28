<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creates_with_db_price_not_user_price()
    {
        $category = Category::create(['name' => 'Kopi']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'slug' => 'kopi-susu',
            'price' => 15000,
            'stock' => 10,
            'is_available' => true
        ]);

        // Mock midtrans config to avoid real API hit during test
        config(['midtrans.server_key' => 'dummy']);
        
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Test',
            'phone' => '08123',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 1000 // Fake price from frontend
                ]
            ]
        ]);

        $response->assertStatus(201);
        
        // 2 * 15000 = 30000 + 2000 service fee = 32000
        $this->assertDatabaseHas('orders', [
            'total' => 32000,
            'subtotal' => 30000
        ]);
        
        $this->assertDatabaseHas('order_items', [
            'price' => 15000 // Real price is saved
        ]);
    }
}
