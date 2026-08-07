<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Post;
use App\Support\HomepageShowcase;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomepageController extends Controller
{
    private const MAX_GRID_ITEMS = 4;

    public function __construct(private ImageUploadService $imageUploader)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Homepage/Index', [
            'content' => [
                'hero_title' => AppSetting::get('homepage_hero_title', 'Cuisine, partage, découvre.'),
                'hero_subtitle' => AppSetting::get(
                    'homepage_hero_subtitle',
                    "SoRecette est l'endroit où de vraies personnes partagent ce qu'elles cuisinent vraiment — rapide, healthy ou gourmand."
                ),
                'hero_badge' => AppSetting::get('homepage_hero_badge', 'Le réseau social des cuisiniers du quotidien'),
                'hero_image' => AppSetting::get('homepage_hero_image'),
                'featured_post_id' => HomepageShowcase::featuredPostId(),
                'grid_post_ids' => HomepageShowcase::gridPostIds(),
            ],
            // Liste légère de toutes les recettes publiées, pour le sélecteur —
            // filtrage/recherche fait côté client (volume raisonnable à ce stade).
            'availablePosts' => Post::query()
                ->published()
                ->orderBy('title')
                ->get(['id', 'title', 'image_path']),
            'maxGridItems' => self::MAX_GRID_ITEMS,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_subtitle' => ['required', 'string', 'max:300'],
            'hero_badge' => ['nullable', 'string', 'max:80'],
            'hero_image' => ['nullable', 'image', 'max:20480'],
            'featured_post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'grid_post_ids' => ['array', 'max:' . self::MAX_GRID_ITEMS],
            'grid_post_ids.*' => ['integer', 'exists:posts,id'],
        ]);

        AppSetting::set('homepage_hero_title', $data['hero_title']);
        AppSetting::set('homepage_hero_subtitle', $data['hero_subtitle']);
        AppSetting::set('homepage_hero_badge', $data['hero_badge'] ?? null);
        AppSetting::set('homepage_featured_post_id', $data['featured_post_id'] ?? null);
        AppSetting::set('homepage_grid_post_ids', json_encode(array_values($data['grid_post_ids'] ?? [])));

        if ($request->hasFile('hero_image')) {
            $this->replaceImage('homepage_hero_image', $request->file('hero_image'));
        }

        return back()->with('success', "Contenu de la page d'accueil mis à jour.");
    }

    public function destroyImage(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'regex:/^homepage_hero_image$/'],
        ]);

        $path = AppSetting::get($data['key']);
        if ($path) {
            Storage::disk('public')->delete($path);
        }
        AppSetting::set($data['key'], null);

        return back()->with('success', 'Image supprimée.');
    }

    private function replaceImage(string $key, $file): void
    {
        $oldPath = AppSetting::get($key);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $this->imageUploader->store($file, 'homepage', maxWidth: 1920, maxHeight: 1920, targetMaxBytes: 2_500_000);
        AppSetting::set($key, $path);
    }
}
