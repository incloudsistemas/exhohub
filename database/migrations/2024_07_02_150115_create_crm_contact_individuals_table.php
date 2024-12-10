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
        Schema::create('crm_contact_individuals', function (Blueprint $table) {
            $table->id();
            // CPF
            $table->string('cpf')->nullable();
            // RG/ Órgão Expedidor
            $table->string('rg')->nullable();
            // Sexo
            // M - 'Masculino', F - 'Feminino'.
            $table->char('gender', 1)->nullable();
            // Data de nascimento
            $table->date('birth_date')->nullable();
            // Cargo
            $table->string('occupation')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_contact_individuals');
    }
};
