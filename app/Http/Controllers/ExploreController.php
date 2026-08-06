<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $posts = Post::query()
            ->published()
            ->with(['user:id,name', 'categories:id,name'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->when(
                $request->filled('category'),
                fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.id', $request->integer('category')))
            )
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        // Appelé en fetch() depuis le scroll infini — réponse JSON directe,
        // sans passer par Inertia, donc aucune manipulation d'URL/historique possible.
        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        $categories = Category::withCount('posts')->orderBy('name')->get();

        return Inertia::render('Explore', [
            'categories' => $categories,
            'posts' => $posts,
            'activeCategory' => $request->integer('category') ?: null,
        ]);
    }
}
