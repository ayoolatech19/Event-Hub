<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'organizer']),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'venue' => fake()->city(),
            'date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'capacity' => 50,
            'tickets_sold' => 0,
            'price' => 500000,
            'status' => 'published',
        ];
    }
}