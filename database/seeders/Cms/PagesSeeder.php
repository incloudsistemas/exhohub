<?php

namespace Database\Seeders\Cms;

use App\Models\Cms\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $idxPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                // 'body',
                // 'cta',
                // 'url',
                // 'embed_video',
                // 'video',
                'image',
                // 'images',
                // 'videos',
                // 'embed_videos',
                // 'tags',
                'seo',
                // 'user_id',
                // 'order',
                // 'featured',
                // 'comment',
                // 'publish_at',
                // 'expiration_at',
                // 'status',
                'sliders',
                // 'tabs',
                // 'accordions',
                // 'attachments',
            ],
        ]);

        $idxPg->cmsPost()
            ->create([
                'title'            => 'Página inicial',
                'slug'             => 'index',
                'subtitle'         => 'Para você morar bem!',
                'excerpt'          => 'Compre ou alugue com agilidade, segurança e sem burocracia.',
                'meta_title'       => 'EXHO Imóveis - Imobiliária líder de mercado em Anápolis-GO.',
                'meta_description' => 'Compre ou alugue imóveis com agilidade, segurança e sem burocracia.',
                'publish_at'       => now(),
            ]);

        $salePg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $salePg->cmsPost()
            ->create([
                'title'            => 'Imóveis à venda',
                'slug'             => 'a-venda',
                'meta_title'       => 'Imóveis à venda em Anápolis-GO | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $rentPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $rentPg->cmsPost()
            ->create([
                'title'            => 'Imóveis para alugar',
                'slug'             => 'para-alugar',
                'meta_title'       => 'Imóveis para alugar em Anápolis-GO | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $enterprisePg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $enterprisePg->cmsPost()
            ->create([
                'title'            => 'Lançamentos',
                'slug'             => 'lancamentos',
                'meta_title'       => 'Lançamentos imobiliários em Anápolis-GO | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $announcePg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $announcePg->cmsPost()
            ->create([
                'title'            => 'Anunciar imóvel',
                'slug'             => 'anunciar',
                'meta_title'       => 'Anuncie seu imóvel em Anápolis-GO | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $aboutPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'body',
                'image',
                'seo',
            ],
        ]);

        $aboutPg->cmsPost()
            ->create([
                'title'            => 'Quem somos',
                'slug'             => 'quem-somos',
                'meta_title'       => 'Quem somos | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $blogPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $blogPg->cmsPost()
            ->create([
                'title'            => 'Blog e materiais grátis',
                'slug'             => 'blog',
                'meta_title'       => 'Blog e materiais grátis | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $contactUsPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $contactUsPg->cmsPost()
            ->create([
                'title'            => 'Fale conosco',
                'slug'             => 'fale-conosco',
                'meta_title'       => 'Fale conosco | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $workWithUsPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'image',
                'seo',
            ],
        ]);

        $workWithUsPg->cmsPost()
            ->create([
                'title'            => 'Trabalhe conosco',
                'slug'             => 'trabalhe-conosco',
                'meta_title'       => 'Trabalhe conosco | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);

        $privacyPg = Page::create([
            'settings' => [
                'categories',
                'subtitle',
                'excerpt',
                'body',
                'image',
                'seo',
            ],
        ]);

        $privacyPg->cmsPost()
            ->create([
                'title'            => 'Política de privacidade',
                'slug'             => 'politica-de-privacidade',
                'meta_title'       => 'Política de privacidade | EXHO Imóveis',
                'meta_description' => '',
                'publish_at'       => now(),
            ]);
    }

    private function truncateTable()
    {
        $this->command->info('Truncating Pages tables');
        Schema::disableForeignKeyConstraints();

        DB::table('cms_pages')->truncate();

        DB::table('cms_posts')
            ->where('postable_type', 'cms_pages')
            ->delete();

        // DB::table('cms_post_sliders')
        //     ->where('slideable_type', 'cms_pages')
        //     ->delete();

        // DB::table('cms_post_subcontents')
        //     ->where('contentable_type', 'cms_pages')
        //     ->delete();

        Schema::enableForeignKeyConstraints();
    }
}
