<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = ['visitor_key', 'visited_date'];

    protected $casts = ['visited_date' => 'date'];
}
