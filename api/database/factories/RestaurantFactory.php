<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'city' => fake()->city(),
            'image' => fake()->imageUrl(640, 480, 'food'),
            'score' => fake()->randomFloat(1, 0, 5),
            'price_score' => fake()->numberBetween(1, 4),
            'type_id' => 1,
            'owner_id' => User::factory(),
        ];
    }
}
