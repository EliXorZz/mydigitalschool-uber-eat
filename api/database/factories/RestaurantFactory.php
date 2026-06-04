<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    private static array $images = [
        'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80',
        'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80',
        'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=800&q=80',
        'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&q=80',
        'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
        'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=800&q=80',
        'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&q=80',
        'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?w=800&q=80',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'city' => fake()->city(),
            'image' => fake()->randomElement(self::$images),
            'score' => fake()->randomFloat(1, 3.0, 5.0),
            'price_score' => fake()->numberBetween(1, 4),
            'type_id' => RestaurantType::inRandomOrder()->value('id') ?? 1,
            'owner_id' => User::factory()->restaurant(),
        ];
    }
}
