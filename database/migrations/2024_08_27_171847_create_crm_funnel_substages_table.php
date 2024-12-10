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
        Schema::create('crm_funnel_substages', function (Blueprint $table) {
            $table->id();
            // Estágio do funil
            $table->foreignId('funnel_stage_id');
            $table->foreign('funnel_stage_id')
                ->references('id')
                ->on('crm_funnel_stages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Nome
            $table->string('name');
            // Descrição
            $table->longText('description')->nullable();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
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
        Schema::dropIfExists('crm_funnel_substages');
    }
};
