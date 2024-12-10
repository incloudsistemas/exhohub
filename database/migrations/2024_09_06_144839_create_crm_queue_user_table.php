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
        Schema::create('crm_queue_user', function (Blueprint $table) {
            // Fila
            $table->foreignId('queue_id');
            $table->foreign('queue_id')
                ->references('id')
                ->on('crm_queues')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Usuário
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite a fila ter usuários duplicados.
            $table->unique(['queue_id', 'user_id'], 'queue_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_queue_user');
    }
};
