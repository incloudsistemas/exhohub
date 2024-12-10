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
        Schema::create('crm_business_real_estate_property', function (Blueprint $table) {
            // Negócio
            $table->foreignId('business_id');
            $table->foreign('business_id')
                ->references('id')
                ->on('crm_business')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Imóvel
            $table->foreignId('property_id');
            $table->foreign('property_id')
                ->references('id')
                ->on('real_estate_properties')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite o negócio ter imóveis duplicados.
            $table->unique(['business_id', 'property_id'], 'business_property_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_business_real_estate_property');
    }
};
