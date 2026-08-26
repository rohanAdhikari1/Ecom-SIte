<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('allows_message', 'has_custom_field');
            $table->renameColumn('message_max_length', 'custom_field_max_length');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('custom_field_label')->default('Special Instructions')->after('has_custom_field');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('custom_field_label');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('has_custom_field', 'allows_message');
            $table->renameColumn('custom_field_max_length', 'message_max_length');
        });
    }
};
