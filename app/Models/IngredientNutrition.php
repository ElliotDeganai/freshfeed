<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientNutrition extends Model
{
    protected $fillable = ['name', 'aliases', 'kcal_per_100', 'kind', 'source', 'standard_unit_weight'];

    protected $casts = ['aliases' => 'array'];
}
