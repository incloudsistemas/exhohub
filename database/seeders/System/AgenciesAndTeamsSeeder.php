<?php

namespace Database\Seeders\System;

use App\Models\System\Agency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AgenciesAndTeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $agencies = [
            'Black' => [
                'Connect'
            ],
            'Haus' => [
                'Elite',
                'Start',
                'Prime',
                'Constancy',
            ],
            'Intense' => [
                'Infinity',
                'Focus',
                'Espartanos',
                'Evolution',
                'Dream House',
                'Órion',
            ],
            'Resilience' => [
                'Delta',
                'Elite Prime',
                'Plus',
                'Star Brokers',
                'Bless',
                'Fênix',
                'Compliance',
            ],
            'Save' => [
                'Prime',
                'Império',
                'Legazy',
                'Kairos',
                'Rafaela',
                'Alpha',
                'Bless',
                'Águia',
            ],
            'Solution' => [
                'Delta',
                'Fênix',
                'Impacto',
                'Constant',
                'Parrézia',
            ],
        ];

        foreach ($agencies as $agency => $teams) {
            $agency = Agency::create([
                'name' => $agency,
                'slug' => Str::slug($agency),
            ]);

            foreach ($teams as $team) {
                $agency->teams()->create([
                    'name' => $team,
                    'slug' => $agency->slug . '-' . Str::slug($team),
                ]);
            }
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating Agencies and Teams table');
        Schema::disableForeignKeyConstraints();

        DB::table('agencies')
            ->truncate();

        DB::table('agency_user')
            ->truncate();

        DB::table('teams')
            ->truncate();

        DB::table('team_user')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
