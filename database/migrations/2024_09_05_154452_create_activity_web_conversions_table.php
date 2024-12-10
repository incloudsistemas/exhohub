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
        Schema::create('activity_web_conversions', function (Blueprint $table) {
            $table->id();
            // conversionnable_id e conversionnable_type.
            // $table->morphs('conversionnable');
            $table->unsignedBigInteger('conversionnable_id');
            $table->string('conversionnable_type');
            $table->index(['conversionnable_id', 'conversionnable_type'], 'conversionnable_index');
            $table->json('data')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('activity_web_conversions');
    }
};
