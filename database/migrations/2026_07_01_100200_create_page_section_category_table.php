<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une section peut piocher son contenu dans plusieurs catégories
        // (ex: section "Rapide & sain" = catégories "Rapide" + "Healthy")
        Schema::create('page_section_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_section_id')->constrained('page_sections')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['page_section_id', 'category_id'], 'page_section_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_section_category');
    }
};
