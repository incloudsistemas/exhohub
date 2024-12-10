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
        Schema::create('user_creci_stages', function (Blueprint $table) {
            $table->id();
            // Usuário
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Etapa
            $table->foreignId('creci_control_stage_id');
            $table->foreign('creci_control_stage_id')
                ->references('id')
                ->on('creci_control_stages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->date('member_since')->nullable();
            $table->date('valid_thru')->nullable();
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
        Schema::dropIfExists('user_creci_stages');
    }
};
