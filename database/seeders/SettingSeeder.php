<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'shop_name' => 'BRT Live Cake',
            'shop_tagline' => 'Freshly baked happiness, delivered across Biratnagar.',
            'contact_phone' => '+977-9801234567',
            'contact_whatsapp' => '+977-9801234567',
            'contact_email' => 'hello@brtlivecake.com',
            'shop_address' => 'BRT Cake, Biratnagar, Koshi Province, Nepal',
            'shop_lat' => '26.4733808',
            'shop_lng' => '87.2730507',
            'shop_google_maps_url' => 'https://maps.app.goo.gl/XKoEtGptbSXLYm3R7',
            'facebook_url' => 'https://www.facebook.com/brtlivecake/',
            'instagram_url' => '',
            'currency_symbol' => 'Rs.',
            'min_order_amount' => '500',
            'default_delivery_fee' => '50',
            'esewa_enabled' => '1',
            'esewa_note' => 'Pay securely online via eSewa.',

            // Homepage content — everything below is admin-editable so this
            // storefront can be reskinned for a different shop without code changes.
            'announcement_text' => 'Same-day delivery across Biratnagar — order before 4 PM',
            'hero_badge_text' => "Biratnagar's Own Cake Parlor",
            'story_heading' => 'Crafted with Love, Baked with Care',
            'story_body' => 'Every BRT Live Cake order is handmade by our in-house bakers in Biratnagar — from the sponge to the final flourish of frosting. We use real ingredients, no shortcuts, and finish every cake by hand so it looks (and tastes) just as good as it looks in the photo.',
            'story_stat1_value' => '500+',
            'story_stat1_label' => 'Cakes delivered',
            'story_stat2_value' => '4.9★',
            'story_stat2_label' => 'Average rating',
            'why_choose_us' => json_encode([
                ['icon' => '🎨', 'title' => 'Fully Customisable', 'body' => 'Pick your weight, flavour, and add a free message on every cake.'],
                ['icon' => '🚴', 'title' => 'Same-Day Delivery', 'body' => 'Order before 4 PM and get it delivered fresh across Biratnagar.'],
                ['icon' => '💵', 'title' => 'Flexible Payment', 'body' => 'Pay with Cash on Delivery, or securely online via eSewa.'],
            ]),
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
