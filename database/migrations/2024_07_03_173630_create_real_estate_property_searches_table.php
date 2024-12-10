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
        Schema::create('real_estate_property_searches', function (Blueprint $table) {
            $table->id();
            // Ip do usuário
            $table->string('user_ip');
            // 1 - 'Imóveis à venda', 2 - 'Imóveis para alugar', 3 - 'Lançamentos'.
            $table->string('role');
            // Tipos dos imóveis
            // 'Apartamento', 'Área/Lote', 'Condomínio', 'Casa residencial'...
            $table->json('types')->nullable();
            // Código
            $table->string('code')->nullable();
            // Localização/Onde deseja morar?
            $table->string('location')->nullable();
            // Estágio do Lançamento/Obra (Caso Lançamentos)
            // 1 - 'Na planta', 2 - 'Em construção', 3 - 'Pronto pra morar'.
            $table->char('enterprise_role', 1)->nullable();
            // Preço (mín)
            $table->bigInteger('min_price')->nullable();
            // Preço (máx)
            $table->bigInteger('max_price')->nullable();
            // Área útil m² (min)
            $table->integer('min_useful_area')->nullable();
            // Área útil m² (máx)
            $table->integer('max_useful_area')->nullable();
            // Área total m² (mín)
            $table->integer('min_total_area')->nullable();
            // Área total m² (máx)
            $table->integer('max_total_area')->nullable();
            // Nº de quartos
            $table->integer('bedroom')->nullable();
            // Nº de suítes
            $table->integer('suite')->nullable();
            // Nº de banheiros
            $table->integer('bathroom')->nullable();
            // Nº de vagas
            $table->integer('garage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_property_searches');
    }
};
