<?php

namespace App\Models\Crm\Queues;

use App\Enums\Crm\Queues\DistributionSettingsEnum;
use App\Enums\Crm\Queues\PropertiesSettingsEnum;
use App\Enums\Crm\Queues\QueueRoleEnum;
use App\Enums\Crm\Queues\UsersSettingsEnum;
use App\Enums\DefaultStatusEnum;
use App\Models\Crm\Funnels\Funnel;
use App\Models\RealEstate\Property;
use App\Models\System\Agency;
use App\Models\System\Team;
use App\Models\System\User;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Queue extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'crm_queues';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'funnel_id',
        'role',
        'name',
        'description',
        'users_settings',
        'properties_settings',
        'distribution_settings',
        'distribution_index',
        'account_id',
        'campaign_id',
        'order',
        'status',
        'custom_settings',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role'                  => QueueRoleEnum::class,
            'users_settings'        => UsersSettingsEnum::class,
            'properties_settings'   => PropertiesSettingsEnum::class,
            'distribution_settings' => DistributionSettingsEnum::class,
            'status'                => DefaultStatusEnum::class,
            'custom_settings'       => 'array',
        ];
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(related: Funnel::class, foreignKey: 'funnel_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Team::class,
            table: 'crm_queue_team',
            foreignPivotKey: 'queue_id',
            relatedPivotKey: 'team_id'
        );
    }

    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Agency::class,
            table: 'agency_crm_queue',
            foreignPivotKey: 'queue_id',
            relatedPivotKey: 'agency_id'
        );
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Property::class,
            table: 'crm_queue_real_estate_property',
            foreignPivotKey: 'queue_id',
            relatedPivotKey: 'property_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'crm_queue_user',
            foreignPivotKey: 'queue_id',
            relatedPivotKey: 'user_id'
        );
    }
}
