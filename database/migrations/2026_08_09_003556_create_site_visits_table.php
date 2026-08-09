<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            // Clé de session (ou hash IP+navigateur pour les requêtes sans session) —
            // sert uniquement à dédupliquer un même visiteur sur une même journée,
            // jamais affichée ni exploitée individuellement.
            $table->string('visitor_key', 64);
            $table->date('visited_date');
            $table->timestamps();

            $table->unique(['visitor_key', 'visited_date']);
            $table->index('visited_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
