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
        Schema::create('support_department_ticket', function (Blueprint $table) {
            // Departamento
            $table->foreignId('department_id');
            $table->foreign('department_id')
                ->references('id')
                ->on('support_departments')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Chamado
            $table->foreignId('ticket_id');
            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite o departamento ter chamados duplicados.
            $table->unique(['department_id', 'ticket_id'], 'ticket_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_department_ticket');
    }
};
