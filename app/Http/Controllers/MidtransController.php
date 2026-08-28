<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            $order = Order::where('order_number', $request->order_id)->first();
            if ($order) {
                $paymentStatus = 'pending';
                if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                    $paymentStatus = 'paid';
                } else if ($request->transaction_status == 'cancel' || $request->transaction_status == 'deny' || $request->transaction_status == 'expire') {
                    $paymentStatus = 'failed';
                }
                
                $order->update([
                    'payment_status' => $paymentStatus,
                    'order_status' => $paymentStatus === 'paid' ? 'processing' : $order->order_status
                ]);
                
                // Update or create payment record
                Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'status' => $paymentStatus,
                        'payment_type' => $request->payment_type,
                        'raw_response' => json_encode($request->all()),
                        'settlement_time' => $request->settlement_time ?? null,
                    ]
                );

                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false], 403);
    }
}
