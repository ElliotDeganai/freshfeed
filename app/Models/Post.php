<?php
/**
 * ⚠️ FICHIER DE SECOURS — uniquement si App\Models\Post n'existe pas encore.
 * Copie ce contenu dans app/Models/Post.php ET fusionne ensuite avec
 * _Post-additions.php (scopes published/draft, relation categories déjà incluse ici).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    protected $fillable = [
        'user_id', 'title', 'status', 'published_at', 'content', 'image_path','calories','calories_unit',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    // Recettes liées entre elles (auto-référencement) — voir README FreshFeed
    // pour la logique complète syncRelatedPosts() / PostPostValue.
    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_post', 'post_id', 'related_post_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // 3. Helper pratique pour le badge admin
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && (! $this->published_at || $this->published_at->isPast());
    }

    public function ingredients()
    {
        return $this->hasMany(PostIngredient::class)->orderBy('order');
    }

    public function steps()
    {
        return $this->hasMany(PostStep::class)->orderBy('order');
    }
}
