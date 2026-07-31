<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_step_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_step_id')->constrained('post_steps')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_step_images');
    }
};
