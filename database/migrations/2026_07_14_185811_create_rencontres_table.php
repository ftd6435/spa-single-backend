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
        Schema::create('rencontres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_team_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('init_sondage_id')->constrained()->cascadeOnDelete();
            $table->string('phase');
            $table->date('date_rencontre')->nullable();
            $table->time('heure_rencontre')->nullable();
            $table->foreignId('team_winner_id')->nullable()->constrained('equipes')->nullOnDelete();
            $table->string('final_score')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencontres');
    }
};
