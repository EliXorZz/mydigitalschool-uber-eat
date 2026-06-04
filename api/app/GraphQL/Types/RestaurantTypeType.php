<?php

namespace App\GraphQL\Types;

use App\Models\RestaurantType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class RestaurantTypeType extends GraphQLType
{
    protected $attributes = [
        'name' => 'RestaurantType',
        'description' => 'A restaurant category/type',
        'model' => RestaurantType::class,
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
                'description' => 'Category name (e.g. Italian, Fast Food)',
            ],
        ];
    }
}
