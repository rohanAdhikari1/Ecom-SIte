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
                'message' => 'Ordered a birthday cake with a custom message and it arrived exactly on time, beautifully packed. Tasted amazing too!',
                'rating' => 5,
            ],
            [
                'name' => 'Prakash T.',
                'message' => 'Best chocolate cake in Biratnagar. The online ordering was super easy and COD made it even better.',
                'rating' => 5,
            ],
            [
                'name' => 'Anita M.',
                'message' => 'Loved the fondant theme cake for my daughter\'s birthday — exactly like the picture we sent!',
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
