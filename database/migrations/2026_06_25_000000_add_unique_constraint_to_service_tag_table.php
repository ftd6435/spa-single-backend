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
        Schema::table('service_tag', function (Blueprint $table) {
            $table->unique(['service_id', 'tag_id'], 'service_tag_service_id_tag_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_tag', function (Blueprint $table) {
            $table->dropUnique('service_tag_service_id_tag_id_unique');
        });
    }
};