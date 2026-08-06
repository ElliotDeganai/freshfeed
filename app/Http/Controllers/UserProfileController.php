<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    public function show(Request $request, User $user): Response|JsonResponse
    {
        $recipes = Post::query()
            ->where('user_id', $user->id)
            ->published()
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->latest('published_at')
            ->paginate(12);

        // Appelé en fetch() depuis le scroll infini — réponse JSON directe,
        // sans passer par Inertia, donc aucune manipulation d'URL/historique possible.
        if ($request->wantsJson()) {
            return response()->json($recipes);
        }

        return Inertia::render('Users/Show', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_path' => $user->avatar_path,
                // strip_tags en sortie, même précaution que pour la description des recettes.
                'bio_safe' => strip_tags(
                    $user->bio ?? '',
                    '<b><i><strong><em><ul><ol><li><h3><br><p>'
                ),
            ],
            'recipes' => $recipes,
        ]);
    }
}
