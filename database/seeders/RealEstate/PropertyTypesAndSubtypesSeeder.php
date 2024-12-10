<?php

namespace Database\Seeders\RealEstate;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PropertyTypesAndSubtypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $types = [
            [
                'usage'            => 1, // Residencial
                'name'             => 'Apartamento',
                'abbr'             => 'apt',
                'canal_pro_vrsync' => 'Apartment',
                'subtypes'         => [
                    'Duplex',
                    'Padrão',
                    'Triplex',
                ]
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Casa',
                'abbr'             => 'cas',
                'canal_pro_vrsync' => 'Home',
                'subtypes'         => [
                    'Kitnet / Conjugado',
                    'Padrão',
                    'Sobrado',
                    'Térrea',
                ],
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Casa de Condomínio',
                'abbr'             => 'cdc',
                'canal_pro_vrsync' => 'Condo',
                'subtypes'         => [
                    'Padrão',
                    'Sobrado',
                    'Térrea',
                ],
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Casa de Vila',
                'abbr'             => 'cdv',
                'canal_pro_vrsync' => 'Village House',
                'subtypes'         => [
                    'Padrão',
                    'Sobrado',
                    'Térrea',
                ],
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Chácara',
                'abbr'             => 'cha',
                'canal_pro_vrsync' => 'Farm Ranch',
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Cobertura',
                'abbr'             => 'cob',
                'canal_pro_vrsync' => 'Penthouse',
                'subtypes'         => [
                    'Duplex',
                    'Padrão',
                    'Triplex',
                ],
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Consultório',
                'abbr'             => 'con',
                'canal_pro_vrsync' => 'Consultorio',
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Escritório',
                'abbr'             => 'esc',
                'canal_pro_vrsync' => 'Office',
                'subtypes'         => [
                    'Andar / Laje corporativa',
                    'Consultório',
                    'Padrão',
                ],
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Fazenda / Sítio / Chácara',
                'abbr'             => 'fzd',
                'canal_pro_vrsync' => 'Agricultural',
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Flat',
                'abbr'             => 'flt',
                'canal_pro_vrsync' => 'Flat',
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Galpão / Depósito / Armazém',
                'abbr'             => 'glp',
                'canal_pro_vrsync' => 'Industrial',
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Garagem',
                'abbr'             => 'gar',
                'canal_pro_vrsync' => 'Garagem',
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Hotel / Motel / Pousada',
                'abbr'             => 'htl',
                'canal_pro_vrsync' => 'Hotel',
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Kitnet / Conjugado',
                'abbr'             => 'kit',
                'canal_pro_vrsync' => 'Kitnet',
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Loft',
                'abbr'             => 'lft',
                'canal_pro_vrsync' => 'Loft',
                'subtypes'         => [
                    'Cobertura',
                    'Duplex',
                    'Padrão',
                    'Triplex',
                ],
            ],
            [
                'usage'            => 3, // Residencial e Comercial
                'name'             => 'Lote / Terreno',
                'abbr'             => 'lte',
                'canal_pro_vrsync' => 'Land Lot',
                'subtypes'         => [
                    'Casa de condomínio',
                    'Casa de vila',
                    'Padrão',
                ],
            ],
            [
                'usage'            => 2, // Comercial
                'name'             => 'Ponto comercial / Loja / Box',
                'abbr'             => 'pcl',
                'canal_pro_vrsync' => 'Business',
                'subtypes'         => [
                    'Centro comercial',
                    'Galeria',
                    'Padrão',
                    'Shopping',
                ],
            ],
            [
                'usage'            => 3, // Residencial e Comercial
                'name'             => 'Prédio / Edifício inteiro',
                'abbr'             => 'edf',
                'canal_pro_vrsync' => 'Edificio Comercial',
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Sobrado',
                'abbr'             => 'sob',
                'canal_pro_vrsync' => 'Sobrado',
            ],
            [
                'usage'            => 1, // Residencial
                'name'             => 'Studio',
                'abbr'             => 'std',
                'canal_pro_vrsync' => 'Studio',
            ],
        ];

        foreach ($types as $key => $type) {
            $typeId = DB::table('real_estate_property_types')->insertGetId([
                'usage'            => $type['usage'],
                'name'             => $type['name'],
                'slug'             => Str::slug($type['name']),
                'abbr'             => $type['abbr'],
                'canal_pro_vrsync' => $type['canal_pro_vrsync'],
                'created_at'       => now(),
                'updated_at'       => now()
            ]);

            if (array_key_exists('subtypes', $type)) {
                foreach ($type['subtypes'] as $subtypeName) {
                    $subtype = DB::table('real_estate_property_subtypes')
                        ->where('name', $subtypeName)
                        ->first();

                    if (!$subtype) {
                        $subtypeId = DB::table('real_estate_property_subtypes')->insertGetId([
                            'name'       => $subtypeName,
                            'slug'       => Str::slug($subtypeName),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        $subtypeId = $subtype->id;
                    }

                    DB::table('real_estate_property_subtype_real_estate_property_type')->insert([
                        'type_id'    => $typeId,
                        'subtype_id' => $subtypeId,
                    ]);
                }
            }
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating Property Types and Subtypes table');
        Schema::disableForeignKeyConstraints();

        DB::table('real_estate_property_types')
            ->truncate();

        DB::table('real_estate_property_subtypes')
            ->truncate();

        DB::table('real_estate_property_subtype_real_estate_property_type')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
