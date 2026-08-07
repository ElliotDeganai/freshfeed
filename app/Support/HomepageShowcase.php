<?php

namespace App\Support;

use App\Models\AppSetting;

class HomepageShowcase
{
    public static function featuredPostId(): ?int
    {
        $id = AppSetting::get('homepage_featured_post_id');

        return $id ? (int) $id : null;
    }

    /**
     * @return array<int, int>
     */
    public static function gridPostIds(): array
    {
        $ids = json_decode(AppSetting::get('homepage_grid_post_ids', '[]') ?? '[]', true);

        return is_array($ids) ? array_map('intval', $ids) : [];
    }

    /**
     * Tous les IDs de recettes visibles par un visiteur non connecté (recette du
     * moment + tuiles), dédupliqués.
     *
     * @return array<int, int>
     */
    public static function guestAllowedPostIds(): array
    {
        $featured = self::featuredPostId();
        $ids = self::gridPostIds();

        if ($featured) {
            array_unshift($ids, $featured);
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
