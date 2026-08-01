<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $posts = Post::query()
            ->published()
            ->with(['user:id,name,avatar_path', 'categories:id,name'])
            ->latest('published_at')
            ->paginate(10);

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
