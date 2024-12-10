<?php

namespace App\Models\Crm\Business;

use App\Casts\DateTimeCast;
use App\Casts\FloatCast;
use App\Enums\Crm\Business\PriorityEnum;
use App\Models\Activities\Activity;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Funnels\FunnelStage;
use App\Models\Crm\Funnels\FunnelSubstage;
use App\Models\Crm\Source;
use App\Models\RealEstate\Property;
use App\Models\System\User;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Business extends Model implements HasMedia, Sortable
{
    use HasFactory, InteractsWithMedia, SortableTrait, SoftDeletes, ClearsResponseCache;

    protected $table = 'crm_business';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'contact_id',
        'funnel_id',
        'funnel_stage_id',
        'funnel_substage_id',
        'source_id',
        'name',
        'description',
        'price',
        'commission_percentage',
        'commission_price',
        'priority',
        'order',
        'business_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price'                 => FloatCast::class,
        'commission_percentage' => FloatCast::class,
        'commission_price'      => FloatCast::class,
        'priority'              => PriorityEnum::class,
        'business_at'           => DateTimeCast::class,
    ];

    public $sortable = [
        'order_column_name'  => 'order',
        'sort_when_creating' => false,
    ];

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Activity::class,
            table: 'activity_crm_business',
            foreignPivotKey: 'business_id',
            relatedPivotKey: 'activity_id'
        );
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Property::class,
            table: 'crm_business_real_estate_property',
            foreignPivotKey: 'business_id',
            relatedPivotKey: 'property_id'
        );
    }

    public function propertiesInterestProfiles(): HasMany
    {
        return $this->hasMany(related: PropertiesInterestProfile::class, foreignKey: 'business_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(related: Source::class, foreignKey: 'source_id');
    }

    public function businessFunnelStages(): HasMany
    {
        return $this->hasMany(related: BusinessFunnelStage::class, foreignKey: 'business_id');
    }

    public function substage(): BelongsTo
    {
        return $this->belongsTo(related: FunnelSubstage::class, foreignKey: 'funnel_substage_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(related: FunnelStage::class, foreignKey: 'funnel_stage_id');
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(related: Funnel::class, foreignKey: 'funnel_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(related: Contact::class, foreignKey: 'contact_id');
    }

    public function users()
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'crm_business_user',
            foreignPivotKey: 'business_id',
            relatedPivotKey: 'user_id'
        )
            ->withPivot('business_at');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
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

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */

    public function currentBusinessFunnelStage()
    {
        return $this->businessFunnelStages()
            ->orderBy('business_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function currentFunnel()
    {
        return $this->currentBusinessFunnelStage()->funnel;
    }

    public function currentStage()
    {
        return $this->currentBusinessFunnelStage()->stage;
    }

    public function currentSubstage()
    {
        return $this->currentBusinessFunnelStage()->substage;
    }

    public function getDisplayCurrentFunnelAttribute(): ?string
    {
        return $this->currentFunnel()->name;
    }

    public function getDisplayCurrentStageAttribute(): ?string
    {
        return $this->currentStage()->name;
    }

    public function getDisplayCurrentSubstageAttribute(): ?string
    {
        return $this->currentSubstage()?->name;
    }

    public function currentUser()
    {
        return $this->users()
            ->orderBy('business_at', 'desc')
            ->first();
    }

    public function getDisplayCurrentUserAttribute(): ?string
    {
        return $this->currentUser()?->name;
    }

    public function getDisplayPriceAttribute(): ?string
    {
        return $this->price ? number_format($this->price, 2, ',', '.') : null;
    }

    public function getDisplayCommissionPercentageAttribute(): ?string
    {
        return $this->commission_percentage ? number_format($this->commission_percentage, 2, ',', '.') : null;
    }

    public function getDisplayCommissionPriceAttribute(): ?string
    {
        return $this->commission_price ? number_format($this->commission_price, 2, ',', '.') : null;
    }
}
