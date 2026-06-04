<?php

namespace App\GraphQL\Queries;

use App\Models\Restaurant;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class RestaurantsQuery extends Query
{
    protected $attributes = [
        'name' => 'restaurants',
        'description' => 'List all restaurants with optional search filter',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Restaurant'));
    }

    public function args(): array
    {
        return [
            'search' => [
                'type' => Type::string(),
                'description' => 'Filter by restaurant name',
            ],
        ];
    }

    public function resolve($root, array $args): \Illuminate\Database\Eloquent\Collection
    {
        $query = Restaurant::with(['type', 'dishes']);

        if (! empty($args['search'])) {
            $query->where('name', 'LIKE', '%' . $args['search'] . '%');
        }

        return $query->get();
    }
}
