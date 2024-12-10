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
        Schema::create('cms_post_sliders', function (Blueprint $table) {
            $table->id();
            // slideable_id e slideable_type.
            $table->morphs('slideable');
            // Tipo
            // 1 - 'Padrão (Imagem)', 2 - 'Vídeo', 3 - 'Youtube Vídeo'...
            $table->char('role', 1)->default(1);
            // Título
            $table->string('title');
            // Subtítulo
            $table->string('subtitle')->nullable();
            // Conteúdo
            $table->longText('body')->nullable();
            // Chamada para ação (Call to action)
            $table->json('cta')->nullable();
            // Vídeo destaque (embed)
            $table->string('embed_video')->nullable();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
            // Status
            // 0- Inativo, 1 - Ativo, 2 - Rascunho
            $table->char('status', 1)->default(1);
            // Configurações do slider
            // Ocultar texto
            // 'hide_text' => 0 - 'não', 1 - 'sim' // default('não');
            // Estilo
            // 'style' => 1 - 'dark', 2 - 'light', 0 - 'none' // default('dark');
            // 'text_indent' => Identação do texto
            // 1 - 'left', 2 - 'center', 3 - 'right']) // default('left');
            // Identação da imagem
            // 'image_indent' => 1 - 'top', 2 - 'left', 3 - 'center', 4 - 'right', 5 - 'bottom'
            // default('center');
            $table->json('settings')->nullable();
            // Data da publicação
            $table->timestamp('publish_at')->default(now());
            // Data de expiração
            $table->timestamp('expiration_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_post_sliders');
    }
};
