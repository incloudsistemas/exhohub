<?php

namespace Database\Seeders\Crm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $sources = [
            'Website',
            'Grupo OLX (CanalPro)',
            'Meta Ads',
            'Google Ads',
            'Tráfego direto',
            'Pesquisa orgânica',
            'Pesquisa paga',
            'Email marketing',
            'Mídia social',
            'Referências',
            'Fontes offline',
            'Migração Arbo',
            'Outras campanhas'
        ];

        foreach ($sources as $source) {
            DB::table('crm_sources')->insert([
                'name'       => $source,
                'slug'       => Str::slug($source),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating CRM Source table');
        Schema::disableForeignKeyConstraints();

        DB::table('crm_sources')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
