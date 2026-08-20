<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            ['name' => 'Birthday Candles', 'price' => 50],
            ['name' => 'Greeting Card', 'price' => 100],
            ['name' => 'Chocolate Bar Topping', 'price' => 150],
            ['name' => 'Fresh Flowers Bunch', 'price' => 350],
            ['name' => 'Photo Print Topper', 'price' => 200],
            ['name' => 'Sparkler Candle', 'price' => 120],
            ['name' => 'Balloon Bundle', 'price' => 250],
        ];

        foreach ($addons as $addon) {
            Addon::query()->updateOrCreate(['name' => $addon['name']], $addon + ['is_active' => true]);
        }
    }
}
