<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_updates_status_with_valid_signature()
    {
        config(['midtrans.server_key' => 'SB-Mid-server-KEY']);
        
        $order = Order::create([
            'order_number' => 'ORD-TEST',
            'customer_name' => 'Test',
            'phone' => '123',
            'subtotal' => 10000,
            'total' => 10000,
            'payment_status' => 'pending'
        ]);
        
        Payment::create([
            'order_id' => $order->id,
            'order_id_midtrans' => 'ORD-TEST',
            'status' => 'pending',
            'gross_amount' => 10000
        ]);

        $payload = [
            'order_id' => 'ORD-TEST',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'transaction_status' => 'settlement'
        ];
        
        $signature = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . config('midtrans.server_key'));
        
        $payload['signature_key'] = $signature;

        $response = $this->postJson('/api/midtrans/callback', $payload);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'order_status' => 'processing'
        ]);
    }

    public function test_webhook_rejects_invalid_signature()
    {
        config(['midtrans.server_key' => 'SB-Mid-server-KEY']);
        
        $payload = [
            'order_id' => 'ORD-TEST',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid-signature'
        ];

        $response = $this->postJson('/api/midtrans/callback', $payload);
        
        $response->assertStatus(403);
    }
}
