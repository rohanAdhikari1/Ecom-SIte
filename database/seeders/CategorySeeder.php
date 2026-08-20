<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Birthday Cakes',
            'Chocolate Cakes',
            'Fondant Cakes',
            'Cheesecakes',
            'Photo Cakes',
            'Cupcakes',
            'Eggless Cakes',
            'Wedding & Anniversary',
        ];

        foreach ($categories as $index => $name) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
