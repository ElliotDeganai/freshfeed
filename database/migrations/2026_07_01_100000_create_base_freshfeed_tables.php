<?php
/**
 * ⚠️ FICHIER DE SECOURS — à utiliser UNIQUEMENT si tu pars d'un Laravel neuf et que
 * tes tables `categories`, `posts`, `post_post`, `post_post_values` n'existent pas encore
 * (issues de tes précédentes conversations FreshFeed). Si tu as déjà ce schéma, IGNORE ce
 * dossier entièrement — ne l'importe pas, il entrerait en conflit avec tes migrations.
 *
 * Renomme ce fichier avec un timestamp (ex: 2026_07_02_090000_create_base_freshfeed_tables.php)
 * et place-le dans database/migrations/ si besoin.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('category_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'category_id']);
        });

        // Auto-référencement Post <-> Post (recettes liées), voir _Post-additions.php
        Schema::create('post_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_post_id')->constrained('posts')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('post_post_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_post_id')->constrained('post_post')->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_post_values');
        Schema::dropIfExists('post_post');
        Schema::dropIfExists('category_post');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }
};
