<?php

namespace App\GraphQL\Mutations;

use App\Models\Restaurant;
use App\Services\RestaurantService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class UpdateRestaurantMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateRestaurant',
        'description' => 'Update an existing restaurant (owner only)',
    ];

    public function __construct(private RestaurantService $restaurantService) {}

    public function type(): Type
    {
        return GraphQL::type('Restaurant');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Restaurant ID',
            ],
            'name' => [
                'type' => Type::string(),
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
        ];
    }

    public function resolve($root, array $args): Restaurant
    {
        $id = $args['id'];
        unset($args['id']);

        return $this->restaurantService->updateRestaurant($id, array_filter($args, fn ($v) => $v !== null));
    }
}
