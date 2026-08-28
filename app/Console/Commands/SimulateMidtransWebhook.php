<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class SimulateMidtransWebhook extends Command
{
    protected $signature = 'midtrans:simulate {order_number} {--status=settlement}';
    protected $description = 'Simulate Midtrans webhook notification for an order';

    public function handle()
    {
        $orderNumber = $this->argument('order_number');
        $status = $this->option('status');
        
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            $this->error("Order {$orderNumber} not found.");
            return;
        }

        $serverKey = config('midtrans.server_key');
        $statusCode = '200';
        $grossAmount = number_format($order->total, 2, '.', '');
        
        // Midtrans hash generation: order_id + status_code + gross_amount + server_key
        $signature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $status,
            'payment_type' => 'bank_transfer',
            'signature_key' => $signature,
            'settlement_time' => now()->toDateTimeString()
        ];

        // Send request to our own API
        $response = Http::post(url('/api/midtrans/callback'), $payload);

        if ($response->successful()) {
            $this->info("Successfully simulated {$status} webhook for {$orderNumber}");
        } else {
            $this->error("Failed to simulate webhook. Status: {$response->status()}");
            $this->line($response->body());
        }
    }
}
