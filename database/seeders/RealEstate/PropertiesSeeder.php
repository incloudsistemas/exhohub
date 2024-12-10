<?php

namespace Database\Seeders\RealEstate;

use App\Models\Polymorphics\Address;
use App\Models\RealEstate\Enterprise;
use App\Models\RealEstate\Individual;
use App\Models\RealEstate\Property;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        Individual::factory(100)
            ->create()
            ->each(function ($individual) {
                $this->command->info('Creating Individual Property ' . $individual->name);

                Property::factory()->create([
                    'propertable_type' => MorphMapByClass(Individual::class),
                    'propertable_id'   => $individual->id,
                ]);

                Address::factory()->create([
                    'addressable_id'   => $individual->property->id,
                    'addressable_type' => MorphMapByClass(Property::class),
                    'name'             => null,
                    'is_main'          => 1,
                ]);
            });

        Enterprise::factory(100)
            ->create()
            ->each(function ($enterprise) {
                $this->command->info('Creating Enterprise Property ' . $enterprise->name);

                Property::factory()->create([
                    'propertable_type' => MorphMapByClass(Enterprise::class),
                    'propertable_id'   => $enterprise->id,
                ]);

                Address::factory()->create([
                    'addressable_id'   => $enterprise->property->id,
                    'addressable_type' => MorphMapByClass(Property::class),
                    'name'             => null,
                    'is_main'          => 1,
                ]);
            });
    }

    private function truncateTable()
    {
        $this->command->info('Truncating Properties, Individuals and Enterprises tables');
        Schema::disableForeignKeyConstraints();

        DB::table('real_estate_properties')->truncate();
        DB::table('real_estate_individuals')->truncate();
        DB::table('real_estate_enterprises')->truncate();

        DB::table('real_estate_property_real_estate_property_characteristic')->truncate();
        DB::table('crm_contact_real_estate_property')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
