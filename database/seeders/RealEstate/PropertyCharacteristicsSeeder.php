<?php

namespace Database\Seeders\RealEstate;

use App\Models\RealEstate\PropertyCharacteristic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PropertyCharacteristicsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $characteristics = [
            // CARACTERÍSTICAS: 1 - DIFERENCIAIS
            ['role' => 1, 'name' => 'Aceita animais', 'canal_pro_vrsync' => 'Pets Allowed'],
            ['role' => 1, 'name' => 'Ambientes integrados', 'canal_pro_vrsync' => 'Integrated Environments'],
            ['role' => 1, 'name' => 'Andar inteiro', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Aquário', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Aquecimento', 'canal_pro_vrsync' => 'Heating'],
            ['role' => 1, 'name' => 'Ar condicionado', 'canal_pro_vrsync' => 'Cooling'],
            ['role' => 1, 'name' => 'Área de serviço', 'canal_pro_vrsync' => "Maid's Quarters"],
            ['role' => 1, 'name' => 'Armário embutido', 'canal_pro_vrsync' => 'Builtin Wardrobe'],
            ['role' => 1, 'name' => 'Armário embutido no quarto', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Armário na cozinha', 'canal_pro_vrsync' => 'Kitchen Cabinets'],
            ['role' => 1, 'name' => 'Armário no banheiro', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Árvore frutífera', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Banheira', 'canal_pro_vrsync' => 'Bathtub'],
            ['role' => 1, 'name' => 'Banheiro de serviço', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Bar', 'canal_pro_vrsync' => 'Bar'],
            ['role' => 1, 'name' => 'Bar na piscina', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Biblioteca', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Box blindex', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Carpete', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Casa de caseiro', 'canal_pro_vrsync' => 'Caretaker House'],
            ['role' => 1, 'name' => 'Casa de fundo', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Casa sede', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Churrasqueira na varanda', 'canal_pro_vrsync' => 'Barbecue Balcony'],
            ['role' => 1, 'name' => 'Chuveiro a gás', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Cimento queimado', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Closet', 'canal_pro_vrsync' => 'Closet'],
            ['role' => 1, 'name' => 'Condomínio sustentável', 'canal_pro_vrsync' => 'Eco Condominium'],
            ['role' => 1, 'name' => 'Conexão à internet', 'canal_pro_vrsync' => 'Internet Connection'],
            ['role' => 1, 'name' => 'Copa', 'canal_pro_vrsync' => 'Copa'],
            ['role' => 1, 'name' => 'Cozinha americana', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Cozinha gourmet', 'canal_pro_vrsync' => 'Gourmet Kitchen'],
            ['role' => 1, 'name' => 'Cozinha grande', 'canal_pro_vrsync' => 'Large Kitchen'],
            ['role' => 1, 'name' => 'Dependência de empregados', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Depósito', 'canal_pro_vrsync' => 'Warehouse'],
            ['role' => 1, 'name' => 'Despensa', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Drywall', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Edícula', 'canal_pro_vrsync' => 'Edicule'],
            ['role' => 1, 'name' => 'Escada', 'canal_pro_vrsync' => 'Stair'],
            ['role' => 1, 'name' => 'Escritório', 'canal_pro_vrsync' => 'Home Office'],
            ['role' => 1, 'name' => 'Fogão', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Freezer', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Geminada', 'canal_pro_vrsync' => 'Geminada'],
            ['role' => 1, 'name' => 'Gesso - Sanca - Teto Rebaixado', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Hidromassagem', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Imóvel de esquina', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Interfone', 'canal_pro_vrsync' => 'Intercom'],
            ['role' => 1, 'name' => 'Isolamento acústico', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Isolamento térmico', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Janela de alumínio', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Janela grande', 'canal_pro_vrsync' => 'Large Window'],
            ['role' => 1, 'name' => 'Laje', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Lareira', 'canal_pro_vrsync' => 'Fireplace'],
            ['role' => 1, 'name' => 'Lavabo', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Meio andar', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Mezanino', 'canal_pro_vrsync' => 'Mezzanine'],
            ['role' => 1, 'name' => 'Mobiliado', 'canal_pro_vrsync' => 'Furnished'],
            ['role' => 1, 'name' => 'Muro de vidro', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Muro e grade', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Móvel planejado', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Ofurô', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Perto de escolas', 'canal_pro_vrsync' => 'Close to schools'],
            ['role' => 1, 'name' => 'Perto de hospitais', 'canal_pro_vrsync' => 'Close to hospitals'],
            ['role' => 1, 'name' => 'Perto de shopping center', 'canal_pro_vrsync' => 'Close to shopping centers'],
            ['role' => 1, 'name' => 'Perto de transporte público', 'canal_pro_vrsync' => 'Close to public transportation	'],
            ['role' => 1, 'name' => 'Perto de vias de acesso', 'canal_pro_vrsync' => 'Close to main roads/avenues'],
            ['role' => 1, 'name' => 'Piscina privativa', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Piso de madeira', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Piso elevado', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Piso frio', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Piso laminado', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Piso vinílico', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Platibanda', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Porcelanato', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Possui divisória', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Pé direito alto', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Quarto de serviço', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Quarto extra reversível', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Quintal', 'canal_pro_vrsync' => 'Backyard'],
            ['role' => 1, 'name' => 'Rua asfaltada', 'canal_pro_vrsync' => 'Paved Street'],
            ['role' => 1, 'name' => 'Sala de almoço', 'canal_pro_vrsync' => 'Lunch Room'],
            ['role' => 1, 'name' => 'Sala de jantar', 'canal_pro_vrsync' => 'Dinner Room'],
            ['role' => 1, 'name' => 'Sala grande', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Sala pequena', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'TV a cabo', 'canal_pro_vrsync' => 'Cable Television'],
            ['role' => 1, 'name' => 'Varanda', 'canal_pro_vrsync' => 'Balcony'],
            ['role' => 1, 'name' => 'Varanda fechada com vidro', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Varanda gourmet', 'canal_pro_vrsync' => 'Gourmet Balcony'],
            ['role' => 1, 'name' => 'Ventilação natural', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Vista exterior', 'canal_pro_vrsync' => 'Exterior View'],
            ['role' => 1, 'name' => 'Vista panorâmica', 'canal_pro_vrsync' => ''],
            ['role' => 1, 'name' => 'Vista para a montanha', 'canal_pro_vrsync' => 'Mountain View'],
            ['role' => 1, 'name' => 'Vista para lago', 'canal_pro_vrsync' => 'Lake View'],
            ['role' => 1, 'name' => 'Vista para o mar', 'canal_pro_vrsync' => 'Ocean View'],

            // CARACTERÍSTICAS: 2 - LAZER E ESPORTE
            ['role' => 2, 'name' => 'Academia', 'canal_pro_vrsync' => 'Gym'],
            ['role' => 2, 'name' => 'Área de lazer', 'canal_pro_vrsync' => 'Recreation Area'],
            ['role' => 2, 'name' => 'Arvorismo', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Brinquedoteca', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Campo de futebol', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Campo de golfe', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Churrasqueira', 'canal_pro_vrsync' => 'BBQ'],
            ['role' => 2, 'name' => 'Cinema', 'canal_pro_vrsync' => 'Media Room'],
            ['role' => 2, 'name' => 'Espaço fitness', 'canal_pro_vrsync' => 'Fitness Room'],
            ['role' => 2, 'name' => 'Espaço gourmet', 'canal_pro_vrsync' => 'Gourmet Area'],
            ['role' => 2, 'name' => 'Forno de pizza', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Jardim', 'canal_pro_vrsync' => 'Garden Area'],
            ['role' => 2, 'name' => 'Orquidário', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Piscina', 'canal_pro_vrsync' => 'Pool'],
            ['role' => 2, 'name' => 'Piscina aquecida', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Piscina coberta', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Piscina infantil', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Piscina para adulto', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Piscina semi-olímpica', 'canal_pro_vrsync' => 'Semi Olympic Pool'],
            ['role' => 2, 'name' => 'Pista de cooper', 'canal_pro_vrsync' => 'Jogging track'],
            ['role' => 2, 'name' => 'Pista de skate', 'canal_pro_vrsync' => ''],
            ['role' => 2, 'name' => 'Playground', 'canal_pro_vrsync' => 'Playground'],
            ['role' => 2, 'name' => 'Quadra de areia', 'canal_pro_vrsync' => 'Sand Pit'],
            ['role' => 2, 'name' => 'Quadra de futebol', 'canal_pro_vrsync' => 'Indoor Soccer'],
            ['role' => 2, 'name' => 'Quadra poliesportiva', 'canal_pro_vrsync' => 'Sports Court'],
            ['role' => 2, 'name' => 'Quadra de squash', 'canal_pro_vrsync' => 'Squash'],
            ['role' => 2, 'name' => 'Quadra de tênis', 'canal_pro_vrsync' => 'Tennis court'],
            ['role' => 2, 'name' => 'Salão de festas', 'canal_pro_vrsync' => 'Party Room'],
            ['role' => 2, 'name' => 'Salão de jogos', 'canal_pro_vrsync' => 'Game room'],
            ['role' => 2, 'name' => 'Sauna', 'canal_pro_vrsync' => 'Sauna'],

            // CARACTERÍSTICAS: 3 - SEGURANÇA
            ['role' => 3, 'name' => 'Câmera de segurança', 'canal_pro_vrsync' => ''],
            ['role' => 3, 'name' => 'Cerca', 'canal_pro_vrsync' => ''],
            ['role' => 3, 'name' => 'Circuito de segurança', 'canal_pro_vrsync' => 'TV Security'],
            ['role' => 3, 'name' => 'Condomínio fechado', 'canal_pro_vrsync' => 'Fenced Yard'],
            ['role' => 3, 'name' => 'Fechadura digital', 'canal_pro_vrsync' => 'Digital Locker'],
            ['role' => 3, 'name' => 'Guarita', 'canal_pro_vrsync' => 'Armored Security Cabin'],
            ['role' => 3, 'name' => 'Portão eletrônico', 'canal_pro_vrsync' => ''],
            ['role' => 3, 'name' => 'Portaria 24h', 'canal_pro_vrsync' => 'Security Guard on Duty'],
            ['role' => 3, 'name' => 'Ronda/Vigilância', 'canal_pro_vrsync' => 'Patrol'],
            ['role' => 3, 'name' => 'Sistema de alarme', 'canal_pro_vrsync' => 'Alarm System'],
            ['role' => 3, 'name' => 'Vigia', 'canal_pro_vrsync' => 'Controlled Access'],

            // CARACTERÍSTICAS: 4 - COMODIDADES E SERVIÇOS
            ['role' => 4, 'name' => 'Acesso para deficientes', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Bicicletário', 'canal_pro_vrsync' => 'Bicycles Place'],
            ['role' => 4, 'name' => 'Canil', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Carregador eletrônico para carro e bicicleta', 'canal_pro_vrsync' => 'Eletric Charger'],
            ['role' => 4, 'name' => 'Celeiro', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Centro de estética', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Children care', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Cobertura coletiva', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Coffee shop', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Coleta seletiva de lixo', 'canal_pro_vrsync' => 'Eco Garbage Collector'],
            ['role' => 4, 'name' => 'Curral', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Deck', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Coworking', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Elevador', 'canal_pro_vrsync' => 'Elevator'],
            ['role' => 4, 'name' => 'Energia solar', 'canal_pro_vrsync' => 'Solar Energy'],
            ['role' => 4, 'name' => 'Entrada de serviço', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Entrada lateral', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Espaço de beleza', 'canal_pro_vrsync' => 'Beauty Room'],
            ['role' => 4, 'name' => 'Espaço pet', 'canal_pro_vrsync' => 'Pet Space'],
            ['role' => 4, 'name' => 'Espaço teen', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Espaço verde / Parque', 'canal_pro_vrsync' => 'Green space / Park'],
            ['role' => 4, 'name' => 'Espaço zen', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Gerador elétrico', 'canal_pro_vrsync' => 'Generator'],
            ['role' => 4, 'name' => 'Gramado', 'canal_pro_vrsync' => 'Lawn'],
            ['role' => 4, 'name' => 'Hall de entrada', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Heliponto', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Horta', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Lago', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Marina', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Muro de escalada', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Pasto', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Pomar', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Praça', 'canal_pro_vrsync' => 'Square'],
            ['role' => 4, 'name' => 'Redário', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Reservatório de água', 'canal_pro_vrsync' => 'Water Tank'],
            ['role' => 4, 'name' => 'Restaurante', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Rio', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Sala de massagem', 'canal_pro_vrsync' => 'Massage Room'],
            ['role' => 4, 'name' => 'Sala de reunião', 'canal_pro_vrsync' => 'Meeting Room'],
            ['role' => 4, 'name' => 'Salão de convenção', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Solarium', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Vestiário para diaristas', 'canal_pro_vrsync' => ''],
            ['role' => 4, 'name' => 'Lavanderia', 'canal_pro_vrsync' => 'Laundry'],
            ['role' => 4, 'name' => 'Spa', 'canal_pro_vrsync' => 'Spa'],
            ['role' => 4, 'name' => 'Zelador', 'canal_pro_vrsync' => 'Caretaker'],
        ];

        foreach ($characteristics as $characteristic) {
            PropertyCharacteristic::create([
                'role'             => $characteristic['role'],
                'name'             => $characteristic['name'],
                'canal_pro_vrsync' => $characteristic['canal_pro_vrsync'],
                'slug'             => Str::slug($characteristic['name']),
            ]);

            $this->command->info('Creating Characteristic ' . $characteristic['name']);
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating Property Characteristics table');
        Schema::disableForeignKeyConstraints();

        DB::table('real_estate_property_characteristics')->truncate();
        // DB::table('real_estate_property_real_estate_property_characteristic')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
