<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
            AddonSeeder::class,
            ProductSeeder::class,
            DeliveryAreaSeeder::class,
            SettingSeeder::class,
            BannerSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
