<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FavoritesController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        // Chaque recette listée ici EST forcément en favori (c'est la liste elle-même) —
        // pas besoin d'un flag is_favorited, contrairement au Fil/Explorer/Mes recettes.
        $posts = Post::query()
            ->published()
            ->join('post_favorites', function ($join) {
                $join->on('post_favorites.post_id', '=', 'posts.id')
                    ->where('post_favorites.user_id', '=', Auth::id());
            })
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('posts.title', 'like', '%' . $request->string('search') . '%')
            )
            ->select('posts.*')
            ->with(['user:id,name,avatar_path', 'categories:id,name'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->orderByDesc('post_favorites.created_at')
            ->paginate(12)
            ->withQueryString();

        // Appelé en fetch() depuis le scroll infini — réponse JSON directe,
        // sans passer par Inertia, donc aucune manipulation d'URL/historique possible.
        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        return Inertia::render('Favorites/Index', [
            'posts' => $posts,
            'filters' => $request->only('search'),
        ]);
    }
}
