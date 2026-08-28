<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'transaction_id', 'order_id_midtrans', 
        'payment_type', 'gross_amount', 'transaction_status', 
        'fraud_status', 'settlement_time', 'raw_response'
    ];

    protected $casts = [
        'raw_response' => 'array',
        'settlement_time' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
