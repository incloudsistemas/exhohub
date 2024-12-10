<?php

namespace App\Models\RealEstate;

use App\Enums\DefaultStatusEnum;
use App\Enums\RealEstate\PropertyTypeUsageEnum;
use App\Observers\RealEstate\PropertyTypeObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyType extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'real_estate_property_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'usage',
        'name',
        'slug',
        'abbr',
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
            'usage'  => PropertyTypeUsageEnum::class,
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function properties(): HasMany
    {
        return $this->hasMany(related: Property::class, foreignKey: 'type_id');
    }

    public function subtypes()
    {
        return $this->belongsToMany(
            related: PropertySubtype::class,
            table: 'real_estate_property_subtype_real_estate_property_type',
            foreignPivotKey: 'type_id',
            relatedPivotKey: 'subtype_id'
        );
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(PropertyTypeObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByUsages(Builder $query, array $usages = [1]): Builder
    {
        return $query->whereIn('usage', $usages);
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
