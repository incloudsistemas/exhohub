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
        Schema::create('crm_business', function (Blueprint $table) {
            $table->id();
            // Criador/Captador "id_owner"
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Contato
            $table->foreignId('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('crm_contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Funil atual
            $table->foreignId('funnel_id');
            $table->foreign('funnel_id')
                ->references('id')
                ->on('crm_funnels')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Estágio atual do funil
            $table->foreignId('funnel_stage_id');
            $table->foreign('funnel_stage_id')
                ->references('id')
                ->on('crm_funnel_stages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Sub-estágio atual do estágio atual do funil
            $table->foreignId('funnel_substage_id')->nullable()->default(null);
            $table->foreign('funnel_substage_id')
                ->references('id')
                ->on('crm_funnel_substages')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Origem do contato
            $table->foreignId('source_id')->nullable()->default(null);
            $table->foreign('source_id')
                ->references('id')
                ->on('crm_sources')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Nome do negócio
            $table->string('name')->nullable();
            // Descrição/Observações do negócio
            $table->text('description')->nullable();
            // Preço total
            $table->bigInteger('price')->nullable();
            // % da comissão
            $table->integer('commission_percentage')->nullable();
            // Valor da comissão
            $table->bigInteger('commission_price')->nullable();
            // Prioridade
            // 1 - Baixa, 2 - Média, 3 - Alta
            $table->char('priority', 1)->nullable();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
            // Dt. competência
            // Data em que o negócio ocorreu
            $table->timestamp('business_at')->default(now());
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
        Schema::dropIfExists('crm_business');
    }
};
