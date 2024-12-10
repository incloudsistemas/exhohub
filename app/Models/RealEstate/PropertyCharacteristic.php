<?php

namespace App\Models\RealEstate;

use App\Enums\DefaultStatusEnum;
use App\Enums\RealEstate\PropertyCharacteristicRoleEnum;
use App\Observers\RealEstate\PropertyCharacteristicObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyCharacteristic extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'real_estate_property_characteristics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
        'name',
        'slug',
        'canal_pro_vrsync',
        'status'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role'   => PropertyCharacteristicRoleEnum::class,
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function properties()
    {
        return $this->belongsToMany(
            related: Property::class,
            table: 'real_estate_property_real_estate_property_characteristic',
            foreignPivotKey: 'characteristic_id',
            relatedPivotKey: 'property_id'
        );
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(PropertyCharacteristicObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeByRoles(Builder $query, array $roles): Builder
    {
        return $query->whereIn('role', $roles);
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
