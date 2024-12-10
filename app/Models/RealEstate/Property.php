<?php

namespace App\Models\RealEstate;

use App\Casts\DateTimeCast;
use App\Casts\FloatCast;
use App\Enums\RealEstate\PropertyStandardEnum;
use App\Enums\RealEstate\PropertyStatusEnum;
use App\Enums\RealEstate\PropertyUsageEnum;
use App\Models\Activities\WebConversion;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Queues\Queue;
use App\Models\Polymorphics\Address;
use App\Models\System\User;
use App\Traits\ClearsResponseCache;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Property extends Model implements HasMedia
{
    use HasFactory, Sluggable, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    protected $table = 'real_estate_properties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'propertable_type',
        'propertable_id',
        'type_id',
        'subtype_id',
        'user_id',
        'usage',
        'code',
        'title',
        'slug',
        'subtitle',
        'excerpt',
        'body',
        'owner_notes',
        'url',
        'embed_videos',
        'show_address',
        'show_watermark',
        'standard',
        'tax_price',
        'condo_price',
        'floors',
        'units_per_floor',
        'towers',
        'construct_year',
        'publish_on',
        'publish_on_data',
        'tags',
        'order',
        'featured',
        'comment',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'publish_at',
        'expiration_at',
        'status',
        'custom',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'usage'           => PropertyUsageEnum::class,
        'embed_videos'    => 'array',
        'standard'        => PropertyStandardEnum::class,
        'tax_price'       => FloatCast::class,
        'condo_price'     => FloatCast::class,
        'publish_on'      => 'array',
        'publish_on_data' => 'array',
        'tags'            => 'array',
        'featured'        => 'boolean',
        'comment'         => 'boolean',
        'meta_keywords'   => 'array',
        'publish_at'      => DateTimeCast::class,
        'expiration_at'   => DateTimeCast::class,
        'status'          => PropertyStatusEnum::class,
        'custom'          => 'array',
    ];

    public function activityWebConversion(): MorphMany
    {
        return $this->morphMany(related: WebConversion::class, name: 'conversionnable');
    }

    public function business(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Business::class,
            table: 'crm_business_real_estate_property',
            foreignPivotKey: 'property_id',
            relatedPivotKey: 'business_id'
        );
    }

    public function queues(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Queue::class,
            table: 'crm_queue_real_estate_property',
            foreignPivotKey: 'property_id',
            relatedPivotKey: 'queue_id'
        );
    }

    public function contacts()
    {
        return $this->belongsToMany(
            related: Contact::class,
            table: 'crm_contact_real_estate_property',
            foreignPivotKey: 'property_id',
            relatedPivotKey: 'contact_id'
        )
            ->withPivot('role');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function characteristics()
    {
        return $this->belongsToMany(
            related: PropertyCharacteristic::class,
            table: 'real_estate_property_real_estate_property_characteristic',
            foreignPivotKey: 'property_id',
            relatedPivotKey: 'characteristic_id'
        );
    }

    public function subtype(): BelongsTo
    {
        return $this->belongsTo(related: PropertySubtype::class, foreignKey: 'subtype_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(related: PropertyType::class, foreignKey: 'type_id');
    }

    public function address(): MorphOne
    {
        return $this->morphOne(related: Address::class, name: 'addressable');
    }

    public function propertable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source'         => 'title',
                'unique'         => true,
                'includeTrashed' => true,
            ]
        ];
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
    }

    /**
     * EVENT LISTENERS.
     *
     */

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

    public function getDisplayUsageAttribute(): ?string
    {
        return $this->usage?->getLabel();
    }

    public function getDisplayStandardAttribute(): ?string
    {
        return $this->standard?->getLabel();
    }

    public function getDisplayCondoPriceAttribute(): ?string
    {
        return $this->condo_price
            ? number_format($this->condo_price, 2, ',', '.')
            : null;
    }

    public function getDisplayTaxPriceAttribute(): ?string
    {
        return $this->tax_price
            ? number_format($this->tax_price, 2, ',', '.')
            : null;
    }

    public function getHasWatermarkAttribute(): bool
    {
        return match ((int) $this->show_watermark) {
            0       => false,
            default => true,
        };
    }

    public function getDisplayWatermarkPositionAttribute(): ?string
    {
        // 0 - 'Não mostrar', 1 - 'Centro', 2 - 'Esquerda', 3 - 'Direita',
        // 4 - 'Superior ao centro', 5 - 'Superior a esquerda', 6 - 'Superior a direita',
        // 7 - 'Inferior ao centro', 8 - 'Inferior a esquerda', 9 - 'Inferior a direita'.
        return match ((int) $this->show_watermark) {
            default => 'center',
            2       => 'left',
            3       => 'right',
            4       => 'top-center',
            5       => 'top-left',
            6       => 'top-right',
            7       => 'bottom-center',
            8       => 'bottom-left',
            9       => 'bottom-right',
        };
    }

    public function getDifferencesCharacteristicsAttribute()
    {
        return $this->characteristics()
            ->where('role', 1)
            ->pluck('name')
            ->toArray();
    }

    public function getLeisureCharacteristicsAttribute()
    {
        return $this->characteristics()
            ->where('role', 2)
            ->pluck('name')
            ->toArray();
    }

    public function getSecurityCharacteristicsAttribute()
    {
        return $this->characteristics()
            ->where('role', 3)
            ->pluck('name')
            ->toArray();
    }

    public function getInfrastructureCharacteristicsAttribute()
    {
        return $this->characteristics()
            ->where('role', 4)
            ->pluck('name')
            ->toArray();
    }

    /**
     * WEBSITE EXCLUSIVE.
     *
     */

    protected function baseWebQuery(
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->newQuery()
            ->with([
                'propertable',
                'propertable.media',
                'address',
                'type:id,name,canal_pro_vrsync,slug',
                'subtype:id,name,slug',
                'characteristics:id,role,name,canal_pro_vrsync,slug',
                'owner:id,name,email',
                'contacts:id,name,email',
            ])
            ->whereHas('propertable')
            ->whereIn('status', $statuses)
            ->where('publish_at', '<=', now())
            ->where(
                fn(Builder $query): Builder =>
                $query->where('expiration_at', '>', now())
                    ->orWhereNull('expiration_at')
            )
            ->orderBy($orderBy, $direction)
            ->orderBy('publish_at', $publishAtDirection);
    }

    public function getWeb(
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        );
    }

    public function getWebFeatured(
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('featured', 1);
    }

    public function getWebIndividualsByRoles(
        array $roles = [1, 2, 3],
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereHasMorph(
                'propertable',
                [Individual::class],
                function ($query) use ($roles): Builder {
                    return $query->whereIn('role', $roles);
                }
            );
    }

    public function getWebFeaturedIndividualsByRoles(
        array $roles = [1, 2, 3],
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWebIndividualsByRoles(
            roles: $roles,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('featured', 1);
    }

    public function getWebEnterprisesByRoles(
        array $roles = [1, 2, 3],
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereHasMorph(
                'propertable',
                [Enterprise::class],
                function ($query) use ($roles): Builder {
                    return $query->whereIn('role', $roles);
                }
            );
    }

    public function getWebFeaturedEnterprisesByRoles(
        array $roles = [1, 2, 3],
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWebEnterprisesByRoles(
            roles: $roles,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('featured', 1);
    }

    public function findWebBySlug(
        string $slug,
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('slug', $slug);
    }

    public function getWebIndividualsByRelatedUsageType(
        array $roles,
        int $usage,
        int $type,
        array $statuses = [1],
        ?int $idToAvoid = null,
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        $query = $this->getWebIndividualsByRoles(
            roles: $roles,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('usage', $usage)
            ->where('type_id', $type);

        if ($idToAvoid) {
            $query->where('id', '<>', $idToAvoid);
        }

        return $query;
    }

    public function getWebEnterprisesByRelatedUsageType(
        array $roles = [1, 2, 3],
        int $usage,
        int $type,
        array $statuses = [1],
        ?int $idToAvoid = null,
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        $query = $this->getWebEnterprisesByRoles(
            roles: $roles,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('usage', $usage)
            ->where('type_id', $type);

        if ($idToAvoid) {
            $query->where('id', '<>', $idToAvoid);
        }

        return $query;
    }

    public function getDisplayWebAddressAttribute(): ?string
    {
        // 1 - 'Completo', 2 - 'Somente bairro, cidade e uf',
        // 3 - 'Somente rua, cidade e uf', 4 - 'Somente cidade e uf'.
        return match ($this->show_address) {
            // Completo
            '1' => $this->address->display_full_address,
            // Somente bairro, cidade e uf
            '2' => $this->address->display_district_city_uf,
            // Somente rua, cidade e uf
            '3' => $this->address->display_street_city_uf,
            // 4 - 'Somente cidade e uf'
            '4' => $this->address->display_city_uf,
            default => null,
        };
    }

    public function getDisplayWebTaxesAttribute(): ?string
    {
        if ($this->display_condo_price && $this->display_tax_price) {
            return 'Cond. R$ ' . $this->display_condo_price . ' // IPTU R$ ' . $this->display_tax_price;
        } elseif ($this->display_condo_price) {
            return 'Cond. R$ ' . $this->display_condo_price;
        } elseif ($this->display_tax_price) {
            return 'IPTU R$ ' . $this->display_tax_price;
        } else {
            return null;
        }
    }
}
