<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'calories_is_auto')) {
                // true = estimé automatiquement à partir des ingrédients,
                // false = saisi manuellement par l'utilisateur.
                $table->boolean('calories_is_auto')->default(false)->after('calories_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('calories_is_auto');
        });
    }
};
