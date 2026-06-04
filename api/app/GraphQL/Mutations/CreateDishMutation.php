<?php

namespace App\GraphQL\Mutations;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Services\DishService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class CreateDishMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createDish',
        'description' => 'Create a new dish for a restaurant (restaurant owner only)',
    ];

    public function type(): Type
    {
        return GraphQL::type('Dish');
    }

    public function args(): array
    {
        return [
            'restaurant_id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Parent restaurant ID',
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Dish name',
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Dish description',
            ],
            'price' => [
                'type' => Type::nonNull(Type::float()),
                'description' => 'Price in euros',
            ],
            'image' => [
                'type' => Type::string(),
                'description' => 'Image URL',
            ],
        ];
    }

    public function resolve($root, array $args): Dish
    {
        $restaurant = Restaurant::findOrFail($args['restaurant_id']);
        unset($args['restaurant_id']);

        return app(DishService::class)->createDish($restaurant, $args);
    }
}
