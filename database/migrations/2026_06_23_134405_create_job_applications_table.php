<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void 
    {        
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')
                ->constrained('job_openings')
                ->cascadeOnDelete();

            $table->string('last_name');
            $table->string('first_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('cv_file')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', [
                'pending',
                'reviewed',
                'accepted',
                'rejected'
            ])->default('pending');
            $table->unique(['job_opening_id', 'email']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
