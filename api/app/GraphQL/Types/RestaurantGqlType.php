<?php

namespace App\GraphQL\Types;

use App\Models\Restaurant;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class RestaurantGqlType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Restaurant',
        'description' => 'A restaurant listed on the platform',
        'model' => Restaurant::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Unique identifier',
            ],
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
                'description' => 'City of the restaurant',
            ],
            'image' => [
                'type' => Type::string(),
                'description' => 'Cover image URL',
            ],
            'score' => [
                'type' => Type::float(),
                'description' => 'Average rating (0-5)',
            ],
            'price_score' => [
                'type' => Type::int(),
                'description' => 'Price range indicator (1-4)',
            ],
            'type' => [
                'type' => GraphQL::type('RestaurantType'),
                'description' => 'Restaurant category',
            ],
            'dishes' => [
                'type' => Type::listOf(GraphQL::type('Dish')),
                'description' => 'List of dishes offered',
            ],
        ];
    }
}
