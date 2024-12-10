<?php

namespace App\Models\System;

use App\Enums\DefaultStatusEnum;
use App\Enums\System\CreciControlStageRoleEnum;
use App\Observers\System\CreciControlStageObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreciControlStage extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'name',
        'slug',
        'complement',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role'   => CreciControlStageRoleEnum::class,
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function userCreciStages(): HasMany
    {
        return $this->hasMany(related: UserCreciStage::class);
    }

    /**
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(CreciControlStageObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByRoles(Builder $query, array $roles = [1, 2, 3]): Builder
    {
        return $query->whereIn('roles', $roles);
    }

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */
}
