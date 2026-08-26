<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert the old hardcoded weight/flavor axes into generic option
     * types + values. Weight becomes a required "Size" type; each weight's
     * previously-absolute price becomes a price_modifier relative to the
     * product's base_price so displayed totals don't shift. Flavor becomes
     * an optional "Flavor" type; its price_modifier carries over unchanged.
     */
    public function up(): void
    {
        if (! Schema::hasTable('product_weight_options') || ! Schema::hasTable('products')) {
            return;
        }

        $products = DB::table('products')->select('id', 'base_price')->get()->keyBy('id');

        $weightsByProduct = DB::table('product_weight_options')->orderBy('sort_order')->get()->groupBy('product_id');
        $flavorsByProduct = DB::table('product_flavor_options')->orderBy('sort_order')->get()->groupBy('product_id');

        foreach ($weightsByProduct as $productId => $weights) {
            $basePrice = (int) ($products[$productId]->base_price ?? 0);

            $typeId = DB::table('product_option_types')->insertGetId([
                'product_id' => $productId,
                'name' => 'Size',
                'sort_order' => 0,
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($weights as $weight) {
                DB::table('product_option_values')->insert([
                    'product_option_type_id' => $typeId,
                    'label' => $weight->label,
                    'price_modifier' => (int) $weight->price - $basePrice,
                    'sort_order' => $weight->sort_order,
                    'is_default' => (bool) $weight->is_default,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($flavorsByProduct as $productId => $flavors) {
            $typeId = DB::table('product_option_types')->insertGetId([
                'product_id' => $productId,
                'name' => 'Flavor',
                'sort_order' => 1,
                'is_required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($flavors as $index => $flavor) {
                DB::table('product_option_values')->insert([
                    'product_option_type_id' => $typeId,
                    'label' => $flavor->name,
                    'price_modifier' => (int) $flavor->price_modifier,
                    'sort_order' => $flavor->sort_order,
                    'is_default' => $index === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::dropIfExists('product_weight_options');
        Schema::dropIfExists('product_flavor_options');
    }

    public function down(): void
    {
        // Irreversible by design — the old tables are gone and this is a
        // one-way data transform. Restore from a backup if needed.
    }
};
