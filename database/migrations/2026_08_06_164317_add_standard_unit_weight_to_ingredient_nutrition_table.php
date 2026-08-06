<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_nutrition', function (Blueprint $table) {
            if (! Schema::hasColumn('ingredient_nutrition', 'standard_unit_weight')) {
                // Poids moyen (en grammes) d'une unité "standard" de cet ingrédient,
                // utilisé quand aucune unité de poids/volume n'est précisée
                // (ex: "2 œufs" → 2 × 50g, sans que l'utilisateur ait à écrire "100g").
                $table->unsignedInteger('standard_unit_weight')->nullable()->after('kind');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_nutrition', function (Blueprint $table) {
            $table->dropColumn('standard_unit_weight');
        });
    }
};
