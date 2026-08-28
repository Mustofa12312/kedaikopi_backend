<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('phone');
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('service_fee', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('payment_status')->default('pending'); // pending, paid, failed, expired, cancelled
            $table->string('order_status')->default('pending'); // pending, processing, ready, completed, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
