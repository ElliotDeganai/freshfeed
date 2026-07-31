<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PostShowController extends Controller
{
    public function show(Post $post): Response
    {
        $isOwner = Auth::check() && Auth::id() === $post->user_id;

        // Une recette en brouillon n'est visible que par son auteur (mode aperçu).
        abort_unless($post->status === 'published' || $isOwner, 404);

        $post->load(['user:id,name,avatar_path', 'categories:id,name', 'ingredients', 'steps.images']);

        return Inertia::render('Post/Show', [
            'post' => [
                ...$post->toArray(),
                // strip_tags en sortie : la description vient d'un éditeur contenteditable
                // qui peut techniquement contenir du HTML collé arbitraire — on limite
                // l'affichage aux balises de mise en forme simples.
                'content_safe' => strip_tags(
                    $post->content ?? '',
                    '<b><i><strong><em><ul><ol><li><h3><br><p>'
                ),
            ],
            'isOwner' => $isOwner,
        ]);
    }
}
