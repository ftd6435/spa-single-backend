<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_category_id')->constrained('formation_categories');
            $table->string('libelle', 200);
            $table->text('short_description')->nullable();
            $table->longText('description');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->unsignedInteger('nombre_places');
            $table->string('lieu_formation');
            $table->date('date_fin_inscription');
            $table->decimal('frais_inscription', 15, 2);
            $table->decimal('frais_formation', 15, 2);
            $table->string('status', 30)->default('en_attente');
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
