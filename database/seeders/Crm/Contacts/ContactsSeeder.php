<?php

namespace Database\Seeders\Crm\Contacts;

use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Contacts\Individual;
use App\Models\Crm\Contacts\LegalEntity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        Individual::factory(100)->create()->each(function ($individual) {
            $this->command->info('Creating Individual Contact ' . $individual->name);

            Contact::factory()->create([
                'contactable_type' => MorphMapByClass(Individual::class),
                'contactable_id'   => $individual->id,
            ]);
        });

        LegalEntity::factory(100)->create()->each(function ($legalEntity) {
            $this->command->info('Creating Legal Entity Contact ' . $legalEntity->name);

            Contact::factory()->create([
                'contactable_type' => MorphMapByClass(LegalEntity::class),
                'contactable_id'   => $legalEntity->id,
            ]);
        });
    }

    private function truncateTable()
    {
        $this->command->info('Truncating CRM Contacts, Individuals and LegalEntities tables');
        Schema::disableForeignKeyConstraints();

        DB::table('crm_contacts')
            ->truncate();

        DB::table('crm_contact_individuals')
            ->truncate();

        DB::table('crm_contact_legal_entities')
            ->truncate();

        DB::table('crm_contact_crm_contact_role')
            ->truncate();

        DB::table('crm_contact_individual_crm_contact_legal_entity')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
