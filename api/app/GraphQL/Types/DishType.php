<?php

namespace App\GraphQL\Types;

use App\Models\Dish;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class DishType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Dish',
        'description' => 'A dish offered by a restaurant',
        'model' => Dish::class,
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
            'restaurant_id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Parent restaurant ID',
            ],
            'restaurant' => [
                'type' => GraphQL::type('Restaurant'),
                'description' => 'Parent restaurant',
            ],
        ];
    }
}
