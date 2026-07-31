<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    private const PREVIEW_COUNT = 6;

    public function index(): Response|RedirectResponse
    {
        // Un utilisateur déjà connecté n'a plus rien à faire sur la page vitrine —
        // /feed est sa vraie page d'accueil désormais.
        if (Auth::check()) {
            return redirect()->route('feed');
        }

        $previewImages = [];
        for ($i = 1; $i <= self::PREVIEW_COUNT; $i++) {
            $previewImages[] = AppSetting::get("homepage_preview_image_$i");
        }

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'content' => [
                'hero_title' => AppSetting::get('homepage_hero_title', 'Cuisine, partage, découvre.'),
                'hero_subtitle' => AppSetting::get(
                    'homepage_hero_subtitle',
                    "FreshFeed est l'endroit où de vraies personnes partagent ce qu'elles cuisinent vraiment — rapide, healthy ou gourmand."
                ),
                'hero_badge' => AppSetting::get('homepage_hero_badge', 'Le réseau social des cuisiniers du quotidien'),
                'hero_image' => AppSetting::get('homepage_hero_image'),
                'preview_images' => $previewImages,
            ],
        ]);
    }
}
