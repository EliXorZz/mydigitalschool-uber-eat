<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DishFactory extends Factory
{
    protected $model = Dish::class;

    private static array $images = [
        'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
        'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=800&q=80',
        'https://images.unsplash.com/photo-1553621042-f6e147245754?w=800&q=80',
        'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
        'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&q=80',
        'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=800&q=80',
        'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&q=80',
        'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800&q=80',
        'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=800&q=80',
        'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=800&q=80',
        'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800&q=80',
        'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->words(fake()->numberBetween(1, 3), true),
            'description' => fake()->sentence(),
            'image' => fake()->randomElement(self::$images),
            'price' => fake()->randomFloat(2, 4, 35),
            'restaurant_id' => Restaurant::factory(),
        ];
    }
}
