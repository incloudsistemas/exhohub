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
        Schema::create('agency_crm_queue', function (Blueprint $table) {
            // Agência
            $table->foreignId('agency_id');
            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Fila
            $table->foreignId('queue_id');
            $table->foreign('queue_id')
                ->references('id')
                ->on('crm_queues')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite a fila ter usuários duplicados.
            $table->unique(['agency_id', 'queue_id'], 'agency_queue_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('agency_crm_queue');
    }
};
