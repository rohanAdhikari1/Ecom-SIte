<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('brand');
            $table->timestamps();
        });

        Schema::create('product_badge', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_badge_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'product_badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_badge');
        Schema::dropIfExists('product_badges');
    }
};
