<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->nullable()->constrained('formations')->cascadeOnDelete();
            $table->string('image_path')->unique();
            $table->uuid('draft_token')->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_images');
    }
};
