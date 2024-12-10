<?php

namespace App\Models\RealEstate;

use App\Enums\DefaultStatusEnum;
use App\Observers\RealEstate\PropertySubtypeObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertySubtype extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'real_estate_property_subtypes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'order',
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
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function properties(): HasMany
    {
        return $this->hasMany(related: Property::class, foreignKey: 'subtype_id');
    }

    public function types()
    {
        return $this->belongsToMany(
            related: PropertyType::class,
            table: 'real_estate_property_subtype_real_estate_property_type',
            foreignPivotKey: 'subtype_id',
            relatedPivotKey: 'type_id'
        );
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(PropertySubtypeObserver::class);
    }

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
