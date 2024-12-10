<?php

namespace Database\Seeders\Crm\Queues;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QueuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $queues = [
            0 => [
                'role'                  => 1, // Website
                'name'                  => '[Website] Imóveis à venda',
                'description'           => '',
                'users_settings'        => 4, // Todos os usuários
                'properties_settings'   => 3, // Todos os imóveis à venda
                'distribution_settings' => 1, // Para os captadores (Lead vai para o próprio captador do imóvel)
                'order'                 => 1,
            ],
            1 => [
                'role'                  => 1, // Website
                'name'                  => '[Website] Imóveis para alugar',
                'description'           => '',
                'users_settings'        => 2, // Customizar por agências
                'properties_settings'   => 4, // Todos os imóveis para alugar
                'distribution_settings' => 2, // Distribuição alternada (Round Robin - Distribuição alternada entre os corretores disponíveis)
                'order'                 => 1,
            ],
            2 => [
                'role'                  => 1, // Website
                'name'                  => '[Website] Empreendimentos',
                'description'           => '',
                'users_settings'        => 4, // Todos os usuários
                'properties_settings'   => 5, // Todos os empreendimentos
                'distribution_settings' => 2, // Distribuição alternada (Round Robin - Distribuição alternada entre os corretores disponíveis)
                'order'                 => 1,
            ],
            3 => [
                'role'                  => 2, // CanalPro
                'name'                  => '[CanalPro] Imóveis à venda',
                'description'           => '',
                'users_settings'        => 1, // Todos os usuários
                'properties_settings'   => 2, // Todos os imóveis à venda
                'distribution_settings' => 1, // Para os captadores (Lead vai para o próprio captador do imóvel)
                'order'                 => 1,
            ],
            4 => [
                'role'                  => 2, // CanalPro
                'name'                  => '[CanalPro] Imóveis para alugar',
                'description'           => '',
                'users_settings'        => 2, // Customizar por agências
                'properties_settings'   => 3, // Todos os imóveis para alugar
                'distribution_settings' => 2, // Distribuição alternada (Round Robin - Distribuição alternada entre os corretores disponíveis)
                'order'                 => 1,
            ],
        ];

        foreach ($queues as $queue) {
            DB::table('crm_queues')->insert([
                'role'                  => $queue['role'],
                'name'                  => $queue['name'],
                'description'           => $queue['description'],
                'users_settings'        => $queue['users_settings'],
                'properties_settings'   => $queue['properties_settings'],
                'distribution_settings' => $queue['distribution_settings'],
                'order'                 => $queue['order'],
                'created_at'            => now(),
                'updated_at'            => now()
            ]);
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating CRM Queues table');
        Schema::disableForeignKeyConstraints();

        DB::table('crm_queues')
            ->truncate();

        DB::table('crm_queue_user')
            ->truncate();

        DB::table('crm_queue_real_estate_property')
            ->truncate();

        DB::table('agency_crm_queue')
            ->truncate();

        DB::table('crm_queue_team')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
