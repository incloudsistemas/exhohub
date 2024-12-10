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
        Schema::create('crm_queue_team', function (Blueprint $table) {
            // Fila
            $table->foreignId('queue_id');
            $table->foreign('queue_id')
                ->references('id')
                ->on('crm_queues')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Equipe
            $table->foreignId('team_id');
            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite a fila ter imóveis duplicados.
            $table->unique(['queue_id', 'team_id'], 'queue_team_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('crm_queue_team');
    }
};
