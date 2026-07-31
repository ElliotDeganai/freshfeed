<?php
/**
 * ⚠️ FICHIER DE SECOURS — uniquement si App\Models\Category n'existe pas encore.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
