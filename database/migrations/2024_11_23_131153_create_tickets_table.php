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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            // Criador/Captador "id_owner"
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Título
            $table->string('title');
            // $table->string('slug')->unique();
            // Conteúdo
            $table->longText('body')->nullable();
            // Prioridade
            // 1 - Baixa, 2 - Média, 3 - Alta
            $table->char('priority', 1)->nullable();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
            // Data em que o chamado finalizou
            $table->timestamp('finished_at')->nullable();
            // Status
            // 0 - Aguardando Atendimento, 1 - Aberto, 2 - Finalizado
            $table->char('status', 1)->default(1);
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
        Schema::dropIfExists('tickets');
    }
};
