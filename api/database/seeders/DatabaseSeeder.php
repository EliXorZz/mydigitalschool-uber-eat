<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantType;
use App\Models\User;
use App\States\OrderConfirmed;
use App\States\OrderDelivered;
use App\States\OrderPending;
use App\States\OrderPreparing;
use App\States\OrderReady;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedRestaurantTypes();
        $this->seedAdmin();

        $restaurants = $this->seedRestaurantsAndOwners();
        $users = $this->seedRegularUsers();

        $this->seedDishes($restaurants);
        $this->seedOrders($users, $restaurants);
    }

    private function seedRestaurantTypes(): void
    {
        $types = [
            'Fast Food',
            'Italien',
            'Chinois',
            'Japonais',
            'Indien',
            'Mexicain',
            'Français',
            'Thaï',
            'Vietnamien',
            'Libanais',
            'Burger',
            'Pizzeria',
            'Sushi',
            'Kebab',
            'Boulangerie',
        ];

        foreach ($types as $type) {
            RestaurantType::firstOrCreate(['name' => $type]);
        }
    }

    private function seedAdmin(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@uber-eat.test'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'role' => Role::ADMIN,
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedRestaurantsAndOwners(): array
    {
        $data = [
            [
                'owner_name' => 'Marie Dubois',
                'owner_email' => 'marie@lebistrot.test',
                'restaurant_name' => 'Le Bistrot Parisien',
                'type' => 'Français',
                'description' => 'Cuisine française traditionnelle dans une ambiance chaleureuse.',
            ],
            [
                'owner_name' => 'Giovanni Rossi',
                'owner_email' => 'giovanni@lapasta.test',
                'restaurant_name' => 'La Pasta Bella',
                'type' => 'Italien',
                'description' => 'Authentiques pâtes fraîches faites maison tous les jours.',
            ],
            [
                'owner_name' => 'Kenji Tanaka',
                'owner_email' => 'kenji@sushimaster.test',
                'restaurant_name' => 'Sushi Master',
                'type' => 'Japonais',
                'description' => 'Sushi et sashimi de qualité premium avec des produits frais.',
            ],
            [
                'owner_name' => 'Ahmed Benali',
                'owner_email' => 'ahmed@kebabroyal.test',
                'restaurant_name' => 'Kebab Royal',
                'type' => 'Kebab',
                'description' => 'Les meilleurs kebabs et tacos de la ville, viande 100% halal.',
            ],
            [
                'owner_name' => 'Sophie Martin',
                'owner_email' => 'sophie@burgerfactory.test',
                'restaurant_name' => 'Burger Factory',
                'type' => 'Burger',
                'description' => 'Burgers gourmets avec du bœuf frais et des frites maison.',
            ],
        ];

        $restaurants = [];
        foreach ($data as $item) {
            $owner = User::firstOrCreate(
                ['email' => $item['owner_email']],
                [
                    'name' => $item['owner_name'],
                    'password' => Hash::make('password'),
                    'role' => Role::RESTAURANT,
                    'email_verified_at' => now(),
                ]
            );

            $type = RestaurantType::where('name', $item['type'])->first();

            $restaurant = Restaurant::firstOrCreate(
                ['name' => $item['restaurant_name']],
                [
                    'description' => $item['description'],
                    'score' => fake()->randomFloat(1, 3.5, 5),
                    'price_score' => fake()->numberBetween(1, 4),
                    'type_id' => $type->id,
                    'owner_id' => $owner->id,
                ]
            );

            $restaurants[] = $restaurant;
        }

        return $restaurants;
    }

    private function seedRegularUsers(): array
    {
        $usersData = [
            ['name' => 'Jean Dupont', 'email' => 'jean@client.test'],
            ['name' => 'Alice Bernard', 'email' => 'alice@client.test'],
            ['name' => 'Lucas Petit', 'email' => 'lucas@client.test'],
            ['name' => 'Emma Richard', 'email' => 'emma@client.test'],
            ['name' => 'Hugo Moreau', 'email' => 'hugo@client.test'],
            ['name' => 'Chloé Simon', 'email' => 'chloe@client.test'],
            ['name' => 'Nathan Laurent', 'email' => 'nathan@client.test'],
            ['name' => 'Léa Roux', 'email' => 'lea@client.test'],
            ['name' => 'Tom Girard', 'email' => 'tom@client.test'],
            ['name' => 'Manon Blanc', 'email' => 'manon@client.test'],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => Role::USER,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $users;
    }

    private function seedDishes(array $restaurants): void
    {
        $dishesByType = [
            'Le Bistrot Parisien' => [
                ['name' => 'Steak Frites', 'description' => 'Entrecôte grillée avec frites maison et sauce béarnaise', 'price' => 18.90],
                ['name' => 'Confit de Canard', 'description' => 'Cuisse de canard confite avec purée de pommes de terre', 'price' => 19.50],
                ['name' => 'Salade César', 'description' => 'Laitue romaine, croûtons, parmesan, poulet grillé', 'price' => 12.50],
                ['name' => 'Soupe à l\'Oignon', 'description' => 'Soupe gratinée avec fromage et croûtons', 'price' => 8.90],
                ['name' => 'Tarte Tatin', 'description' => 'Tarte aux pommes caramélisées avec crème fraîche', 'price' => 7.50],
                ['name' => 'Plateau de Fromages', 'description' => 'Sélection de fromages français avec pain et raisins', 'price' => 14.00],
            ],
            'La Pasta Bella' => [
                ['name' => 'Spaghetti Carbonara', 'description' => 'Spaghetti avec œufs, pecorino, guanciale et poivre noir', 'price' => 14.50],
                ['name' => 'Lasagne Bolognaise', 'description' => 'Lasagne maison avec sauce bolognaise et béchamel', 'price' => 15.90],
                ['name' => 'Risotto aux Champignons', 'description' => 'Risotto crémeux aux champignons sauvages et parmesan', 'price' => 16.50],
                ['name' => 'Pizza Margherita', 'description' => 'Pizza classique avec sauce tomate, mozzarella et basilic', 'price' => 11.00],
                ['name' => 'Tiramisu', 'description' => 'Dessert italien classique au mascarpone et café', 'price' => 6.90],
                ['name' => 'Bruschetta', 'description' => 'Pain grillé avec tomates fraîches, ail et basilic', 'price' => 7.50],
                ['name' => 'Panna Cotta', 'description' => 'Crème cuite italienne avec coulis de fruits rouges', 'price' => 6.50],
            ],
            'Sushi Master' => [
                ['name' => 'Plateau Sushi Mix', 'description' => '12 pièces assorties de sushis et maki', 'price' => 22.00],
                ['name' => 'Ramen au Poulet', 'description' => 'Bouillon de poulet avec nouilles, œuf mollet et légumes', 'price' => 15.50],
                ['name' => 'Tataki de Thon', 'description' => 'Tranches de thon mi-cuit avec sauce ponzu', 'price' => 18.00],
                ['name' => 'Gyoza au Porc', 'description' => '8 raviolis japonais grillés avec sauce soja', 'price' => 9.50],
                ['name' => 'Tempura de Crevettes', 'description' => 'Crevettes enrobées de pâte légère et frites', 'price' => 13.50],
                ['name' => 'Mochi Glacé', 'description' => 'Boulettes de riz gluant avec glace au thé vert', 'price' => 6.00],
                ['name' => 'Soupe Miso', 'description' => 'Soupe traditionnelle japonaise au tofu et algues', 'price' => 4.50],
            ],
            'Kebab Royal' => [
                ['name' => 'Kebab Classique', 'description' => 'Viande de bœuf épicée avec salade, tomates et sauce blanche', 'price' => 8.50],
                ['name' => 'Tacos XL', 'description' => 'Tacos avec viande au choix, frites et sauces', 'price' => 10.00],
                ['name' => 'Assiette Mixte', 'description' => 'Assiette avec viande grillée, frites et salade', 'price' => 12.50],
                ['name' => 'Cheese Naan', 'description' => 'Pain indien au fromage avec accompagnement', 'price' => 4.50],
                ['name' => 'Falafel Wrap', 'description' => 'Wrap végétarien avec falafels et légumes frais', 'price' => 7.50],
                ['name' => 'Baklava', 'description' => 'Pâtisserie orientale aux noix et au miel', 'price' => 3.50],
            ],
            'Burger Factory' => [
                ['name' => 'Burger Classic', 'description' => 'Bœuf, cheddar, salade, tomate, oignon, sauce maison', 'price' => 12.00],
                ['name' => 'Double Cheese', 'description' => 'Double steak et double cheddar avec bacon croustillant', 'price' => 15.50],
                ['name' => 'Chicken Burger', 'description' => 'Filet de poulet pané avec sauce mayonnaise épicée', 'price' => 11.50],
                ['name' => 'Veggie Burger', 'description' => 'Galette de légumes avec avocat et sauce yaourt', 'price' => 11.00],
                ['name' => 'Frites Truffes', 'description' => 'Frites maison à l\'huile de truffe et parmesan', 'price' => 6.50],
                ['name' => 'Milkshake Vanille', 'description' => 'Milkshake crémeux à la vanille bourbon', 'price' => 5.50],
                ['name' => 'Brownie Chocolat', 'description' => 'Brownie fondant avec glace vanille et noix de pécan', 'price' => 7.00],
                ['name' => 'Onion Rings', 'description' => 'Rondelles d\'oignon panées et frites croustillantes', 'price' => 5.00],
            ],
        ];

        foreach ($restaurants as $restaurant) {
            $dishes = $dishesByType[$restaurant->name] ?? [];
            foreach ($dishes as $dishData) {
                Dish::firstOrCreate(
                    ['name' => $dishData['name'], 'restaurant_id' => $restaurant->id],
                    ['description' => $dishData['description'], 'price' => $dishData['price']]
                );
            }
        }
    }

    private function seedOrders(array $users, array $restaurants): void
    {
        $ordersData = [
            ['user' => 'jean@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Steak Frites', 'Tarte Tatin'], 'state' => OrderDelivered::class],
            ['user' => 'alice@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Spaghetti Carbonara', 'Tiramisu'], 'state' => OrderPending::class],
            ['user' => 'lucas@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Plateau Sushi Mix'], 'state' => OrderPreparing::class],
            ['user' => 'emma@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Burger Classic', 'Frites Truffes'], 'state' => OrderConfirmed::class],
            ['user' => 'hugo@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Kebab Classique', 'Baklava'], 'state' => OrderReady::class],
            ['user' => 'chloe@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Confit de Canard'], 'state' => OrderPending::class],
            ['user' => 'nathan@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Pizza Margherita', 'Bruschetta', 'Panna Cotta'], 'state' => OrderPreparing::class],
            ['user' => 'lea@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Ramen au Poulet', 'Gyoza au Porc'], 'state' => OrderDelivered::class],
            ['user' => 'tom@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Double Cheese', 'Milkshake Vanille'], 'state' => OrderPending::class],
            ['user' => 'manon@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Tacos XL', 'Cheese Naan'], 'state' => OrderConfirmed::class],
            ['user' => 'jean@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Chicken Burger', 'Onion Rings'], 'state' => OrderPending::class],
            ['user' => 'alice@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Tempura de Crevettes', 'Mochi Glacé'], 'state' => OrderPreparing::class],
            ['user' => 'lucas@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Salade César', 'Plateau de Fromages'], 'state' => OrderReady::class],
            ['user' => 'emma@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Assiette Mixte', 'Falafel Wrap'], 'state' => OrderDelivered::class],
            ['user' => 'hugo@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Lasagne Bolognaise', 'Tiramisu'], 'state' => OrderPending::class],
            ['user' => 'chloe@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Veggie Burger', 'Brownie Chocolat'], 'state' => OrderPreparing::class],
            ['user' => 'nathan@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Tataki de Thon', 'Soupe Miso'], 'state' => OrderConfirmed::class],
            ['user' => 'lea@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Kebab Classique', 'Tacos XL', 'Baklava'], 'state' => OrderPending::class],
            ['user' => 'tom@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Soupe à l\'Oignon', 'Steak Frites'], 'state' => OrderDelivered::class],
            ['user' => 'manon@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Risotto aux Champignons', 'Pizza Margherita'], 'state' => OrderReady::class],
        ];

        foreach ($ordersData as $orderData) {
            $user = User::where('email', $orderData['user'])->first();
            $restaurant = Restaurant::where('name', $orderData['restaurant'])->first();

            if (!$user || !$restaurant) {
                continue;
            }

            $total = 0;
            $dishIds = [];

            foreach ($orderData['dishes'] as $dishName) {
                $dish = Dish::where('name', $dishName)->where('restaurant_id', $restaurant->id)->first();
                if ($dish) {
                    $total += $dish->price;
                    $dishIds[$dish->id] = ['quantity' => 1];
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'state' => $orderData['state'],
                'total' => $total,
            ]);

            if (!empty($dishIds)) {
                $order->dishes()->attach($dishIds);
            }
        }
    }
}
