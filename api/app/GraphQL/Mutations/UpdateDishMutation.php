<?php

namespace App\GraphQL\Mutations;

use App\Models\Dish;
use App\Services\DishService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class UpdateDishMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateDish',
        'description' => 'Update an existing dish (restaurant owner only)',
    ];

    public function __construct(private DishService $dishService) {}

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
            'name' => [
                'type' => Type::string(),
                'description' => 'Dish name',
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Dish description',
            ],
            'price' => [
                'type' => Type::float(),
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
        $id = $args['id'];
        unset($args['id']);

        return $this->dishService->updateDish($id, array_filter($args, fn ($v) => $v !== null));
    }
}
