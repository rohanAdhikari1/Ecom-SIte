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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('esewa_transaction_uuid')->nullable()->unique()->after('payment_status');
            $table->string('esewa_ref_id')->nullable()->after('esewa_transaction_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['esewa_transaction_uuid', 'esewa_ref_id']);
        });
    }
};
