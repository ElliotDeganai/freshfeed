<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    public $timestamps = false; // created_at géré manuellement, pas d'updated_at

    protected $fillable = ['user_id', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];
}
