<?php

namespace Database\Seeders\Crm\Funnels;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FunnelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        // Funil de Vendas
        $funnelSalesId = DB::table('crm_funnels')->insertGetId([
            'name'       => 'Funil de Vendas',
            'order'      => 1,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $salesStages = [
            'Pré-atendimento' => [
                'probability' => 10,
                'substages'   => [
                    'Aguardando atendimento',
                    'Tentando contato',
                    'Contato realizado',
                    'Descarte temporário',
                ]
            ],
            'Atendimento/Visita' => [
                'probability' => 20,
                'substages'   => [
                    'Agendada',
                    'Visita confirmada',
                    'Visita realizada',
                ]
            ],
            'Análise de crédito' => [
                'probability' => 30,
                'substages'   => [
                    'Montagem de pasta',
                    'Em análise de crédito',
                    'Pendência de documento',
                    'Restrição CPF',
                    'CONRES',
                    'Aguardando autorização',
                    'Cancelamento de avaliação',
                    'Crédito condicionado',
                    'Engenharia',
                    'Crédito aprovado',
                    'Crédito reprovado',
                ]
            ],
            'Proposta' => [
                'probability' => 50,
                'substages'   => [
                    'Elaborando proposta',
                    'Em análise',
                    'Proposta aprovada',
                    'Proposta reprovada',
                ]
            ],
            'Contrato' => [
                'probability' => 70,
                'substages'   => [
                    'Elaborando contrato',
                    'Contrato emitido',
                    'Enviado para assinatura',
                    'Contrato assinado',
                ]
            ],
            'Pós-venda' => [
                'probability' => 90,
                'substages'   => [
                    'Contrato banco',
                    'Registro',
                    'Conformidade e recebimento'
                ]
            ],
            'Venda finalizada' => [
                'probability' => 100,
                'substages'   => []
            ],
            'Venda perdida' => [
                'probability' => 0,
                'substages'   => []
            ]
        ];

        foreach ($salesStages as $stageName => $stageData) {
            $stageId = DB::table('crm_funnel_stages')->insertGetId([
                'funnel_id'           => $funnelSalesId,
                'name'                => $stageName,
                'business_probability' => $stageData['probability'],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            foreach ($stageData['substages'] as $substageName) {
                DB::table('crm_funnel_substages')->insert([
                    'funnel_stage_id' => $stageId,
                    'name'            => $substageName,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // Funil de Aluguel
        $funnelRentalId = DB::table('crm_funnels')->insertGetId([
            'name'       => 'Funil de Alugueis',
            'order'      => 2,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rentalStages = [
            'Pré-atendimento' => [
                'probability' => 10,
                'substages'   => [
                    'Aguardando atendimento',
                    'Tentando contato',
                    'Contato realizado',
                    'Descarte temporário'
                ]
            ],
            'Atendimento/Visita' => [
                'probability' => 20,
                'substages'   => [
                    'Agendada',
                    'Visita confirmada',
                    'Visita realizada'
                ]
            ],
            'Análise de crédito' => [
                'probability' => 30,
                'substages'   => []
            ],
            'Proposta' => [
                'probability' => 50,
                'substages'   => [
                    'Elaborando proposta',
                    'Em análise',
                    'Proposta aprovada',
                    'Proposta reprovada'
                ]
            ],
            'Contrato' => [
                'probability' => 70,
                'substages'   => [
                    'Elaborando contrato',
                    'Contrato emitido',
                    'Enviado para assinatura',
                    'Contrato assinado'
                ]
            ],
            'Contrato ativo' => [
                'probability' => 100,
                'substages'   => []
            ],
            'Negócio perdido' => [
                'probability' => 0,
                'substages'   => []
            ]
            // 'Renovação' => [
            //     'probability' => 85,
            //     'substages'   => []
            // ],
            // 'Contrato Finalizado' => [
            //     'probability' => 100,
            //     'substages'   => []
            // ]
        ];

        foreach ($rentalStages as $stageName => $stageData) {
            $stageId = DB::table('crm_funnel_stages')->insertGetId([
                'funnel_id'           => $funnelRentalId,
                'name'                => $stageName,
                'business_probability' => $stageData['probability'],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            foreach ($stageData['substages'] as $substageName) {
                DB::table('crm_funnel_substages')->insert([
                    'funnel_stage_id' => $stageId,
                    'name'            => $substageName,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating CRM Funnels table');
        Schema::disableForeignKeyConstraints();

        DB::table('crm_funnels')
            ->truncate();

        DB::table('crm_funnel_stages')
            ->truncate();

        DB::table('crm_funnel_substages')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
