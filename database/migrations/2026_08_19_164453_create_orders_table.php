<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->uuid('tracking_token')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('delivery_address');
            $table->foreignId('delivery_area_id')->nullable()->constrained()->nullOnDelete();
            $table->date('delivery_date');
            $table->string('delivery_time_slot');
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->string('status')->default('pending');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
