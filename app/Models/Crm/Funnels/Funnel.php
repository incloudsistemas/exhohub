<?php

namespace App\Models\Crm\Funnels;

use App\Enums\DefaultStatusEnum;
use App\Models\Activities\FunnelUpdate;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Business\BusinessFunnelStage;
use App\Models\Crm\Queues\Queue;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funnel extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'crm_funnels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'order',
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
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function queues(): HasMany
    {
        return $this->hasMany(related: Queue::class, foreignKey: 'funnel_id');
    }

    public function business(): HasMany
    {
        return $this->hasMany(related: Business::class, foreignKey: 'funnel_id');
    }

    public function businessFunnelStages(): HasMany
    {
        return $this->hasMany(related: BusinessFunnelStage::class, foreignKey: 'funnel_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(related: FunnelStage::class, foreignKey: 'funnel_id');
    }

    /**
     * EVENT LISTENERS.
     *
     */

    /**
     * SCOPES.
     *
     */

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
