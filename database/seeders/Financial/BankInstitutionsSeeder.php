<?php

namespace Database\Seeders\Financial;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankInstitutionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['code' => 1, 'name' => 'Banco do Brasil'],
            ['code' => 237, 'name' => 'Bradesco'],
            ['code' => 218, 'name' => 'BS2'],
            ['code' => 208, 'name' => 'BTG Pactual'],
            ['code' => 403, 'name' => 'Cora'],
            ['code' => 77, 'name' => 'Inter'],
            ['code' => 341, 'name' => 'Itaú'],
            ['code' => 33, 'name' => 'Santander'],
            ['code' => 999, 'name' => 'Sicoob'],
            ['code' => 748, 'name' => 'Sicredi'],
            ['code' => 332, 'name' => 'Acesso Soluções de pagamento'],
            ['code' => 121, 'name' => 'AgiBank'],
            ['code' => 85, 'name' => 'Ailos'],
            ['code' => 461, 'name' => 'ASAAS IP'],
            ['code' => 3, 'name' => 'Banco da Amazonia'],
            ['code' => 70, 'name' => 'Banco de Brasília'],
            ['code' => 4, 'name' => 'Banco do Nordeste'],
            ['code' => 756, 'name' => 'Bancoob'],
            ['code' => 47, 'name' => 'Banese'],
            ['code' => 21, 'name' => 'Banestes'],
            ['code' => 479, 'name' => 'BankBoston'],
            ['code' => 37, 'name' => 'Banpara'],
            ['code' => 41, 'name' => 'Banrisul'],
            ['code' => 291, 'name' => 'BCN'],
            ['code' => 318, 'name' => 'BMG'],
            ['code' => 752, 'name' => 'BNP Paribas Brasil'],
            ['code' => 173, 'name' => 'BRL Trust DTVM'],
            ['code' => 336, 'name' => 'C6'],
            ['code' => 104, 'name' => 'Caixa Econômica'],
            ['code' => 412, 'name' => 'Capital S.A'],
            ['code' => 273, 'name' => 'CCR de São Miguel do Oeste'],
            ['code' => 745, 'name' => 'Citibank'],
            ['code' => 97, 'name' => 'Cooperativa Central de Credito Noroeste Brasileiro'],
            ['code' => 89, 'name' => 'Credisan'],
            ['code' => 133, 'name' => 'Cresol'],
            ['code' => 707, 'name' => 'Daycoval'],
            ['code' => 450, 'name' => 'Fitbank'],
            ['code' => 125, 'name' => 'Genial'],
            ['code' => 364, 'name' => 'Gerencianet Pagamentos do Brasil'],
            ['code' => 384, 'name' => 'Global SCM'],
            ['code' => 399, 'name' => 'HSBC'],
            ['code' => 376, 'name' => 'JP Morgan'],
            ['code' => 383, 'name' => 'Juno'],
            ['code' => 323, 'name' => 'Mercado Pago'],
            ['code' => 746, 'name' => 'Modal'],
            ['code' => 274, 'name' => 'Money Plus'],
            ['code' => 655, 'name' => 'Neon'],
            ['code' => 998, 'name' => 'Next'],
            ['code' => 151, 'name' => 'Nossa Caixa'],
            ['code' => 260, 'name' => 'Nubank'],
            ['code' => 613, 'name' => 'Omni'],
            ['code' => 212, 'name' => 'Original'],
            ['code' => 290, 'name' => 'Pagseguro'],
            ['code' => 623, 'name' => 'Pan'],
            ['code' => 380, 'name' => 'Picpay'],
            ['code' => 301, 'name' => 'PJBank'],
            ['code' => 93, 'name' => 'Polocred'],
            ['code' => 356, 'name' => 'Real'],
            ['code' => 633, 'name' => 'Rendimento'],
            ['code' => 741, 'name' => 'Ribeirão Preto'],
            ['code' => 422, 'name' => 'Safra'],
            ['code' => 197, 'name' => 'Stone'],
            ['code' => 347, 'name' => 'Sudameris'],
            ['code' => 82, 'name' => 'Topazio'],
            ['code' => 409, 'name' => 'Unibanco'],
            ['code' => 136, 'name' => 'Unicred'],
            ['code' => 99, 'name' => 'Uniprime'],
            ['code' => 84, 'name' => 'Uniprime Norte do Paraná'],
            ['code' => 310, 'name' => 'VORTX DTVM LTDA'],
            ['code' => 355, 'name' => 'Ótimo'],
            ['code' => -20, 'name' => 'Outro Banco'],
        ];

        DB::table('financial_bank_institutions')->insert($banks);
    }
}
