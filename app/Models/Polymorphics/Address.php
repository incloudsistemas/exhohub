<?php

namespace App\Models\Polymorphics;

use App\Enums\ProfileInfos\UfEnum;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory, ClearsResponseCache;

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
        'is_main'          => 'boolean',
        'uf'               => UfEnum::class,
        'gmap_coordinates' => 'array'
    ];

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

    /**
     * CUSTOMS.
     *
     */

    public function getDisplayUfAttribute(): ?string
    {
        return $this->uf?->getLabel();
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

        if (!empty(trim($this->city)) && (isset($this->uf) && !empty(trim($this->uf->name)))) {
            $components[] = $this->city . '-' . $this->uf->name;
        } else if (!empty(trim($this->city)) && !isset($this->uf)) {
            $components[] = $this->city;
        }

        if (!empty(trim($this->zipcode))) {
            $components[] = $this->zipcode;
        }

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

        if (!empty(trim($this->city)) && (isset($this->uf) && !empty(trim($this->uf->name)))) {
            $components[] = $this->city . '-' . $this->uf->name;
        } else if (!empty(trim($this->city)) && !isset($this->uf)) {
            $components[] = $this->city;
        }

        return implode(', ', $components);
    }

    public function getDisplayStreetCityUfAttribute(): ?string
    {
        $components = [];

        if (!empty(trim($this->address_line))) {
            $components[] = trim($this->address_line);
        }

        if (!empty(trim($this->city)) && (isset($this->uf) && !empty(trim($this->uf->name)))) {
            $components[] = $this->city . '-' . $this->uf->name;
        } else if (!empty(trim($this->city)) && !isset($this->uf)) {
            $components[] = $this->city;
        }

        return implode(', ', $components);
    }

    public function getDisplayCityUfAttribute(): ?string
    {
        $components = [];

        if (!empty(trim($this->city)) && (isset($this->uf) && !empty(trim($this->uf->name)))) {
            $components[] = $this->city . '-' . $this->uf->name;
        } else if (!empty(trim($this->city)) && !isset($this->uf)) {
            $components[] = $this->city;
        }

        return implode(', ', $components);
    }
}
