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
        Schema::create('crm_queue_real_estate_property', function (Blueprint $table) {
            // Fila
            $table->foreignId('queue_id');
            $table->foreign('queue_id')
                ->references('id')
                ->on('crm_queues')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Imóvel
            $table->foreignId('property_id');
            $table->foreign('property_id')
                ->references('id')
                ->on('real_estate_properties')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite a fila ter imóveis duplicados.
            $table->unique(['queue_id', 'property_id'], 'queue_property_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_queue_real_estate_property');
    }
};
