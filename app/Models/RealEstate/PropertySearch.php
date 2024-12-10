<?php

namespace App\Models\RealEstate;

use App\Casts\FloatCast;
use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertySearch extends Model
{
    use HasFactory, ClearsResponseCache;

    protected $table = 'real_estate_property_searches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_ip',
        'role',
        'types',
        'code',
        'location',
        'enterprise_role',
        'min_price',
        'max_price',
        'min_useful_area',
        'max_useful_area',
        'min_total_area',
        'max_total_area',
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
        'types'           => 'array',
        'enterprise_role' => EnterpriseRoleEnum::class,
        'min_price'       => FloatCast::class,
        'max_price'       => FloatCast::class,
        'min_useful_area' => FloatCast::class,
        'max_useful_area' => FloatCast::class,
        'min_total_area'  => FloatCast::class,
        'max_total_area'  => FloatCast::class,
    ];

    /**
     * EVENT LISTENERS.
     *
     */

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

    public function getDisplayEnterpriseRoleAttribute(): ?string
    {
        return $this->enterprise_role?->getLabel();
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

        if ($this->min_useful_area == $this->max_useful_area) {
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

        if ($this->min_total_area == $this->max_total_area) {
            return number_format($this->min_total_area, 2, ',', '.');
        }

        return number_format($this->min_total_area, 2, ',', '.') . ' - ' . number_format($this->max_total_area, 2, ',', '.');
    }
}
