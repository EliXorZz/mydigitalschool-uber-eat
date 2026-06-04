<?php

namespace App\GraphQL\Queries;

use App\Models\Dish;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class DishesQuery extends Query
{
    protected $attributes = [
        'name' => 'dishes',
        'description' => 'List dishes, optionally filtered by restaurant',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Dish'));
    }

    public function args(): array
    {
        return [
            'restaurant_id' => [
                'type' => Type::int(),
                'description' => 'Filter dishes by restaurant ID',
            ],
        ];
    }

    public function resolve($root, array $args): \Illuminate\Database\Eloquent\Collection
    {
        $query = Dish::with('restaurant');

        if (! empty($args['restaurant_id'])) {
            $query->where('restaurant_id', $args['restaurant_id']);
        }

        return $query->get();
    }
}
