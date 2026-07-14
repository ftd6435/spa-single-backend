<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations');
            $table->foreignId('participant_id')->constrained('participants');
            $table->decimal('frais_inscription_requis', 15, 2);
            $table->decimal('frais_inscription_paye', 15, 2)->default(0);
            $table->string('status', 30)->default('en_attente');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['formation_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
