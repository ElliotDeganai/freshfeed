<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private ImageUploadService $imageUploader)
    {
    }
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'app_name' => AppSetting::get('app_name', 'FreshFeed'),
                'browser_title' => AppSetting::get('browser_title', 'FreshFeed'),
                'logo_path' => AppSetting::get('logo_path'),
                'meta_description' => AppSetting::get('meta_description'),
                'canonical_domain' => AppSetting::get('canonical_domain'),
                'slug_feed' => AppSetting::slug('feed'),
                'slug_explore' => AppSetting::slug('explore'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'browser_title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'canonical_domain' => ['nullable', 'string', 'max:255'],
            'slug_feed' => ['nullable', 'string', 'alpha_dash', 'max:100'],
            'slug_explore' => ['nullable', 'string', 'alpha_dash', 'max:100'],
            'logo' => ['nullable', 'mimes:png,jpg,jpeg,svg', 'max:20480'],
        ]);

        AppSetting::set('app_name', $data['app_name']);
        AppSetting::set('browser_title', $data['browser_title']);
        AppSetting::set('meta_description', $data['meta_description'] ?? null);
        AppSetting::set('canonical_domain', $data['canonical_domain'] ?? null);

        if (! empty($data['slug_feed'])) {
            AppSetting::updateSlug('feed', $data['slug_feed']);
        }
        if (! empty($data['slug_explore'])) {
            AppSetting::updateSlug('explore', $data['slug_explore']);
        }

        if ($request->hasFile('logo')) {
            $path = $this->imageUploader->store($request->file('logo'), 'branding', maxWidth: 512, maxHeight: 512, targetMaxBytes: 300_000);
            AppSetting::set('logo_path', $path);
        }

        return back()->with('success', 'Paramètres mis à jour.');
    }
}
