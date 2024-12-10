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
        Schema::create('real_estate_properties', function (Blueprint $table) {
            $table->id();
            // propertable_id e propertable_type.
            $table->morphs('propertable');
            // Tipo do imóvel
            // 'Apartamento', 'Área/Lote', 'Condomínio', 'Casa residencial'...
            $table->foreignId('type_id');
            $table->foreign('type_id')
                ->references('id')
                ->on('real_estate_property_types')
                ->onUpdate('cascade')
                ->onDelete('no action');
            // Subtipo do imóvel
            // Ex. Tipo = Apartamento => Padrão, Duplex, Triplex
            $table->foreignId('subtype_id')->nullable()->default(null);
            $table->foreign('subtype_id')
                ->references('id')
                ->on('real_estate_property_subtypes')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Criador/Captador "id_owner"
            $table->foreignId('user_id')->nullable()->default(null);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // O imóvel é
            // 1 - Residencial, 2 - Comercial
            $table->char('usage', 1)->default(1);
            // Código da imobiliária
            $table->string('code')->unique();
            // Tipo de imóvel
            // 'Apartamento', 'Área/Lote', 'Condomínio', 'Casa residencial'...
            // $table->integer('type');
            // Título
            $table->string('title');
            $table->string('slug');
            // Subtítulo
            $table->string('subtitle')->nullable();
            // Chamada
            $table->text('excerpt')->nullable();
            // Conteúdo
            $table->longText('body')->nullable();
            // Notas do captador
            $table->longText('owner_notes')->nullable();
            // Url destaque
            $table->string('url')->nullable();
            // Vídeos destaques (embed)
            $table->json('embed_videos')->nullable();
            // Mostrar o endereço
            // 0- 'Não mostrar', 1 - 'Completo', 2 - 'Somente bairro, cidade e uf', 3 - 'Somente rua, cidade e uf', 4 - 'Somente cidade e uf'.
            $table->char('show_address', 1)->nullable()->default(1);
            // Marca d'água?
            // 0 - 'Sem marca d'água', 1 - 'Centro', 2 - 'Esquerda', 3 - 'Direita',
            // 4 - 'Superior ao centro', 5 - 'Superior a esquerda', 6 - 'Superior a direita',
            // 7 - 'Inferior ao centro', 8 - 'Inferior a esquerda', 9 - 'Inferior a direita'.
            $table->char('show_watermark', 1)->nullable()->default(1);
            // Perfil do imóvel
            // 1 - 'Econômico', 2 - 'Médio padrão', 3 - 'Alto padrão'.
            $table->char('standard', 1)->nullable();
            // Preço/Valor do IPTU
            $table->bigInteger('tax_price')->nullable();
            // Preço/Valor do condomínio
            $table->bigInteger('condo_price')->nullable();
            // Nº de andares
            $table->integer('floors')->nullable();
            // Nº de unidades por andar
            $table->integer('units_per_floor')->nullable();
            // Nº de torres
            $table->integer('towers')->nullable();
            // Ano da construção/lançamento
            $table->char('construct_year', 4)->nullable();
            // Publicar em (Portais)
            $table->json('publish_on')->nullable();
            $table->json('publish_on_data')->nullable();
            // Tags
            $table->json('tags')->nullable();
            // Ordem
            $table->integer('order')->unsigned()->default(1);
            // Em destaque? 1 - sim, 0 - não
            $table->boolean('featured')->default(0);
            // Permitir comentário? 1 - sim, 0 - não
            $table->boolean('comment')->default(0);
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            // Data da publicação
            $table->timestamp('publish_at')->default(now());
            // Data de expiração
            $table->timestamp('expiration_at')->nullable();
            // Status
            // 0- Inativo, 1 - Ativo, 2 - Rascunho
            $table->char('status', 1)->default(1);
            // Atributos personalizados
            $table->json('custom')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Slug único por tipo da propriedade.
            $table->unique(['slug', 'propertable_type'], 'slug_propertable_unique');
            // Permite apenas uma propriedade por registro.
            $table->unique(['propertable_id', 'propertable_type'], 'propertable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('real_estate_properties');
    }
};
