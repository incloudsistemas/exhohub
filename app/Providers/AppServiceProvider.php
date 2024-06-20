<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super-admin" role all permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadministrador');
        });

        // Morph map for polymorphic relations.
        Relation::morphMap([
            'permissions' => 'App\Models\System\Permission',
            'roles'       => 'App\Models\System\Role',
            'users'       => 'App\Models\System\User',

            'addresses' => 'App\Models\Polymorphics\Address',
        ]);
    }
}
