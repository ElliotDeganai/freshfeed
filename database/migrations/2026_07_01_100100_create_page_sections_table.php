<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('title')->nullable();

            // Type de rendu attendu côté Vue (SectionRenderer.vue switch sur cette valeur)
            // hero | masonry_grid | category_carousel | featured | custom_html
            $table->string('type')->default('masonry_grid');

            $table->unsignedInteger('order')->default(0);

            // Nombre de posts à afficher, tri (recent|popular|random), etc.
            $table->json('settings')->nullable();

            // Utilisé uniquement si type = custom_html
            $table->longText('custom_html')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
