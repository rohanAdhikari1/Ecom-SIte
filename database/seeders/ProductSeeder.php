<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $addonIds = Addon::query()->pluck('id');

        $products = [
            [
                'category' => 'Birthday Cakes',
                'name' => 'Rainbow Sprinkle Cake',
                'description' => 'A festive vanilla sponge layered with buttercream and topped with rainbow sprinkles — a BRT Live Cake birthday favourite.',
                'is_featured' => true,
                'weights' => [['1 lb', 750], ['2 lb', 1400], ['3 lb', 2000]],
                'flavors' => [['Vanilla', 0], ['Chocolate', 0], ['Butterscotch', 100]],
                'images' => ['birthday-drip-pink.jpg'],
            ],
            [
                'category' => 'Chocolate Cakes',
                'name' => 'Belgian Chocolate Truffle',
                'description' => 'Rich Belgian chocolate sponge soaked in chocolate ganache with a truffle finish.',
                'is_featured' => true,
                'weights' => [['1 lb', 850], ['2 lb', 1600], ['3 lb', 2300]],
                'flavors' => [['Dark Chocolate', 0], ['Milk Chocolate', 0]],
                'images' => ['chocolate-cake.jpg'],
            ],
            [
                'category' => 'Fondant Cakes',
                'name' => 'Custom Fondant Theme Cake',
                'description' => 'A fully customisable fondant cake — tell us your theme and we bring it to life.',
                'is_featured' => true,
                'weights' => [['2 lb', 2200], ['3 lb', 3200], ['5 lb', 5000]],
                'flavors' => [['Vanilla', 0], ['Chocolate', 0], ['Red Velvet', 150]],
                'images' => ['fondant-baby-theme.jpg'],
            ],
            [
                'category' => 'Cheesecakes',
                'name' => 'New York Baked Cheesecake',
                'description' => 'Classic creamy baked cheesecake with a buttery biscuit base.',
                'is_featured' => false,
                'weights' => [['1 lb', 950], ['2 lb', 1800]],
                'flavors' => [['Plain', 0], ['Blueberry', 150], ['Strawberry', 150]],
                'images' => ['cheesecake-berry.jpg'],
            ],
            [
                'category' => 'Photo Cakes',
                'name' => 'Edible Photo Print Cake',
                'description' => 'Vanilla or chocolate sponge topped with an edible print of your favourite photo.',
                'is_featured' => false,
                'weights' => [['1 lb', 900], ['2 lb', 1650]],
                'flavors' => [['Vanilla', 0], ['Chocolate', 0]],
                'images' => ['birthday-happy.jpg'],
            ],
            [
                'category' => 'Cupcakes',
                'name' => 'Assorted Cupcakes (Box of 6)',
                'description' => 'A colourful assortment of six mini cupcakes, perfect for small celebrations.',
                'is_featured' => false,
                'weights' => [['Box of 6', 600], ['Box of 12', 1100]],
                'flavors' => [['Mixed', 0]],
                'images' => ['cupcake-strawberry.jpg'],
            ],
            [
                'category' => 'Eggless Cakes',
                'name' => 'Eggless Vanilla Delight',
                'description' => '100% eggless vanilla sponge cake with fresh cream frosting.',
                'is_featured' => true,
                'weights' => [['1 lb', 750], ['2 lb', 1400]],
                'flavors' => [['Vanilla', 0], ['Chocolate', 0]],
                'eggless' => true,
                'images' => ['vanilla-strawberry-slice.jpg'],
            ],
            [
                'category' => 'Wedding & Anniversary',
                'name' => 'Three-Tier Celebration Cake',
                'description' => 'An elegant three-tier cake designed for weddings and anniversaries, fully customisable.',
                'is_featured' => true,
                'weights' => [['5 lb', 6500], ['8 lb', 9800], ['10 lb', 12000]],
                'flavors' => [['Vanilla', 0], ['Chocolate', 0], ['Red Velvet', 300]],
                'images' => ['wedding-tier-flowers.jpg', 'wedding-rose-pink.jpg'],
            ],
        ];

        foreach ($products as $data) {
            $category = Category::query()->where('name', $data['category'])->firstOrFail();

            $product = Product::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'base_price' => $data['weights'][0][1],
                    'is_eggless' => $data['eggless'] ?? false,
                    'allows_message' => true,
                    'message_max_length' => 40,
                    'is_featured' => $data['is_featured'],
                    'is_active' => true,
                ],
            );

            $product->weightOptions()->delete();
            foreach ($data['weights'] as $index => [$label, $price]) {
                $product->weightOptions()->create([
                    'label' => $label,
                    'price' => $price,
                    'sort_order' => $index,
                    'is_default' => $index === 0,
                ]);
            }

            $product->flavorOptions()->delete();
            foreach ($data['flavors'] as $index => [$name, $modifier]) {
                $product->flavorOptions()->create([
                    'name' => $name,
                    'price_modifier' => $modifier,
                    'sort_order' => $index,
                ]);
            }

            $product->addons()->sync($addonIds);

            if ($product->getMedia('gallery')->isEmpty()) {
                foreach ($data['images'] as $image) {
                    $product->addMedia(__DIR__.'/images/'.$image)
                        ->preservingOriginal()
                        ->toMediaCollection('gallery');
                }
            }
        }
    }
}
