<?php

namespace App\Models\Crm\Contacts;

use App\Enums\DefaultStatusEnum;
use App\Models\Activities\Activity;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Business\PropertiesInterestProfile;
use App\Models\Crm\Source;
use App\Models\RealEstate\Property;
use App\Models\System\User;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Contact extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    protected $table = 'crm_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contactable_type',
        'contactable_id',
        'user_id',
        'source_id',
        'name',
        'email',
        'additional_emails',
        'phones',
        'complement',
        'status',
        'custom',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'additional_emails' => 'array',
            'phones'            => 'array',
            'status'            => DefaultStatusEnum::class,
            'custom'            => 'array',
        ];
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Activity::class,
            table: 'activity_crm_contact',
            foreignPivotKey: 'contact_id',
            relatedPivotKey: 'activity_id'
        );
    }

    public function propertiesInterestProfiles(): HasMany
    {
        return $this->hasMany(related: PropertiesInterestProfile::class, foreignKey: 'contact_id');
    }

    public function business(): HasMany
    {
        return $this->hasMany(related: Business::class, foreignKey: 'contact_id');
    }

    public function properties()
    {
        return $this->belongsToMany(
            related: Property::class,
            table: 'crm_contact_real_estate_property',
            foreignPivotKey: 'contact_id',
            relatedPivotKey: 'property_id'
        )
            ->withPivot('role');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(related: Source::class, foreignKey: 'source_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            related: Role::class,
            table: 'crm_contact_crm_contact_role',
            foreignPivotKey: 'contact_id',
            relatedPivotKey: 'role_id'
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function contactable(): MorphTo
    {
        return $this->morphTo();
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

    public function getDisplayAdditionalEmailsAttribute(): ?array
    {
        $additionalEmails = [];

        if (isset($this->emails[1]['email'])) {
            foreach (array_slice($this->emails, 1) as $email) {
                $additionalEmail = $email['email'];

                if (!empty($email['name'])) {
                    $additionalEmail .= " ({$email['name']})";
                }

                $additionalEmails[] = $additionalEmail;
            }
        }

        return !empty($additionalEmails) ? $additionalEmails : null;
    }

    public function getDisplayMainPhoneAttribute(): ?string
    {
        return $this->phones[0]['number'] ?? null;
    }

    public function getDisplayMainPhoneWithNameAttribute(): ?string
    {
        if (isset($this->phones[0]['number'])) {
            $mainPhone = $this->phones[0]['number'];
            $phoneName = $this->phones[0]['name'] ?? null;

            if (!empty($phoneName)) {
                $mainPhone .= " ({$phoneName})";
            }

            return $mainPhone;
        }

        return null;
    }

    public function getDisplayAdditionalPhonesAttribute(): ?array
    {
        $additionalPhones = [];

        if (isset($this->phones[1]['number'])) {
            foreach (array_slice($this->phones, 1) as $phone) {
                $additionalPhone = $phone['number'];

                if (!empty($phone['name'])) {
                    $additionalPhone .= " ({$phone['name']})";
                }

                $additionalPhones[] = $additionalPhone;
            }
        }

        return !empty($additionalPhones) ? $additionalPhones : null;
    }
}
