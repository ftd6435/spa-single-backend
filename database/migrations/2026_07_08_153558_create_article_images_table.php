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
        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            // nullable car l'image est uploadée par CKEditor AVANT que l'article n'existe ;
            // elle est rattachée à l'article au moment du store/update
            $table->foreignId('article_id')->nullable()->constrained('articles')->cascadeOnDelete();
            // Nom du fichier sur Cloudflare R2 (dossier images/articles/content/)
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_images');
    }
};
