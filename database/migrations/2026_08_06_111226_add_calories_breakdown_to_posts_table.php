<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'calories_breakdown')) {
                // Détail ingrédient par ingrédient de la dernière estimation automatique
                // (nul si les calories ont été saisies manuellement).
                $table->json('calories_breakdown')->nullable()->after('calories_is_auto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('calories_breakdown');
        });
    }
};
