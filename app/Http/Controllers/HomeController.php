<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Post;
use App\Models\PostRating;
use App\Models\User;
use App\Support\HomepageShowcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        // Un utilisateur déjà connecté n'a plus rien à faire sur la page vitrine —
        // /feed est sa vraie page d'accueil désormais.
        if (Auth::check()) {
            return redirect()->route('feed');
        }

        $featuredId = HomepageShowcase::featuredPostId();
        $gridIds = HomepageShowcase::gridPostIds();

        $featured = $featuredId
            ? Post::query()->published()->withAvg('ratings', 'rating')->withCount('ratings')
                ->with('user:id,name')->find($featuredId)
            : null;

        $grid = $gridIds
            ? Post::query()->published()->whereIn('id', $gridIds)->get(['id', 'title', 'image_path'])
                // préserve l'ordre choisi dans l'admin, pas l'ordre SQL
                ->sortBy(fn ($post) => array_search($post->id, $gridIds))
                ->values()
            : collect();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'content' => [
                'hero_title' => AppSetting::get('homepage_hero_title', 'Cuisine, partage, découvre.'),
                'hero_subtitle' => AppSetting::get(
                    'homepage_hero_subtitle',
                    "SoRecette est l'endroit où de vraies personnes partagent ce qu'elles cuisinent vraiment — rapide, healthy ou gourmand."
                ),
                'hero_badge' => AppSetting::get('homepage_hero_badge', 'Le réseau social des cuisiniers du quotidien'),
                'hero_image' => AppSetting::get('homepage_hero_image'),
            ],
            'featuredPost' => $featured,
            'gridPosts' => $grid,
            'stats' => [
                'recipes_count' => Post::query()->published()->count(),
                'members_count' => User::count(),
                'avg_rating' => PostRating::count() ? round(PostRating::avg('rating'), 1) : null,
            ],
        ]);
    }
}
