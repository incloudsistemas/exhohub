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
        Schema::create('crm_contact_real_estate_property', function (Blueprint $table) {
            // Contato
            $table->foreignId('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('crm_contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Imóvel
            $table->foreignId('property_id');
            $table->foreign('property_id')
                ->references('id')
                ->on('real_estate_properties')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Papel do usuário
            // 1 - 'Proprietário', ...
            $table->char('role', 1);
            // Não permite o contato ter imóveis duplicados em relação ao papel que ele desempenha.
            $table->unique(['contact_id', 'property_id', 'role'], 'contact_property_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_contact_real_estate_property');
    }
};
