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
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            // Auto relacionamento - Sub página - Ref. página parental/pai
            $table->foreignId('page_id')->nullable()->default(null);
            $table->foreign('page_id')
                ->references('id')
                ->on('cms_pages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Chamada para ação (Call to action)
            $table->json('cta')->nullable();
            // Vídeos destaques (embed)
            $table->json('embed_videos')->nullable();
            // Configurações da página
            $table->json('settings')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cms_pages');
    }
};
