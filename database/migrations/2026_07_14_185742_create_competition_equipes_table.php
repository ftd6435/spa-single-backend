<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competition_equipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['equipe_id', 'competition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_equipes');
    }
};
