<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_type_id')->constrained('offer_types')->cascadeOnDelete();
            $table->string('plan', 160);
            $table->decimal('price', 12, 2)->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};