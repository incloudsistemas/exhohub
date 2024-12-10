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
        Schema::create('crm_contact_legal_entities', function (Blueprint $table) {
            $table->id();
            // Nome fantasia
            $table->string('trade_name')->nullable();
            // CNPJ
            $table->string('cnpj')->nullable();
            // Inscrição municipal
            // É o número de identificação municipal da sua empresa cadastrado na prefeitura.
            $table->string('municipal_registration')->nullable();
            // Inscrição estadual
            $table->string('state_registration')->nullable();
            // Url do site
            $table->string('url')->nullable();
            // Setor
            $table->string('sector')->nullable();
            //  Nº de funcionários
            $table->integer('num_employees')->nullable();
            // Receita anual
            $table->bigInteger('anual_income')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_contact_legal_entities');
    }
};
