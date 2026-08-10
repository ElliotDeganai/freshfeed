<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Base de catégories volontairement large et variée — l'objectif est que
     * chaque utilisateur trouve rapidement celle qui correspond à sa recette,
     * qu'il pense par type de plat, régime, origine géographique ou occasion.
     *
     * Remplace WorldCuisineCategoriesSeeder (consolidé ici) — les catégories déjà
     * créées par FreshFeedDemoRecipesSeeder (Healthy, Rapide, Vegan, etc.) sont
     * reprises ici aussi, sans risque de doublon grâce à firstOrCreate().
     */
    public function run(): void
    {
        $groups = [
            'Type de plat' => [
                'Entrée', 'Plat principal', 'Dessert', 'Apéro',
                'Sauces & condiments', 'Soupes & veloutés', 'Salades', 'Pâtisserie',
            ],

            'Repas & moments' => [
                'Petit-déjeuner', 'Brunch', 'Goûter', 'Boissons',
            ],

            'Régime & alimentation' => [
                'Healthy', 'Vegan', 'Végétarien', 'Sans gluten', 'Sans lactose',
                'Low carb', 'Riche en protéines', 'Comfort food', 'Low calories'
            ],

            'Cuisine du monde' => [
                'Italien', 'Japonais', 'Thaï', 'Indien', 'Coréen', 'Chinois',
                'Mexicain', 'Libanais & mezze', 'Maghrébin', 'Antillais & créole',
                'Sud-américain', 'Africain', 'Street food',
            ],

            'Praticité & occasions' => [
                'Rapide', 'Batch cooking', 'Petit budget', 'Spécial enfants',
                'Fêtes & occasions', 'Barbecue & grillades',
            ],
        ];

        $total = 0;
        foreach ($groups as $group => $names) {
            foreach ($names as $name) {
                Category::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
                $total++;
            }
            $this->command->info("→ {$group} (" . count($names) . ')');
        }

        $this->command->info("{$total} catégories vérifiées/créées au total.");
    }
}
