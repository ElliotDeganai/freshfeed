<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_nutrition', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // nom normalisé (minuscule, sans accents)
            $table->json('aliases')->nullable(); // variantes connues du même ingrédient
            $table->unsignedInteger('kcal_per_100'); // calories pour 100g OU 100ml
            $table->enum('kind', ['solid', 'liquid'])->default('solid');
            // 'seed' = fourni au départ, 'api' = mis en cache depuis Open Food Facts
            $table->enum('source', ['seed', 'api'])->default('seed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_nutrition');
    }
};
