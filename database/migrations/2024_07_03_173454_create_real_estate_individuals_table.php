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
        Schema::create('real_estate_individuals', function (Blueprint $table) {
            $table->id();
            // Tipo do negócio
            // 1 - 'Venda', 2 - 'Aluguel', 3 - 'Venda e Aluguel'.
            // ['SALE', 'RENT', 'SALE AND RENT']
            $table->char('role', 1);
            // Preço da venda
            $table->bigInteger('sale_price')->nullable();
            // Preço do aluguel
            $table->bigInteger('rent_price')->nullable();
            // Pagamento do aluguel
            // 1 - 'Diário', 2 - 'Semanal', 3 - 'Mensal', 4 - 'Trimestral', '5 - Anual'.
            $table->char('rent_period', 1)->nullable();
            // Modalidade do aluguel (garantias)
            // 1 - 'Depósito de segurança', 2 - 'Fiador', 3 - 'Garantia de seguro', 4 - 'Carta de garantia', '5 - Títulos de capitalização'.
            $table->json('rent_warranties')->nullable();
            // Área útil m²
            $table->bigInteger('useful_area')->nullable();
            // Área total m²
            $table->bigInteger('total_area')->nullable();
            // Nº de quartos
            $table->integer('bedroom')->nullable();
            // Nº de suítes
            $table->integer('suite')->nullable();
            // Nº de banheiros
            $table->integer('bathroom')->nullable();
            // Nº de vagas
            $table->integer('garage')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_individuals');
    }
};
