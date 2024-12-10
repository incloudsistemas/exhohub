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
        Schema::create('crm_business_funnel_stages', function (Blueprint $table) {
            $table->id();
            // Negócio
            $table->foreignId('business_id');
            $table->foreign('business_id')
                ->references('id')
                ->on('crm_business')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Funil
            $table->foreignId('funnel_id');
            $table->foreign('funnel_id')
                ->references('id')
                ->on('crm_funnels')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Etapa/Estágio do funil
            $table->foreignId('funnel_stage_id');
            $table->foreign('funnel_stage_id')
                ->references('id')
                ->on('crm_funnel_stages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Sub-etapa/Sub-estágio do funil
            $table->foreignId('funnel_substage_id')->nullable()->default(null);
            $table->foreign('funnel_substage_id')
                ->references('id')
                ->on('crm_funnel_substages')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Motivo da perda (caso o funnel_stage_id seja perda)
            // 1 - Negociou com outra pessoa/empresa
            // 2 - Não gostou do atendimento
            // 3 - Proprietário desistiu do negócio
            // 4 - Valores acima do esperado
            // 5 - Perdido contato com o cliente
            // 6 - Cliente pesquisando
            // 7 - Financiamento não aprovado
            // 8 - Contatar futuramente
            // 9 - Dados do contato inválido
            // 10 - Sem contato
            // 11 - Desacordo de contrato
            // 12 - Desistiu do negócio
            // Dt. competência
            // Data de atualização do estágio do funil
            $table->timestamp('business_at')->default(now());
            $table->integer('loss_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_business_funnel_stages');
    }
};
