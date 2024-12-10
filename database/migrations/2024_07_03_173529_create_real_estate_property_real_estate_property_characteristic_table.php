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
        Schema::create('real_estate_property_real_estate_property_characteristic', function (Blueprint $table) {
            // Imóvel
            $table->foreignId('property_id');
            $table->foreign('property_id', 'property_foreign')
                ->references('id')
                ->on('real_estate_properties')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Características
            $table->foreignId('characteristic_id');
            $table->foreign('characteristic_id', 'characteristic_foreign')
                ->references('id')
                ->on('real_estate_property_characteristics')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite o imóvel ter características duplicadas.
            $table->unique(['property_id', 'characteristic_id'], 'property_characteristic_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('real_estate_property_real_estate_property_characteristic');
    }
};
