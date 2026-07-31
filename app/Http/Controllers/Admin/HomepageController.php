<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomepageController extends Controller
{
    private const PREVIEW_COUNT = 6;

    public function index(): Response
    {
        $previewImages = [];
        for ($i = 1; $i <= self::PREVIEW_COUNT; $i++) {
            $previewImages[] = AppSetting::get("homepage_preview_image_$i");
        }

        return Inertia::render('Admin/Homepage/Index', [
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

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_subtitle' => ['required', 'string', 'max:300'],
            'hero_badge' => ['nullable', 'string', 'max:80'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'preview_images' => ['array'],
            'preview_images.*' => ['nullable', 'image', 'max:4096'],
        ]);

        AppSetting::set('homepage_hero_title', $data['hero_title']);
        AppSetting::set('homepage_hero_subtitle', $data['hero_subtitle']);
        AppSetting::set('homepage_hero_badge', $data['hero_badge'] ?? null);

        if ($request->hasFile('hero_image')) {
            $this->replaceImage('homepage_hero_image', $request->file('hero_image'));
        }

        foreach ($request->file('preview_images', []) as $index => $file) {
            if ($file) {
                $this->replaceImage('homepage_preview_image_' . ($index + 1), $file);
            }
        }

        return back()->with('success', "Contenu de la page d'accueil mis à jour.");
    }

    public function destroyImage(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'regex:/^homepage_(hero_image|preview_image_[1-6])$/'],
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

        $path = $file->store('homepage', 'public');
        AppSetting::set($key, $path);
    }
}
