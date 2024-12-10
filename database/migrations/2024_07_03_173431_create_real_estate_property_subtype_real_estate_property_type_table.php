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
        Schema::create('real_estate_property_subtype_real_estate_property_type', function (Blueprint $table) {
            // Tipos de imóveis
            $table->foreignId('type_id');
            $table->foreign('type_id', 'type_foreign')
                ->references('id')
                ->on('real_estate_property_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Subtipo do imóvel
            $table->foreignId('subtype_id');
            $table->foreign('subtype_id', 'subtype_foreign')
                ->references('id')
                ->on('real_estate_property_subtypes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite o subtipo ter tipos duplicados.
            $table->unique(['type_id', 'subtype_id'], 'subtype_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_property_subtype_real_estate_property_type');
    }
};
