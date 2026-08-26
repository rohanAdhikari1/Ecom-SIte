<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'shop_name' => 'ShopKit',
            'shop_tagline' => 'Everything you need, delivered fast.',
            'contact_phone' => '+1-555-0100',
            'contact_whatsapp' => '+1-555-0100',
            'contact_email' => 'hello@example.com',
            'shop_address' => '123 Market Street, Springfield, ST 00000',
            'shop_lat' => '',
            'shop_lng' => '',
            'shop_google_maps_url' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'currency_symbol' => 'Rs.',
            'min_order_amount' => '500',
            'default_delivery_fee' => '50',
            'esewa_enabled' => '1',
            'esewa_note' => 'Pay securely online via eSewa.',

            // Homepage content — everything below is admin-editable so this
            // storefront can be reskinned for a different shop without code changes.
            'announcement_text' => 'Same-day delivery available — order before 4 PM',
            'hero_badge_text' => 'Your Local Favourite',
            'story_heading' => 'Crafted with Care, Delivered with Pride',
            'story_body' => 'Every order is prepared by our team from start to finish — no shortcuts, no middlemen. We use quality materials and finish every order by hand so it arrives exactly as promised.',
            'story_stat1_value' => '500+',
            'story_stat1_label' => 'Orders delivered',
            'story_stat2_value' => '4.9★',
            'story_stat2_label' => 'Average rating',
            'why_choose_us' => json_encode([
                ['icon' => '🎨', 'title' => 'Fully Customisable', 'body' => 'Pick your options and add a personal note on every order.'],
                ['icon' => '🚴', 'title' => 'Same-Day Delivery', 'body' => 'Order before 4 PM and get it delivered fresh, the same day.'],
                ['icon' => '💵', 'title' => 'Flexible Payment', 'body' => 'Pay with Cash on Delivery, or securely online via eSewa.'],
            ]),
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
