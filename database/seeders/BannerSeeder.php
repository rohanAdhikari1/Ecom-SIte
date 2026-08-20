<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Freshly Baked, Delivered with Love',
                'subtitle' => 'Biratnagar\'s favourite cake shop — order online in minutes.',
                'cta_label' => 'Order Now',
                'cta_url' => '/shop',
                'sort_order' => 0,
                'image' => 'chef-piping.jpg',
            ],
            [
                'title' => 'Design Your Dream Cake',
                'subtitle' => 'Pick your weight, flavour, and add a personal message — free on every cake.',
                'cta_label' => 'Customise a Cake',
                'cta_url' => '/shop',
                'sort_order' => 1,
                'image' => 'wedding-rose-pink.jpg',
            ],
            [
                'title' => 'Same-Day Delivery Across Biratnagar',
                'subtitle' => 'Order before 4 PM and get it delivered fresh, the same day.',
                'cta_label' => 'See Delivery Areas',
                'cta_url' => '/shop',
                'sort_order' => 2,
                'image' => 'bakery-display.jpg',
            ],
        ];

        foreach ($banners as $data) {
            $image = $data['image'];
            unset($data['image']);

            $banner = Banner::query()->updateOrCreate(['title' => $data['title']], $data + ['is_active' => true]);

            if ($banner->getMedia('image')->isEmpty()) {
                $banner->addMedia(__DIR__.'/images/'.$image)
                    ->preservingOriginal()
                    ->toMediaCollection('image');
            }
        }
    }
}
