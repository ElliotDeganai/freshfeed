<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FreshFeedRolesAndPermissionsSeeder::class,
            FreshFeedTestUsersSeeder::class,
            IngredientNutritionSeeder::class, // base de référence pour l'estimation calorique
            CategoriesSeeder::class, // base
            FreshFeedDemoRecipesSeeder::class,
        ]);
    }
}
