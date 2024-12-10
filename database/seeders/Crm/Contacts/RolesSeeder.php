<?php

namespace Database\Seeders\Crm\Contacts;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        $roles = [
            'Assinante',
            'Lead',
            'Cliente',
            'Proprietário',
            'Fornecedor',
            'Outro'
        ];

        foreach ($roles as $role) {
            DB::table('crm_contact_roles')->insert([
                'name'       => $role,
                'slug'       => Str::slug($role),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function truncateTable()
    {
        $this->command->info('Truncating CRM Contact Role table');
        Schema::disableForeignKeyConstraints();

        DB::table('crm_contact_roles')
            ->truncate();

        DB::table('crm_contact_crm_contact_role')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
