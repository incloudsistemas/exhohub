<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Cms\PagesSeeder;
use Database\Seeders\Crm\Contacts\ContactsSeeder;
use Database\Seeders\Crm\Contacts\RolesSeeder as ContactRolesSeeder;
use Database\Seeders\Crm\SourcesSeeder;
use Database\Seeders\Crm\Funnels\FunnelsSeeder;
use Database\Seeders\Crm\Queues\QueuesSeeder;
use Database\Seeders\RealEstate\PropertiesSeeder;
use Database\Seeders\RealEstate\PropertyCharacteristicsSeeder;
use Database\Seeders\RealEstate\PropertyTypesAndSubtypesSeeder;
use Database\Seeders\System\AgenciesAndTeamsSeeder;
use Database\Seeders\System\CreciControlStagesSeeder;
use Database\Seeders\System\RolesAndPermissionsSeeder;
use Database\Seeders\System\UsersSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AgenciesAndTeamsSeeder::class,
            UsersSeeder::class,
            CreciControlStagesSeeder::class,

            ContactRolesSeeder::class,
            SourcesSeeder::class,
            // ContactsSeeder::class,
            FunnelsSeeder::class,
            QueuesSeeder::class,

            PropertyTypesAndSubtypesSeeder::class,
            PropertyCharacteristicsSeeder::class,
            // PropertiesSeeder::class,

            PagesSeeder::class,
        ]);
    }
}
