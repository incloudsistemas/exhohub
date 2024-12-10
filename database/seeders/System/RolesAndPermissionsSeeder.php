<?php

namespace Database\Seeders\System;

use App\Models\System\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->forgetCachedPermissions();
        $this->truncatePermissionTables();

        // If there isn't any role related to roles permissions,
        // then force the creation of Roles Permissions
        // $this->forceCreateRolesPermission();

        $config = Config::get('permission_seeder.roles_structure');

        if ($config === null) {
            $this->command->error("The configuration has not been published.");
            return;
        }

        $mapPermission = collect(Config::get('permission_seeder.permissions_map'));

        foreach ($config as $key => $modules) {
            // Create a new role
            $role = Role::firstOrCreate(['name' => $key]);

            $this->command->info('Creating Role ' . strtoupper($key));

            // Delay of 1 seconds
            // sleep(1);

            // Reading role permission modules
            foreach ($modules as $module => $value) {
                foreach (explode(',', $value) as $perm) {
                    $permissionValue = $mapPermission->get($perm);

                    $permission = Permission::firstOrCreate([
                        'name' => $permissionValue . ' ' . $module,
                    ]);

                    $this->command->info('Creating Permission to ' . $permissionValue . ' for ' . $module);

                    // Attach permission to the role
                    $role->givePermissionTo($permission);

                    // Delay of 1 seconds
                    // sleep(1);
                }
            }

            if (Config::get('permission_seeder.create_users')) {
                $this->command->info("Creating '{$key}' user");

                // Create default user for each role
                $user = User::create([
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'email' => strtolower($key).'@i2c-admin.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]);

                $user->assignRole($role);
            }
        }

        $this->forgetCachedPermissions();
    }

    private function forceCreateRolesPermission ()
    {
        $permissionNames = [
            'Cadastrar Níveis de Acessos',
            'Visualizar Níveis de Acessos',
            'Editar Níveis de Acessos',
            'Deletar Níveis de Acessos',
        ];

        foreach ($permissionNames as $key => $name) {
            Permission::firstOrCreate(['name' => $name]);

            $this->command->info('Creating Permission ' . $name);
        }
    }

    private function forgetCachedPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function truncatePermissionTables()
    {
        $this->command->info('Truncating Roles and Permissions tables');
        Schema::disableForeignKeyConstraints();

        DB::table('model_has_permissions')->truncate();
        // DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();

        if (Config::get('permission_seeder.truncate_tables')) {
            DB::table('roles')->truncate();
            DB::table('permissions')->truncate();

            if (Config::get('permission_seeder.create_users')) {
                $usersTable = (new User)->getTable();
                DB::table($usersTable)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
