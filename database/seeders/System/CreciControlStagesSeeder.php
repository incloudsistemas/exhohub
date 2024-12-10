<?php

namespace Database\Seeders\System;

use App\Models\System\CreciControlStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreciControlStagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $stages = [
            [
                'role' => 1, // Trainee
                'name' => 'Aguardando inscrição no curso de TTI'
            ],
            [
                'role' => 1, // Trainee
                'name' => 'Inscrito no curso de TTI'
            ],
            [
                'role' => 2, // Estagiário
                'name' => '1º estágio ativo'
            ],
            [
                'role' => 2, // Estagiário
                'name' => '2º estágio ativo'
            ],
            [
                'role' => 3, // Corretor Pleno
                'name' => 'CRECI definitivo'
            ],
        ];

        foreach ($stages as $stage) {
            CreciControlStage::create([
                'role' => $stage['role'],
                'name' => $stage['name'],
                'slug' => Str::slug($stage['name']),
            ]);

            $this->command->info('Creating CRECI Control Stage ' . $stage['name']);
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating CRECI Control Stages table');
        Schema::disableForeignKeyConstraints();

        DB::table('creci_control_stages')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
