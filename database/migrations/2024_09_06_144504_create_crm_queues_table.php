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
        Schema::create('crm_queues', function (Blueprint $table) {
            $table->id();
            // Funil
            $table->foreignId('funnel_id')->nullable()->default(null);
            $table->foreign('funnel_id')
                ->references('id')
                ->on('crm_funnels')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Integração da fila
            // 1 - 'Website', 2 - 'CanalPro', 3 - 'Meta Ads'.
            $table->integer('role')->nullable();
            // Nome da fila
            $table->string('name')->nullable();
            // Descrição/Observações da fila
            $table->text('description')->nullable();
            // Configurações dos usuários
            // 1 - 'Customizar os usuários'
            // 2 - 'Customizar por agências'
            // 3 - 'Customizar por equipes'
            // 4 - 'Todos os usuários'
            $table->char('users_settings', 1)->nullable();
            // Configurações dos imóveis
            // 1 - 'Customizar os imóveis'
            // 2 - 'Todos os imóveis e empreendimentos'
            // 3 - 'Todos os imóveis à venda'
            // 4 - 'Todos os imóveis para alugar'
            // 5 - 'Todos os empreendimentos'
            $table->char('properties_settings', 1)->nullable();
            // Configurações de distribuição
            // 1 - 'Para os captadores' (Lead vai para o próprio captador do imóvel)
            // 2 - 'Distribuição alternada' (Round Robin - Distribuição alternada entre os corretores disponíveis)
            // 3 - 'Prioridade por performance' (Corretores com melhores performances recebem leads prioritariamente)
            // 4 - 'Disponibilidade' (Lead vai para corretores com menor carga de trabalho atual)
            $table->char('distribution_settings', 1)->nullable();
            // Index atual da distribuição
            $table->integer('distribution_index')->default(0);
            // ID da conta de anúncios
            $table->string('account_id')->nullable();
            // ID da campanha de anúncio
            $table->string('campaign_id')->nullable();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
            // Status
            // 0- Inativo, 1 - Ativo
            $table->char('status', 1)->default(1);
             // Configurações customizadas da fila
             $table->json('custom_settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_queues');
    }
};
