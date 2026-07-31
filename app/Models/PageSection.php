<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'title',
        'type',
        'order',
        'settings',
        'custom_html',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = [
        'hero' => 'Hero (grande bannière)',
        'masonry_grid' => 'Grille masonry',
        'category_carousel' => 'Carrousel de catégorie',
        'featured' => 'Mise en avant (1 à 3 posts)',
        'custom_html' => 'Bloc HTML libre',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'page_section_category')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    /**
     * Résout les posts à afficher pour cette section, en tenant compte
     * du tri configuré dans `settings` (recent|popular|random) et de la limite.
     */
    public function resolvePosts()
    {
        $categoryIds = $this->categories->pluck('id');

        $query = Post::query()
            ->published()
            ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereHas(
                'categories',
                fn ($c) => $c->whereIn('categories.id', $categoryIds)
            ));

        $sort = $this->settings['sort'] ?? 'recent';
        $limit = $this->settings['limit'] ?? 12;

        match ($sort) {
            'popular' => $query->orderByDesc('views_count'),
            'random' => $query->inRandomOrder(),
            default => $query->orderByDesc('published_at'),
        };

        return $query->limit($limit)->get();
    }
}
