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
        Schema::create('crm_business_properties_interest_profiles', function (Blueprint $table) {
            $table->id();
            // Negócio
            $table->foreignId('business_id');
            $table->foreign('business_id')
                ->references('id')
                ->on('crm_business')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Contato
            $table->foreignId('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('crm_contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // O imóvel é
            // 1 - Residencial, 2 - Comercial
            $table->char('usage', 1)->default(1);
            // 1 - 'Imóveis à venda', 2 - 'Imóveis para alugar', 3 - 'Lançamentos'.
            $table->char('role', 1)->nullable();
            // Tipos dos imóveis
            // 'Apartamento', 'Área/Lote', 'Condomínio', 'Casa residencial'...
            $table->json('types')->nullable();
            // Estágio do Lançamento/Obra (Caso Lançamentos)
            // 1 - 'Na planta', 2 - 'Em construção', 3 - 'Pronto pra morar'.
            $table->char('enterprise_role', 1)->nullable();
            // Nº de quartos
            $table->integer('bedroom')->nullable();
            // Nº de suítes
            $table->integer('suite')->nullable();
            // Nº de banheiros
            $table->integer('bathroom')->nullable();
            // Nº de vagas
            $table->integer('garage')->nullable();
            // Área útil m² (min)
            $table->integer('min_useful_area')->nullable();
            // Área útil m² (máx)
            $table->integer('max_useful_area')->nullable();
            // Área total m² (mín)
            $table->integer('min_total_area')->nullable();
            // Área total m² (máx)
            $table->integer('max_total_area')->nullable();
            // Preço (mín)
            $table->bigInteger('min_price')->nullable();
            // Preço (máx)
            $table->bigInteger('max_price')->nullable();
            // Características
            $table->json('characteristics')->nullable();
            // Endereço
            // Uf/Estado
            $table->char('uf', 2)->nullable();
            // Cidade
            $table->string('city')->nullable();
            // Bairros
            $table->json('districts')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_business_properties_interest_profiles');
    }
};
