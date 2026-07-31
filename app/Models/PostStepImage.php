<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostStepImage extends Model
{
    protected $fillable = ['post_step_id', 'path', 'order'];

    public function step(): BelongsTo
    {
        return $this->belongsTo(PostStep::class, 'post_step_id');
    }
}
