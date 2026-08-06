<?php

namespace App\Services;

use App\Models\IngredientNutrition;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalorieEstimatorService
{
    /**
     * Conversion approximative vers des grammes (ou ml, considéré équivalent en poids
     * pour un liquide de densité proche de 1 — approximation volontaire).
     */
    private const UNIT_TO_GRAMS = [
        'kg' => 1000,
        'g' => 1,
        'gramme' => 1,
        'l' => 1000,
        'cl' => 10,
        'ml' => 1,
        'millilitre' => 1,
        'c a soupe' => 15,
        'cuillere a soupe' => 15,
        'c a cafe' => 5,
        'cuillere a cafe' => 5,
        'pincee' => 1,
        'tasse' => 240,
        'verre' => 200,
        'sachet' => 5,
        'gousse' => 5,
        'feuille' => 3,
        'dose' => 30,
        'botte' => 100,
        'poignee' => 30,
    ];

    /**
     * Estime les calories pour 100g/100ml d'une recette à partir de ses ingrédients.
     *
     * @param  array<int, array{amount?: ?string, unit?: ?string, name: string}>  $ingredients
     * @return array{calories: int, unit: string, breakdown: array}|null  null si
     *         l'estimation est impossible (aucun ingrédient quantifiable/reconnu)
     */
    public function estimate(array $ingredients): ?array
    {
        $totalCalories = 0.0;
        $totalGrams = 0.0;
        $solidCount = 0;
        $liquidCount = 0;
        $breakdown = [];

        foreach ($ingredients as $ingredient) {
            $label = trim(($ingredient['amount'] ?? '') . ' ' . ($ingredient['unit'] ?? '') . ' ' . ($ingredient['name'] ?? ''));

            $nutrition = $this->lookup($ingredient['name'] ?? '');
            if (! $nutrition) {
                $breakdown[] = [
                    'label' => $label,
                    'status' => 'skipped',
                    'reason' => 'ingrédient non reconnu (ni en local, ni via Open Food Facts)',
                ];
                continue;
            }

            $grams = $this->toGrams($ingredient['amount'] ?? null, $ingredient['unit'] ?? null, $nutrition->standard_unit_weight);

            if ($grams === null || $grams <= 0) {
                $breakdown[] = [
                    'label' => $label,
                    'status' => 'skipped',
                    'reason' => $nutrition->standard_unit_weight
                        ? "quantité non exploitable (nombre attendu, ex: \"2\")"
                        : "quantité non exploitable (pas d'unité de poids/volume reconnue, et aucun poids unitaire standard connu pour cet ingrédient)",
                ];
                continue;
            }

            $contributed = ($grams / 100) * $nutrition->kcal_per_100;

            $breakdown[] = [
                'label' => $label,
                'status' => 'matched',
                'matched_as' => $nutrition->name,
                'source' => $nutrition->source,
                'grams' => round($grams, 1),
                'kcal_per_100' => $nutrition->kcal_per_100,
                'kcal_contributed' => round($contributed),
            ];

            $totalCalories += $contributed;
            $totalGrams += $grams;
            $nutrition->kind === 'liquid' ? $liquidCount++ : $solidCount++;
        }

        if ($totalGrams <= 0) {
            return null;
        }

        return [
            'calories' => (int) round(($totalCalories / $totalGrams) * 100),
            'unit' => $liquidCount > $solidCount ? 'ml' : 'g',
            'breakdown' => $breakdown,
        ];
    }

    private function toGrams(?string $amount, ?string $unit, ?int $standardUnitWeight): ?float
    {
        if (! $amount || ! is_numeric(trim($amount))) {
            return null;
        }

        $qty = (float) $amount;
        $normalizedUnit = $this->normalize($unit ?? '');

        // Unités "à la pièce" explicites (ex: "2 pièces", "3 unités") → poids unitaire standard.
        $unitWordsMeaningPiece = ['piece', 'pieces', 'unite', 'unites', 'u'];
        if ($normalizedUnit === '' || in_array($normalizedUnit, $unitWordsMeaningPiece, true)) {
            // Pas d'unité de poids/volume précisée (ex: "2 œufs", "1 avocat") → on se rabat
            // sur le poids moyen d'une unité de cet ingrédient, s'il est connu.
            return $standardUnitWeight ? $qty * $standardUnitWeight : null;
        }

        foreach (self::UNIT_TO_GRAMS as $key => $grams) {
            if (str_contains($normalizedUnit, $key)) {
                return $qty * $grams;
            }
        }

        return null; // unité non reconnue et pas un cas "à la pièce"
    }

    private function lookup(string $rawName): ?IngredientNutrition
    {
        $name = $this->normalize($rawName);
        if ($name === '') {
            return null;
        }

        // 1. Correspondance exacte
        $match = IngredientNutrition::where('name', $name)->first();
        if ($match) {
            return $match;
        }

        // 2. Correspondance via les alias connus
        $match = IngredientNutrition::whereJsonContains('aliases', $name)->first();
        if ($match) {
            return $match;
        }

        // 3. Correspondance partielle (ex: "farine de sarrasin bio" contient "farine de sarrasin")
        foreach (IngredientNutrition::all() as $candidate) {
            if (str_contains($name, $candidate->name) || str_contains($candidate->name, $name)) {
                return $candidate;
            }
            foreach ($candidate->aliases ?? [] as $alias) {
                if ($alias && (str_contains($name, $alias) || str_contains($alias, $name))) {
                    return $candidate;
                }
            }
        }

        // 4. Repli : Open Food Facts — le résultat est mis en cache localement (source: api)
        //    pour que les prochaines recherches du même ingrédient n'appellent plus l'API.
        return $this->fetchFromOpenFoodFacts($name);
    }

    private function fetchFromOpenFoodFacts(string $name): ?IngredientNutrition
    {
        try {
            $response = Http::timeout(4)->get('https://world.openfoodfacts.org/cgi/search.pl', [
                'search_terms' => $name,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => 1,
                'fields' => 'product_name,nutriments',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $kcal = $response->json('products.0.nutriments.energy-kcal_100g');
            if (! $kcal || ! is_numeric($kcal)) {
                return null;
            }

            return IngredientNutrition::create([
                'name' => $name,
                'aliases' => [],
                'kcal_per_100' => (int) round($kcal),
                // Open Food Facts ne précise pas fiablement solide/liquide dans cette
                // réponse allégée — on part sur "solid" par défaut (répercussion mineure
                // sur l'unité finale kcal/100g vs kcal/100ml).
                'kind' => 'solid',
                'source' => 'api',
            ]);
        } catch (\Throwable $e) {
            // L'estimation est une aide, pas une fonctionnalité critique : on échoue
            // silencieusement plutôt que de bloquer la création de la recette.
            Log::warning('CalorieEstimatorService: échec appel Open Food Facts', [
                'ingredient' => $name,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['œ', 'æ'], ['oe', 'ae'], $value);
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = $transliterated !== false ? $transliterated : $value;
        $value = preg_replace('/\b(de|du|des|la|le|les|un|une|frais|fraiche|surgele|surgelee|surgeles|surgelees)\b/', '', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9\s]/', '', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
