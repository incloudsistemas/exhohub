<?php

namespace App\Models\RealEstate;

use App\Casts\FloatCast;
use App\Enums\RealEstate\IndividualRoleEnum;
use App\Enums\RealEstate\RentPeriodEnum;
use App\Observers\RealEstate\IndividualObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\RealEstate\Propertable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Individual extends Model implements HasMedia
{
    use Propertable, ClearsResponseCache;

    protected $table = 'real_estate_individuals';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
        'sale_price',
        'rent_price',
        'rent_period',
        'rent_warranties',
        'useful_area',
        'total_area',
        'bedroom',
        'suite',
        'bathroom',
        'garage',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role'            => IndividualRoleEnum::class,
        'sale_price'      => FloatCast::class,
        'rent_price'      => FloatCast::class,
        'rent_period'     => RentPeriodEnum::class,
        'rent_warranties' => 'array',
        'useful_area'     => FloatCast::class,
        'total_area'      => FloatCast::class,
    ];

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(IndividualObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */

    public function getDisplayRoleAttribute(): string
    {
        return $this->role->getLabel();
    }

    public function getDisplayRoleSlugAttribute(): string
    {
        return $this->role->getSlug();
    }

    public function getDisplaySalePriceAttribute(): ?string
    {
        return $this->sale_price
            ? number_format($this->sale_price, 2, ',', '.')
            : null;
    }

    public function getDisplayRentPriceAttribute(): ?string
    {
        return $this->rent_price
            ? number_format($this->rent_price, 2, ',', '.')
            : null;
    }

    public function getDisplayRentPeriodAttribute(): ?string
    {
        return $this->rent_period?->getLabel();
    }

    public function getDisplayUsefulAreaAttribute(): ?string
    {
        return $this->useful_area
            ? number_format($this->useful_area, 2, ',', '.')
            : null;
    }

    public function getDisplayTotalAreaAttribute(): ?string
    {
        return $this->total_area
            ? number_format($this->total_area, 2, ',', '.')
            : null;
    }
}
