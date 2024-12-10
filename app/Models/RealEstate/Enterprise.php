<?php

namespace App\Models\RealEstate;

use App\Casts\FloatCast;
use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Observers\RealEstate\EnterpriseObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\RealEstate\Propertable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Enterprise extends Model implements HasMedia
{
    use Propertable, ClearsResponseCache;

    protected $table = 'real_estate_enterprises';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
        'min_price',
        'max_price',
        'min_useful_area',
        'max_useful_area',
        'min_total_area',
        'max_total_area',
        'min_bedroom',
        'max_bedroom',
        'min_suite',
        'max_suite',
        'min_bathroom',
        'max_bathroom',
        'min_garage',
        'max_garage',
        'construction_follow_up'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role'                   => EnterpriseRoleEnum::class,
        'min_price'              => FloatCast::class,
        'max_price'              => FloatCast::class,
        'min_useful_area'        => FloatCast::class,
        'max_useful_area'        => FloatCast::class,
        'min_total_area'         => FloatCast::class,
        'max_total_area'         => FloatCast::class,
        'construction_follow_up' => 'array',
    ];

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(EnterpriseObserver::class);
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

    public function getDisplayMinPriceAttribute(): ?string
    {
        return $this->min_price
            ? number_format($this->min_price, 2, ',', '.')
            : null;
    }

    public function getDisplayMaxPriceAttribute(): ?string
    {
        return $this->max_price
            ? number_format($this->max_price, 2, ',', '.')
            : null;
    }

    public function getDisplayPriceAttribute(): ?string
    {
        $minPrice = $this->min_price
            ? number_format($this->min_price, 2, ',', '.')
            : null;

        $maxPrice = $this->max_price
            ? number_format($this->max_price, 2, ',', '.')
            : null;

        if ($this->min_price == $this->max_price) {
            return $minPrice;
        }

        return $minPrice . ' - ' . $maxPrice;
    }

    public function getDisplayMinUsefulAreaAttribute(): ?string
    {
        return $this->min_useful_area
            ? number_format($this->min_useful_area, 2, ',', '.')
            : null;
    }

    public function getDisplayMaxUsefulAreaAttribute(): ?string
    {
        return $this->max_useful_area
            ? number_format($this->max_useful_area, 2, ',', '.')
            : null;
    }

    public function getDisplayUsefulAreaAttribute(): ?string
    {
        if (!$this->min_useful_area) {
            return null;
        }

        if ($this->min_useful_area === $this->max_useful_area) {
            return number_format($this->min_useful_area, 2, ',', '.');
        }

        return number_format($this->min_useful_area, 2, ',', '.') . ' - ' . number_format($this->max_useful_area, 2, ',', '.');
    }

    public function getDisplayMinTotalAreaAttribute(): ?string
    {
        return $this->min_total_area
            ? number_format($this->min_total_area, 2, ',', '.')
            : null;
    }

    public function getDisplayMaxTotalAreaAttribute(): ?string
    {
        return $this->max_total_area
            ? number_format($this->max_total_area, 2, ',', '.')
            : null;
    }

    public function getDisplayTotalAreaAttribute(): ?string
    {
        if (!$this->min_total_area) {
            return null;
        }

        if ($this->min_total_area === $this->max_total_area) {
            return number_format($this->min_total_area, 2, ',', '.');
        }

        return number_format($this->min_total_area, 2, ',', '.') . ' - ' . number_format($this->max_total_area, 2, ',', '.');
    }

    public function getDisplayBedroomAttribute(): ?string
    {
        if (!$this->min_bedroom) {
            return null;
        }

        if ($this->min_bedroom === $this->max_bedroom) {
            return $this->min_bedroom;
        }

        return $this->min_bedroom . ' - ' . $this->max_bedroom;
    }

    public function getDisplaySuiteAttribute(): ?string
    {
        if (!$this->min_suite) {
            return null;
        }

        if ($this->min_suite === $this->max_suite) {
            return $this->min_suite;
        }

        return $this->min_suite . ' - ' . $this->max_suite;
    }

    public function getDisplayBathroomAttribute(): ?string
    {
        if (!$this->min_bathroom) {
            return null;
        }

        if ($this->min_bathroom === $this->max_bathroom) {
            return $this->min_bathroom;
        }

        return $this->min_bathroom . ' - ' . $this->max_bathroom;
    }

    public function getDisplayGarageAttribute(): ?string
    {
        if (!$this->min_garage) {
            return null;
        }

        if ($this->min_garage === $this->max_garage) {
            return $this->min_garage;
        }

        return $this->min_garage . ' - ' . $this->max_garage;
    }
}
