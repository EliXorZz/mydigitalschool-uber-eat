<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DishFactory extends Factory
{
    protected $model = Dish::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'image' => fake()->imageUrl(640, 480, 'food'),
            'price' => fake()->randomFloat(2, 5, 50),
            'restaurant_id' => Restaurant::factory(),
        ];
    }
}
