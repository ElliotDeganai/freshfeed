<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IngredientNutrition;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IngredientNutritionController extends Controller
{
    public function index(Request $request): Response
    {
        $ingredients = IngredientNutrition::query()
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('name', 'like', '%' . strtolower($request->string('search')) . '%')
            )
            ->when(
                $request->filled('source'),
                fn ($q) => $q->where('source', $request->string('source'))
            )
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Admin/Ingredients/Index', [
            'ingredients' => $ingredients,
            'filters' => $request->only(['search', 'source']),
            'stats' => [
                'total' => IngredientNutrition::count(),
                'from_api' => IngredientNutrition::where('source', 'api')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        IngredientNutrition::create([
            ...$data,
            'source' => 'seed', // ajouté manuellement depuis l'admin, traité comme une valeur de référence fiable
        ]);

        return back()->with('success', 'Ingrédient ajouté.');
    }

    public function update(Request $request, IngredientNutrition $ingredient)
    {
        $data = $this->validated($request, $ingredient->id);

        $ingredient->update($data);

        return back()->with('success', 'Ingrédient mis à jour.');
    }

    public function destroy(IngredientNutrition $ingredient)
    {
        $ingredient->delete();

        return back()->with('success', 'Ingrédient supprimé.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                'unique:ingredient_nutrition,name' . ($ignoreId ? ",$ignoreId" : ''),
            ],
            'kcal_per_100' => ['required', 'integer', 'min:0', 'max:2000'],
            'kind' => ['required', 'in:solid,liquid'],
            'standard_unit_weight' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'aliases' => ['nullable', 'string', 'max:500'], // saisi comme texte, une variante par ligne
        ]);

        $data['name'] = strtolower(trim($data['name']));
        $data['aliases'] = $data['aliases']
            ? array_values(array_filter(array_map('trim', explode("\n", strtolower($data['aliases'])))))
            : [];

        return $data;
    }
}
