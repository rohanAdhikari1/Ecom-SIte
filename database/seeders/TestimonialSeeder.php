<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Sabina R.',
                'message' => 'Ordered with a custom note and it arrived exactly on time, beautifully packed. Quality was amazing too!',
                'rating' => 5,
            ],
            [
                'name' => 'Prakash T.',
                'message' => 'Best shop I\'ve ordered from. The online ordering was super easy and COD made it even better.',
                'rating' => 5,
            ],
            [
                'name' => 'Anita M.',
                'message' => 'Loved the fully customised order for my daughter\'s birthday — exactly like the picture we sent!',
                'rating' => 4,
            ],
        ];

        foreach ($testimonials as $index => $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['name' => $testimonial['name']],
                $testimonial + ['is_active' => true, 'sort_order' => $index],
            );
        }
    }
}
