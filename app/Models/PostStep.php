<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostStep extends Model
{
    protected $fillable = ['post_id', 'instruction', 'video_path', 'order'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostStepImage::class)->orderBy('order');
    }
}
