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
            'Fast Food', 'Italien', 'Chinois', 'Japonais', 'Indien',
            'Mexicain', 'Français', 'Thaï', 'Vietnamien', 'Libanais',
            'Burger', 'Pizzeria', 'Sushi', 'Kebab', 'Boulangerie',
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
                'avatar' => 'https://i.pravatar.cc/256?img=70',
                'password' => 'password',
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
                'owner_avatar' => 'https://i.pravatar.cc/256?img=47',
                'restaurant_name' => 'Le Bistrot Parisien',
                'type' => 'Français',
                'city' => 'Paris',
                'score' => 4.6,
                'price_score' => 3,
                'description' => 'Cuisine française traditionnelle dans une ambiance chaleureuse.',
                'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80',
            ],
            [
                'owner_name' => 'Giovanni Rossi',
                'owner_email' => 'giovanni@lapasta.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=12',
                'restaurant_name' => 'La Pasta Bella',
                'type' => 'Italien',
                'city' => 'Lyon',
                'score' => 4.4,
                'price_score' => 2,
                'description' => 'Authentiques pâtes fraîches faites maison tous les jours.',
                'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80',
            ],
            [
                'owner_name' => 'Kenji Tanaka',
                'owner_email' => 'kenji@sushimaster.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=15',
                'restaurant_name' => 'Sushi Master',
                'type' => 'Japonais',
                'city' => 'Bordeaux',
                'score' => 4.8,
                'price_score' => 3,
                'description' => 'Sushi et sashimi de qualité premium avec des produits frais.',
                'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=800&q=80',
            ],
            [
                'owner_name' => 'Ahmed Benali',
                'owner_email' => 'ahmed@kebabroyal.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=57',
                'restaurant_name' => 'Kebab Royal',
                'type' => 'Kebab',
                'city' => 'Marseille',
                'score' => 4.1,
                'price_score' => 1,
                'description' => 'Les meilleurs kebabs et tacos de la ville, viande 100% halal.',
                'image' => 'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?w=800&q=80',
            ],
            [
                'owner_name' => 'Sophie Martin',
                'owner_email' => 'sophie@burgerfactory.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=49',
                'restaurant_name' => 'Burger Factory',
                'type' => 'Burger',
                'city' => 'Toulouse',
                'score' => 4.3,
                'price_score' => 2,
                'description' => 'Burgers gourmets avec du bœuf frais et des frites maison.',
                'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&q=80',
            ],
            [
                'owner_name' => 'Priya Sharma',
                'owner_email' => 'priya@spicepalace.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=44',
                'restaurant_name' => 'Spice Palace',
                'type' => 'Indien',
                'city' => 'Nice',
                'score' => 4.5,
                'price_score' => 2,
                'description' => 'Cuisine indienne authentique, épices importées directement de Mumbai.',
                'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
            ],
            [
                'owner_name' => 'Carlos Rivera',
                'owner_email' => 'carlos@tacomundo.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=60',
                'restaurant_name' => 'Taco Mundo',
                'type' => 'Mexicain',
                'city' => 'Nantes',
                'score' => 4.2,
                'price_score' => 1,
                'description' => 'Street food mexicaine authentique, tacos et burritos frais.',
                'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&q=80',
            ],
            [
                'owner_name' => 'Mei Lin',
                'owner_email' => 'mei@dragonwok.test',
                'owner_avatar' => 'https://i.pravatar.cc/256?img=25',
                'restaurant_name' => 'Dragon Wok',
                'type' => 'Chinois',
                'city' => 'Strasbourg',
                'score' => 4.0,
                'price_score' => 2,
                'description' => 'Cuisine cantonaise et pékinoise, dim sum le week-end.',
                'image' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=800&q=80',
            ],
        ];

        $restaurants = [];
        foreach ($data as $item) {
            $owner = User::firstOrCreate(
                ['email' => $item['owner_email']],
                [
                    'name' => $item['owner_name'],
                    'avatar' => $item['owner_avatar'],
                    'password' => 'password',
                    'role' => Role::RESTAURANT,
                    'email_verified_at' => now(),
                ]
            );

            $type = RestaurantType::where('name', $item['type'])->first();

            $restaurant = Restaurant::firstOrCreate(
                ['name' => $item['restaurant_name']],
                [
                    'image' => $item['image'],
                    'description' => $item['description'],
                    'city' => $item['city'],
                    'score' => $item['score'],
                    'price_score' => $item['price_score'],
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
            ['name' => 'Jean Dupont',    'email' => 'jean@client.test',   'avatar' => 'https://i.pravatar.cc/256?img=1'],
            ['name' => 'Alice Bernard',  'email' => 'alice@client.test',  'avatar' => 'https://i.pravatar.cc/256?img=32'],
            ['name' => 'Lucas Petit',    'email' => 'lucas@client.test',  'avatar' => 'https://i.pravatar.cc/256?img=3'],
            ['name' => 'Emma Richard',   'email' => 'emma@client.test',   'avatar' => 'https://i.pravatar.cc/256?img=38'],
            ['name' => 'Hugo Moreau',    'email' => 'hugo@client.test',   'avatar' => 'https://i.pravatar.cc/256?img=5'],
            ['name' => 'Chloé Simon',    'email' => 'chloe@client.test',  'avatar' => 'https://i.pravatar.cc/256?img=41'],
            ['name' => 'Nathan Laurent', 'email' => 'nathan@client.test', 'avatar' => 'https://i.pravatar.cc/256?img=7'],
            ['name' => 'Léa Roux',       'email' => 'lea@client.test',    'avatar' => 'https://i.pravatar.cc/256?img=45'],
            ['name' => 'Tom Girard',     'email' => 'tom@client.test',    'avatar' => 'https://i.pravatar.cc/256?img=9'],
            ['name' => 'Manon Blanc',    'email' => 'manon@client.test',  'avatar' => 'https://i.pravatar.cc/256?img=48'],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'avatar' => $data['avatar'],
                    'password' => 'password',
                    'role' => Role::USER,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $users;
    }

    private function seedDishes(array $restaurants): void
    {
        $dishesByRestaurant = [
            'Le Bistrot Parisien' => [
                ['name' => 'Steak Frites', 'description' => 'Entrecôte grillée avec frites maison et sauce béarnaise', 'price' => 18.90, 'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80'],
                ['name' => 'Confit de Canard', 'description' => 'Cuisse de canard confite avec purée de pommes de terre', 'price' => 19.50, 'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80'],
                ['name' => 'Salade César', 'description' => 'Laitue romaine, croûtons, parmesan, poulet grillé', 'price' => 12.50, 'image' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=800&q=80'],
                ['name' => 'Soupe à l\'Oignon', 'description' => 'Soupe gratinée avec fromage et croûtons', 'price' => 8.90, 'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=800&q=80'],
                ['name' => 'Tarte Tatin', 'description' => 'Tarte aux pommes caramélisées avec crème fraîche', 'price' => 7.50, 'image' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&q=80'],
                ['name' => 'Plateau de Fromages', 'description' => 'Sélection de fromages français avec pain et raisins', 'price' => 14.00, 'image' => 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=800&q=80'],
                ['name' => 'Foie Gras Maison', 'description' => 'Foie gras de canard avec toast brioche et confiture de figue', 'price' => 16.50, 'image' => 'https://images.unsplash.com/photo-1607532941433-304659e8198a?w=800&q=80'],
            ],
            'La Pasta Bella' => [
                ['name' => 'Spaghetti Carbonara', 'description' => 'Spaghetti avec œufs, pecorino, guanciale et poivre noir', 'price' => 14.50, 'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=800&q=80'],
                ['name' => 'Lasagne Bolognaise', 'description' => 'Lasagne maison avec sauce bolognaise et béchamel', 'price' => 15.90, 'image' => 'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?w=800&q=80'],
                ['name' => 'Risotto aux Champignons', 'description' => 'Risotto crémeux aux champignons sauvages et parmesan', 'price' => 16.50, 'image' => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=800&q=80'],
                ['name' => 'Pizza Margherita', 'description' => 'Pizza classique avec sauce tomate, mozzarella et basilic', 'price' => 11.00, 'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&q=80'],
                ['name' => 'Tiramisu', 'description' => 'Dessert italien classique au mascarpone et café', 'price' => 6.90, 'image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800&q=80'],
                ['name' => 'Bruschetta', 'description' => 'Pain grillé avec tomates fraîches, ail et basilic', 'price' => 7.50, 'image' => 'https://images.unsplash.com/photo-1506280754576-f6fa8a873550?w=800&q=80'],
                ['name' => 'Panna Cotta', 'description' => 'Crème cuite italienne avec coulis de fruits rouges', 'price' => 6.50, 'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=800&q=80'],
                ['name' => 'Gnocchi Gorgonzola', 'description' => 'Gnocchi maison nappés d\'une crème au gorgonzola et noix', 'price' => 15.00, 'image' => 'https://images.unsplash.com/photo-1555949258-eb67b1ef0ceb?w=800&q=80'],
            ],
            'Sushi Master' => [
                ['name' => 'Plateau Sushi Mix', 'description' => '12 pièces assorties de sushis et maki', 'price' => 22.00, 'image' => 'https://images.unsplash.com/photo-1553621042-f6e147245754?w=800&q=80'],
                ['name' => 'Ramen au Poulet', 'description' => 'Bouillon de poulet avec nouilles, œuf mollet et légumes', 'price' => 15.50, 'image' => 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=800&q=80'],
                ['name' => 'Tataki de Thon', 'description' => 'Tranches de thon mi-cuit avec sauce ponzu', 'price' => 18.00, 'image' => 'https://images.unsplash.com/photo-1617196034183-421b4040ed20?w=800&q=80'],
                ['name' => 'Gyoza au Porc', 'description' => '8 raviolis japonais grillés avec sauce soja', 'price' => 9.50, 'image' => 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=800&q=80'],
                ['name' => 'Tempura de Crevettes', 'description' => 'Crevettes enrobées de pâte légère et frites', 'price' => 13.50, 'image' => 'https://images.unsplash.com/photo-1615361200141-f45040f367be?w=800&q=80'],
                ['name' => 'Mochi Glacé', 'description' => 'Boulettes de riz gluant avec glace au thé vert', 'price' => 6.00, 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800&q=80'],
                ['name' => 'Soupe Miso', 'description' => 'Soupe traditionnelle japonaise au tofu et algues', 'price' => 4.50, 'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80'],
                ['name' => 'Chirashi Bowl', 'description' => 'Bol de riz vinaigré garni de sashimis assortis', 'price' => 19.50, 'image' => 'https://images.unsplash.com/photo-1617196034082-2af12a86f5d6?w=800&q=80'],
            ],
            'Kebab Royal' => [
                ['name' => 'Kebab Classique', 'description' => 'Viande de bœuf épicée avec salade, tomates et sauce blanche', 'price' => 8.50, 'image' => 'https://images.unsplash.com/photo-1561043433-aaf687c4cf04?w=800&q=80'],
                ['name' => 'Tacos XL', 'description' => 'Tacos avec viande au choix, frites et sauces', 'price' => 10.00, 'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&q=80'],
                ['name' => 'Assiette Mixte', 'description' => 'Assiette avec viande grillée, frites et salade', 'price' => 12.50, 'image' => 'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?w=800&q=80'],
                ['name' => 'Cheese Naan', 'description' => 'Pain indien au fromage fondu avec chutney maison', 'price' => 4.50, 'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&q=80'],
                ['name' => 'Falafel Wrap', 'description' => 'Wrap végétarien avec falafels croustillants et légumes frais', 'price' => 7.50, 'image' => 'https://images.unsplash.com/photo-1512058556646-c4da40fba323?w=800&q=80'],
                ['name' => 'Baklava', 'description' => 'Pâtisserie orientale feuilletée aux noix et au miel', 'price' => 3.50, 'image' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548?w=800&q=80'],
                ['name' => 'Ayran', 'description' => 'Boisson traditionnelle au yaourt salé', 'price' => 2.50, 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800&q=80'],
            ],
            'Burger Factory' => [
                ['name' => 'Burger Classic', 'description' => 'Bœuf, cheddar, salade, tomate, oignon, sauce maison', 'price' => 12.00, 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80'],
                ['name' => 'Double Cheese', 'description' => 'Double steak et double cheddar avec bacon croustillant', 'price' => 15.50, 'image' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=800&q=80'],
                ['name' => 'Chicken Burger', 'description' => 'Filet de poulet pané avec sauce mayonnaise épicée', 'price' => 11.50, 'image' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=800&q=80'],
                ['name' => 'Veggie Burger', 'description' => 'Galette de légumes avec avocat frais et sauce yaourt', 'price' => 11.00, 'image' => 'https://images.unsplash.com/photo-1520072959219-c595dc870360?w=800&q=80'],
                ['name' => 'Frites Truffes', 'description' => 'Frites maison à l\'huile de truffe et parmesan râpé', 'price' => 6.50, 'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800&q=80'],
                ['name' => 'Milkshake Vanille', 'description' => 'Milkshake crémeux à la vanille bourbon', 'price' => 5.50, 'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=800&q=80'],
                ['name' => 'Brownie Chocolat', 'description' => 'Brownie fondant avec glace vanille et noix de pécan', 'price' => 7.00, 'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80'],
                ['name' => 'Onion Rings', 'description' => 'Rondelles d\'oignon panées et croustillantes', 'price' => 5.00, 'image' => 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=800&q=80'],
            ],
            'Spice Palace' => [
                ['name' => 'Butter Chicken', 'description' => 'Poulet mariné dans une sauce tomate crémeuse aux épices', 'price' => 15.90, 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80'],
                ['name' => 'Saag Paneer', 'description' => 'Épinards mijotés avec cubes de fromage frais', 'price' => 13.50, 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80'],
                ['name' => 'Biryani Agneau', 'description' => 'Riz basmati parfumé avec agneau tendre et safran', 'price' => 17.00, 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80'],
                ['name' => 'Naan Beurre', 'description' => 'Pain indien traditionnel au four tandoor', 'price' => 3.00, 'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&q=80'],
                ['name' => 'Samosa', 'description' => '3 samosas farcis aux légumes et épices, chutney inclus', 'price' => 6.50, 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=800&q=80'],
                ['name' => 'Mango Lassi', 'description' => 'Boisson fraîche à la mangue et au yaourt', 'price' => 4.00, 'image' => 'https://images.unsplash.com/photo-1553361371-9b22f78e8b1d?w=800&q=80'],
                ['name' => 'Gulab Jamun', 'description' => 'Petites boules de lait frites dans un sirop de rose', 'price' => 5.50, 'image' => 'https://images.unsplash.com/photo-1610611159228-bc83b4d78c26?w=800&q=80'],
            ],
            'Taco Mundo' => [
                ['name' => 'Tacos al Pastor', 'description' => 'Porc mariné à l\'ananas, coriandre et oignon', 'price' => 9.50, 'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&q=80'],
                ['name' => 'Burrito Bœuf', 'description' => 'Tortilla garnie de bœuf épicé, riz, haricots et guacamole', 'price' => 11.00, 'image' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800&q=80'],
                ['name' => 'Nachos Cheese', 'description' => 'Tortillas chips avec fromage fondu, jalapeños et crème', 'price' => 8.00, 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80'],
                ['name' => 'Guacamole Maison', 'description' => 'Avocat frais, tomate, oignon, citron vert et coriandre', 'price' => 5.50, 'image' => 'https://images.unsplash.com/photo-1512058556646-c4da40fba323?w=800&q=80'],
                ['name' => 'Quesadilla Poulet', 'description' => 'Tortilla grillée farcie au poulet et fromage fondu', 'price' => 10.00, 'image' => 'https://images.unsplash.com/photo-1618040996337-56904b7850b9?w=800&q=80'],
                ['name' => 'Churros', 'description' => 'Beignets espagnols sucrés avec sauce chocolat chaude', 'price' => 5.00, 'image' => 'https://images.unsplash.com/photo-1624371414361-e670edf4a1e5?w=800&q=80'],
                ['name' => 'Agua Fresca', 'description' => 'Boisson fraîche aux fruits de saison', 'price' => 3.50, 'image' => 'https://images.unsplash.com/photo-1553361371-9b22f78e8b1d?w=800&q=80'],
            ],
            'Dragon Wok' => [
                ['name' => 'Canard Laqué', 'description' => 'Canard rôti laqué à la sauce hoisin, pancakes inclus', 'price' => 21.00, 'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80'],
                ['name' => 'Dim Sum Vapeur', 'description' => '8 raviolis vapeur assortis avec sauce soja et gingembre', 'price' => 10.50, 'image' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=800&q=80'],
                ['name' => 'Nouilles Sautées', 'description' => 'Nouilles de blé sautées aux légumes et sauce aux huîtres', 'price' => 12.00, 'image' => 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=800&q=80'],
                ['name' => 'Soupe Won Ton', 'description' => 'Bouillon clair avec raviolis au porc et crevettes', 'price' => 8.50, 'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80'],
                ['name' => 'Poulet Kung Pao', 'description' => 'Poulet sauté aux cacahuètes, poivrons et piments', 'price' => 14.00, 'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=800&q=80'],
                ['name' => 'Riz Cantonnais', 'description' => 'Riz sauté aux œufs, petits pois, jambon et crevettes', 'price' => 9.00, 'image' => 'https://images.unsplash.com/photo-1512058454905-6b841e7ad132?w=800&q=80'],
                ['name' => 'Bao Porc Rôti', 'description' => 'Pain vapeur fourré au porc rôti laqué et pickles', 'price' => 7.50, 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800&q=80'],
            ],
        ];

        foreach ($restaurants as $restaurant) {
            $dishes = $dishesByRestaurant[$restaurant->name] ?? [];
            foreach ($dishes as $dishData) {
                Dish::firstOrCreate(
                    ['name' => $dishData['name'], 'restaurant_id' => $restaurant->id],
                    ['description' => $dishData['description'], 'image' => $dishData['image'], 'price' => $dishData['price']]
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
            ['user' => 'chloe@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Confit de Canard', 'Plateau de Fromages'], 'state' => OrderPending::class],
            ['user' => 'nathan@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Pizza Margherita', 'Bruschetta', 'Panna Cotta'], 'state' => OrderPreparing::class],
            ['user' => 'lea@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Ramen au Poulet', 'Gyoza au Porc', 'Soupe Miso'], 'state' => OrderDelivered::class],
            ['user' => 'tom@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Double Cheese', 'Milkshake Vanille', 'Onion Rings'], 'state' => OrderPending::class],
            ['user' => 'manon@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Tacos XL', 'Cheese Naan'], 'state' => OrderConfirmed::class],
            ['user' => 'jean@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Chicken Burger', 'Onion Rings', 'Brownie Chocolat'], 'state' => OrderPending::class],
            ['user' => 'alice@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Tempura de Crevettes', 'Mochi Glacé', 'Chirashi Bowl'], 'state' => OrderPreparing::class],
            ['user' => 'lucas@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Salade César', 'Foie Gras Maison'], 'state' => OrderReady::class],
            ['user' => 'emma@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Assiette Mixte', 'Falafel Wrap'], 'state' => OrderDelivered::class],
            ['user' => 'hugo@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Lasagne Bolognaise', 'Gnocchi Gorgonzola', 'Tiramisu'], 'state' => OrderPending::class],
            ['user' => 'chloe@client.test', 'restaurant' => 'Burger Factory', 'dishes' => ['Veggie Burger', 'Frites Truffes', 'Brownie Chocolat'], 'state' => OrderPreparing::class],
            ['user' => 'nathan@client.test', 'restaurant' => 'Sushi Master', 'dishes' => ['Tataki de Thon', 'Soupe Miso', 'Plateau Sushi Mix'], 'state' => OrderConfirmed::class],
            ['user' => 'lea@client.test', 'restaurant' => 'Kebab Royal', 'dishes' => ['Kebab Classique', 'Tacos XL', 'Baklava'], 'state' => OrderPending::class],
            ['user' => 'tom@client.test', 'restaurant' => 'Le Bistrot Parisien', 'dishes' => ['Soupe à l\'Oignon', 'Steak Frites', 'Tarte Tatin'], 'state' => OrderDelivered::class],
            ['user' => 'manon@client.test', 'restaurant' => 'La Pasta Bella', 'dishes' => ['Risotto aux Champignons', 'Pizza Margherita', 'Panna Cotta'], 'state' => OrderReady::class],
            ['user' => 'jean@client.test', 'restaurant' => 'Spice Palace', 'dishes' => ['Butter Chicken', 'Naan Beurre', 'Mango Lassi'], 'state' => OrderDelivered::class],
            ['user' => 'alice@client.test', 'restaurant' => 'Taco Mundo', 'dishes' => ['Tacos al Pastor', 'Guacamole Maison'], 'state' => OrderPending::class],
            ['user' => 'lucas@client.test', 'restaurant' => 'Dragon Wok', 'dishes' => ['Dim Sum Vapeur', 'Soupe Won Ton'], 'state' => OrderConfirmed::class],
            ['user' => 'emma@client.test', 'restaurant' => 'Spice Palace', 'dishes' => ['Biryani Agneau', 'Samosa', 'Mango Lassi'], 'state' => OrderPreparing::class],
            ['user' => 'hugo@client.test', 'restaurant' => 'Taco Mundo', 'dishes' => ['Burrito Bœuf', 'Nachos Cheese', 'Churros'], 'state' => OrderReady::class],
            ['user' => 'chloe@client.test', 'restaurant' => 'Dragon Wok', 'dishes' => ['Canard Laqué', 'Riz Cantonnais'], 'state' => OrderPending::class],
            ['user' => 'nathan@client.test', 'restaurant' => 'Spice Palace', 'dishes' => ['Saag Paneer', 'Naan Beurre', 'Gulab Jamun'], 'state' => OrderDelivered::class],
            ['user' => 'lea@client.test', 'restaurant' => 'Taco Mundo', 'dishes' => ['Quesadilla Poulet', 'Churros', 'Agua Fresca'], 'state' => OrderPreparing::class],
            ['user' => 'tom@client.test', 'restaurant' => 'Dragon Wok', 'dishes' => ['Poulet Kung Pao', 'Nouilles Sautées', 'Bao Porc Rôti'], 'state' => OrderConfirmed::class],
            ['user' => 'manon@client.test', 'restaurant' => 'Spice Palace', 'dishes' => ['Butter Chicken', 'Samosa', 'Naan Beurre'], 'state' => OrderPending::class],
        ];

        foreach ($ordersData as $orderData) {
            $user = User::where('email', $orderData['user'])->first();
            $restaurant = Restaurant::where('name', $orderData['restaurant'])->first();

            if (! $user || ! $restaurant) {
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

            if (! empty($dishIds)) {
                $order->dishes()->attach($dishIds);
            }
        }
    }
}
