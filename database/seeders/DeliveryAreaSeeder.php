<?php

namespace Database\Seeders;

use App\Models\DeliveryArea;
use Illuminate\Database\Seeder;

class DeliveryAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'Traffic Chowk / Main Road', 'fee' => 0, 'eta_text' => 'Same day'],
            ['name' => 'Adarsha Nagar', 'fee' => 50, 'eta_text' => 'Same day'],
            ['name' => 'Rani', 'fee' => 50, 'eta_text' => 'Same day'],
            ['name' => 'Sundarharaicha', 'fee' => 100, 'eta_text' => 'Same day'],
            ['name' => 'Pandit Colony', 'fee' => 50, 'eta_text' => 'Same day'],
            ['name' => 'Katahari', 'fee' => 150, 'eta_text' => 'Next day'],
            ['name' => 'Itahari (Outskirts)', 'fee' => 200, 'eta_text' => 'Next day'],
        ];

        foreach ($areas as $index => $area) {
            DeliveryArea::query()->updateOrCreate(
                ['name' => $area['name']],
                $area + ['is_active' => true, 'sort_order' => $index],
            );
        }
    }
}
