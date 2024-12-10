<?php

namespace App\Models\Crm\Business;

use App\Casts\FloatCast;
use App\Enums\Crm\Business\PropertiesInterestProfileRoleEnum;
use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Enums\RealEstate\PropertyUsageEnum;
use App\Models\Crm\Contacts\Contact;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesInterestProfile extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'crm_business_properties_interest_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id',
        'contact_id',
        'usage',
        'role',
        'types',
        'enterprise_role',
        'bedroom',
        'suite',
        'bathroom',
        'garage',
        'min_useful_area',
        'max_useful_area',
        'min_total_area',
        'max_total_area',
        'min_price',
        'max_price',
        'characteristics',
        'uf',
        'city',
        'districts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'usage'           => PropertyUsageEnum::class,
        'role'            => PropertiesInterestProfileRoleEnum::class,
        'types'           => 'array',
        'enterprise_role' => EnterpriseRoleEnum::class,
        'min_useful_area' => FloatCast::class,
        'max_useful_area' => FloatCast::class,
        'min_total_area'  => FloatCast::class,
        'max_total_area'  => FloatCast::class,
        'min_price'       => FloatCast::class,
        'max_price'       => FloatCast::class,
        'characteristics' => 'array',
        'districts'       => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(related: Contact::class, foreignKey: 'contact_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(related: Business::class, foreignKey: 'business_id');
    }

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
}
