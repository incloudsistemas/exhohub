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
        Schema::create('crm_contact_individual_crm_contact_legal_entity', function (Blueprint $table) {
            // P. Física
            $table->foreignId('individual_id');
            $table->foreign('individual_id', 'individual_foreign')
                ->references('id')
                ->on('crm_contact_individuals')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // P. Jurídica
            $table->foreignId('legal_entity_id');
            $table->foreign('legal_entity_id', 'legal_entity_foreign')
                ->references('id')
                ->on('crm_contact_legal_entities')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Não permite o contato ter empresas duplicadas.
            $table->unique(['individual_id', 'legal_entity_id'], 'individual_legal_entity_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_contact_individual_crm_contact_legal_entity');
    }
};
