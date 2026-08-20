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
            'Birthday Cakes' => 'birthday-drip-pink.jpg',
            'Chocolate Cakes' => 'chocolate-cake.jpg',
            'Fondant Cakes' => 'fondant-baby-theme.jpg',
            'Cheesecakes' => 'cheesecake-berry.jpg',
            'Photo Cakes' => 'redvelvet-slice.jpg',
            'Cupcakes' => 'cupcake-strawberry.jpg',
            'Eggless Cakes' => 'vanilla-strawberry-slice.jpg',
            'Wedding & Anniversary' => 'wedding-tier-flowers.jpg',
        ];

        $index = 0;

        foreach ($categories as $name => $image) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index++,
                    'is_active' => true,
                ],
            );

            if ($category->getMedia('image')->isEmpty()) {
                $category->addMedia(__DIR__.'/images/'.$image)
                    ->preservingOriginal()
                    ->toMediaCollection('image');
            }
        }
    }
}
