<?php

namespace App\GraphQL\Queries;

use App\Models\Dish;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class DishQuery extends Query
{
    protected $attributes = [
        'name' => 'dish',
        'description' => 'Get a single dish by ID',
    ];

    public function type(): Type
    {
        return GraphQL::type('Dish');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Dish ID',
            ],
        ];
    }

    public function resolve($root, array $args): Dish
    {
        return Dish::with('restaurant')->findOrFail($args['id']);
    }
}
