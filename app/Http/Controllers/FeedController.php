<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $posts = Post::query()
            ->published()
            ->with(['user:id,name,avatar_path', 'categories:id,name'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->when(Auth::check(), fn ($q) => $q->addSelect(['is_favorited' => DB::table('post_favorites')
                ->selectRaw('1')
                ->whereColumn('post_favorites.post_id', 'posts.id')
                ->where('post_favorites.user_id', Auth::id())
                ->limit(1),
            ]))
            ->latest('published_at')
            ->paginate(12);

        // Appelé en fetch() depuis le scroll infini — réponse JSON directe,
        // sans passer par Inertia, donc aucune manipulation d'URL/historique possible.
        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        return Inertia::render('Feed', [
            'posts' => $posts,
        ]);
    }
}
