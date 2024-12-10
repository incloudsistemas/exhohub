<?php

namespace App\Services\System;

use App\Models\System\Permission;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class PermissionService extends BaseService
{
    public function __construct(protected Permission $permission)
    {
        //
    }

    public function getQueryAvoidingRoles(Builder $query): Builder
    {
        // Always avoid 1 - Super-admin and 2 - Client/Customer
        $rolesToAvoid = [1, 2]; // 1-

        return $query->whereNotIn('id', $rolesToAvoid)
            ->orderBy('id', 'asc');
    }
}
