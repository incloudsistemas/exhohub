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
        Schema::create('real_estate_enterprises', function (Blueprint $table) {
            $table->id();
            // Estágio do Lançamento/Obra
            // 1 - 'Na planta', 2 - 'Em construção', 3 - 'Pronto pra morar'.
            $table->char('role', 1);
            // Preço (mín)
            $table->bigInteger('min_price')->nullable();
            // Preço (máx)
            $table->bigInteger('max_price')->nullable();
            // Área útil m² (min)
            $table->bigInteger('min_useful_area')->nullable();
            // Área útil m² (máx)
            $table->bigInteger('max_useful_area')->nullable();
            // Área total m² (mín)
            $table->bigInteger('min_total_area')->nullable();
            // Área total m² (máx)
            $table->bigInteger('max_total_area')->nullable();
            // Nº de quartos (mín)
            $table->integer('min_bedroom')->nullable();
            // Nº de quartos (máx)
            $table->integer('max_bedroom')->nullable();
            // Nº de suítes (mín)
            $table->integer('min_suite')->nullable();
            // Nº de suítes (máx)
            $table->integer('max_suite')->nullable();
            // Nº de banheiros (mín)
            $table->integer('min_bathroom')->nullable();
            // Nº de banheiros (máx)
            $table->integer('max_bathroom')->nullable();
            // Nº de vagas (mín)
            $table->integer('min_garage')->nullable();
            // Nº de vagas (máx)
            $table->integer('max_garage')->nullable();
            // Acompanhamento da Obra
            // - Projetos => projects
            // - Escavação/Terraplanagem => excavation_landscaping
            // - Fundação => foundation
            // - Estrutura => structure
            // - Instalações Hidrossanitária/Gás/Elétrica => plumbing_gas_electrical_installations
            // - Vedações => seals
            // - Cobertura => roof
            // - Revestimentos => coatings
            // - Acabamentos => finishes
            // - Limpeza/Entrega => cleaning_delivery
            $table->json('construction_follow_up')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_enterprises');
    }
};
