<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot each order item's old fixed weight/flavor columns into the
     * generic order_item_options table, then drop those columns. This is a
     * historical-record transform — order_items.unit_price/line_total stay
     * as-is and remain authoritative for totals; these rows are purely the
     * "what was selected" breakdown shown on receipts/admin views.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('order_items', 'weight_label')) {
            return;
        }

        $items = DB::table('order_items')
            ->select('id', 'weight_label', 'weight_price', 'flavor_label', 'flavor_price_modifier')
            ->get();

        foreach ($items as $item) {
            DB::table('order_item_options')->insert([
                'order_item_id' => $item->id,
                'option_type_name' => 'Size',
                'option_value_label' => $item->weight_label,
                'price_modifier' => (int) $item->weight_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (filled($item->flavor_label)) {
                DB::table('order_item_options')->insert([
                    'order_item_id' => $item->id,
                    'option_type_name' => 'Flavor',
                    'option_value_label' => $item->flavor_label,
                    'price_modifier' => (int) $item->flavor_price_modifier,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('order_items', function ($table) {
            $table->dropColumn(['weight_label', 'weight_price', 'flavor_label', 'flavor_price_modifier']);
        });
    }

    public function down(): void
    {
        // Irreversible by design — see the paired create_options migration.
    }
};
