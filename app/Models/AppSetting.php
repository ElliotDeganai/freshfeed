<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("app_setting:$key", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("app_setting:$key");
    }

    /**
     * Slug personnalisable d'une page logique (ex: 'feed', 'explore').
     * Garde un historique dans 'slug_history:{page}' pour gérer les 301.
     */
    public static function slug(string $page): string
    {
        return static::get("slug:$page", $page);
    }

    public static function updateSlug(string $page, string $newSlug): void
    {
        $oldSlug = static::slug($page);

        if ($oldSlug !== $newSlug) {
            $history = json_decode(static::get("slug_history:$page", '[]'), true);
            $history[] = $oldSlug;
            static::set("slug_history:$page", json_encode(array_unique($history)));
        }

        static::set("slug:$page", $newSlug);
    }

    /**
     * Si l'URL demandée correspond à un ancien slug, renvoie la redirection 301.
     */
    public static function resolveRedirect(string $page, string $requestedSlug): ?\Illuminate\Http\RedirectResponse
    {
        $currentSlug = static::slug($page);

        if ($requestedSlug === $currentSlug) {
            return null;
        }

        $history = json_decode(static::get("slug_history:$page", '[]'), true);

        if (in_array($requestedSlug, $history, true)) {
            return Redirect::to('/' . $currentSlug, 301);
        }

        return null;
    }
}
