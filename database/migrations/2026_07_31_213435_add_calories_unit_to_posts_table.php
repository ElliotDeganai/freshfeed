<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'calories_unit')) {
                // 'g' (solide, pour 100g) ou 'ml' (liquide, pour 100ml)
                $table->string('calories_unit', 2)->nullable()->after('calories');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('calories_unit');
        });
    }
};
