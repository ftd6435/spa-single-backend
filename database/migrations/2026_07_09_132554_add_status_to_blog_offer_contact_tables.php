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
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('cover_path');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('content');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('is_popular');
        });

        Schema::table('offer_types', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('description');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('offer_types', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
