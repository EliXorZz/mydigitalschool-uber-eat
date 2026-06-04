<?php

namespace App\GraphQL\Queries;

use App\Models\Restaurant;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class RestaurantQuery extends Query
{
    protected $attributes = [
        'name' => 'restaurant',
        'description' => 'Get a single restaurant by ID',
    ];

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
        ];
    }

    public function resolve($root, array $args): Restaurant
    {
        return Restaurant::with(['type', 'dishes'])->findOrFail($args['id']);
    }
}
