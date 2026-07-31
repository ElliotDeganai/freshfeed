<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostIngredient extends Model
{
    protected $fillable = ['post_id', 'amount', 'unit', 'name', 'order'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
