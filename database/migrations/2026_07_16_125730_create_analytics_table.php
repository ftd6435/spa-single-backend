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
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->index();
            $table->string('path');
            $table->string('referrer')->nullable();
            $table->string('device', 20);
            $table->string('browser', 50);
            $table->string('os', 50);
            $table->string('country', 2)->nullable();
            $table->string('ip_hash', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
