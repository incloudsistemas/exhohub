<?php

namespace App\Models\Polymorphics;

use App\Enums\ProfileInfos\State;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'name',
        'is_main',
        'zipcode',
        'state',
        'uf',
        'city',
        'country',
        'district',
        'address_line',
        'number',
        'complement',
        'custom_street',
        'custom_block',
        'custom_lot',
        'reference',
        'gmap_coordinates',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_main' => 'boolean',
    ];

    /**
     * Get all of the owning addressable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * SCOPES.
     *
     */

    /**
     * MUTATORS.
     *
     */

    protected function state(): Attribute
    {
        return Attribute::make(
            set: fn () => isset($this->uf) ? State::tryFrom($this->uf)?->label() : null,
        );
    }

    /**
     * CUSTOMS.
     *
     */

    protected function getDisplayUfAttribute(): ?string
    {
        return $this->uf ? State::tryFrom($this->uf)?->label() : null;
    }

    public function getDisplayFullAddressAttribute(): ?string
    {
        $components = [];

        if (!empty(trim($this->address_line))) {
            $components[] = trim($this->address_line);
        }

        if (!empty(trim($this->number))) {
            $components[] = trim($this->number);
        }

        if (!empty(trim($this->complement))) {
            $components[] = trim($this->complement);
        }

        if (!empty(trim($this->district))) {
            $components[] = trim($this->district);
        }

        $components[] = $this->city . '-' . $this->uf;
        $components[] = $this->zipcode;

        return implode(', ', $components);
    }

    public function getDisplayShortAddressAttribute(): ?string
    {
        $components = [];

        if (!empty(trim($this->address_line))) {
            $components[] = trim($this->address_line);
        }

        if (!empty(trim($this->number))) {
            $components[] = trim($this->number);
        }

        if (!empty(trim($this->district))) {
            $components[] = trim($this->district);
        }

        return implode(', ', $components);
    }

    public function getDisplayDistrictCityUfAttribute(): ?string
    {
        $components = [];

        if (!empty(trim($this->district))) {
            $components[] = trim($this->district);
        }

        $components[] = $this->city . '-' . $this->uf;

        return implode(', ', $components);
    }

    public function getDisplayStreetCityUfAttribute(): ?string
    {
        $components = [];

        if (!empty(trim($this->address_line))) {
            $components[] = trim($this->address_line);
        }

        $components[] = $this->city . '-' . $this->uf;

        return implode(', ', $components);
    }

    public function getDisplayCityUfAttribute(): ?string
    {
        $components[] = $this->city . '-' . $this->uf;

        return implode(', ', $components);
    }
}
