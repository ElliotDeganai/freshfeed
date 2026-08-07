<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\HomepageShowcase;
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

        // Visiteur non connecté : accès limité à la petite sélection mise en avant
        // sur la page d'accueil (recette du moment + tuiles) — pas au catalogue
        // complet. Un utilisateur connecté (même sans lien avec cette recette)
        // continue de tout voir normalement, comme avant.
        if (! Auth::check()) {
            abort_unless(in_array($post->id, HomepageShowcase::guestAllowedPostIds(), true), 404);
        }

        $post->load(['user:id,name,avatar_path', 'categories:id,name', 'ingredients', 'steps.images']);

        $ratingsCount = $post->ratings()->count();
        $ratingsAverage = $ratingsCount ? round($post->ratings()->avg('rating'), 1) : null;
        $myRating = Auth::check()
            ? $post->ratings()->where('user_id', Auth::id())->value('rating')
            : null;
        $isFavorited = Auth::check()
            ? $post->favoritedBy()->where('user_id', Auth::id())->exists()
            : false;

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
            'ratingsAverage' => $ratingsAverage,
            'ratingsCount' => $ratingsCount,
            'myRating' => $myRating,
            'isFavorited' => $isFavorited,
        ]);
    }
}
