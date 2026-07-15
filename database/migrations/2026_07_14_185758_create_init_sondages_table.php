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
        Schema::create('init_sondages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->json('avantage')->nullable();
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->json('niveau_vote')->nullable();
            $table->json('cadeaux')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('init_sondages');
    }
};
