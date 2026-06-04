<?php

namespace App\GraphQL\Mutations;

use App\Models\Restaurant;
use App\Models\User;
use App\Services\RestaurantService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class CreateRestaurantMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createRestaurant',
        'description' => 'Create a new restaurant (admin only)',
    ];

    public function type(): Type
    {
        return GraphQL::type('Restaurant');
    }

    public function args(): array
    {
        return [
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Restaurant name',
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Short description',
            ],
            'city' => [
                'type' => Type::string(),
                'description' => 'City',
            ],
            'image' => [
                'type' => Type::string(),
                'description' => 'Cover image URL',
            ],
            'score' => [
                'type' => Type::float(),
                'description' => 'Rating 0-5',
            ],
            'price_score' => [
                'type' => Type::int(),
                'description' => 'Price range 1-4',
            ],
            'type_id' => [
                'type' => Type::int(),
                'description' => 'Restaurant type ID',
            ],
            'owner_id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Owner user ID',
            ],
        ];
    }

    public function resolve($root, array $args): Restaurant
    {
        $owner = User::findOrFail($args['owner_id']);

        return app(RestaurantService::class)->createRestaurant($owner, $args);
    }
}
