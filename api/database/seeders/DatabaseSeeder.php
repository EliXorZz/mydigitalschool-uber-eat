<?php

namespace Database\Seeders;

use App\Models\RestaurantType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $types = [
            'Fast food',
            'Italien',
            'Chinois',
            'Japonais',
            'Indien',
            'Mexicain',
            'Français',
            'Thaï',
            'Vietnamien',
            'Libanais',
        ];

        foreach ($types as $type) {
            RestaurantType::firstOrCreate(['name' => $type]);
        }
    }
}
