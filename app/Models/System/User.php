<?php

namespace App\Models\System;

use App\Casts\DateCast;
use App\Enums\ProfileInfos\EducationalLevelEnum;
use App\Enums\ProfileInfos\GenderEnum;
use App\Enums\ProfileInfos\MaritalStatusEnum;
use App\Enums\ProfileInfos\UserStatusEnum;
use App\Models\Activities\Activity;
use App\Models\Cms\Post;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Queues\Queue;
use App\Models\Financial\Transaction;
use App\Models\Polymorphics\Address;
use App\Models\RealEstate\Property;
use App\Models\Support\Department;
use App\Models\Support\Ticket;
use App\Models\Support\TicketComment;
use App\Observers\System\UserObserver;
use App\Services\System\RoleService;
use App\Traits\ClearsResponseCache;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    protected $fillable = [
        'name',
        'email',
        'password',
        'additional_emails',
        'phones',
        'cpf',
        'rg',
        'gender',
        'birth_date',
        'marital_status',
        'educational_level',
        'nationality',
        'citizenship',
        'complement',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'additional_emails' => 'array',
            'phones'            => 'array',
            'gender'            => GenderEnum::class,
            'birth_date'        => DateCast::class,
            'marital_status'    => MaritalStatusEnum::class,
            'educational_level' => EducationalLevelEnum::class,
            'status'            => UserStatusEnum::class,
        ];
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(related: Transaction::class, foreignKey: 'user_id');
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(related: TicketComment::class, foreignKey: 'user_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Ticket::class,
            table: 'ticket_user',
            foreignPivotKey: 'ticket_id',
            relatedPivotKey: 'user_id'
        )
            ->withPivot('role');
    }

    public function ownTickets(): HasMany
    {
        return $this->hasMany(related: Ticket::class, foreignKey: 'user_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Department::class,
            table: 'support_department_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'department_id'
        );
    }

    public function queues(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Queue::class,
            table: 'crm_queue_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'queue_id'
        );
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Activity::class,
            table: 'activity_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'activity_id'
        );
    }

    public function ownActivities(): HasMany
    {
        return $this->hasMany(related: Activity::class, foreignKey: 'user_id');
    }

    public function business()
    {
        return $this->belongsToMany(
            related: Business::class,
            table: 'crm_business_user',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'business_id'
        )
            ->withPivot('business_at');
    }

    public function ownBusiness(): HasMany
    {
        return $this->hasMany(related: Business::class, foreignKey: 'user_id');
    }

    public function cmsPosts(): HasMany
    {
        return $this->hasMany(related: Post::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(related: Property::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(related: Contact::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(related: Team::class)
            ->withPivot('role');
    }

    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(related: Agency::class)
            ->withPivot('role');
    }

    public function userCreciStages(): HasMany
    {
        return $this->hasMany(related: UserCreciStage::class);
    }

    public function address(): MorphOne
    {
        return $this->morphOne(related: Address::class, name: 'addressable');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ((int) $this->status->value === 0) {
            // auth()->logout();
            return false;
        }

        // if ((int) $this->status->value === 2) {
        //     // auth()->logout();
        //     return false;
        // }

        return true;
    }

    /**
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(UserObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByAuthUserRoles(Builder $query, User $user): Builder
    {
        $rolesToAvoid = RoleService::getArrayOfRolesToAvoidByAuthUserRoles(user: $user);

        return $query->whereHas('roles', function (Builder $query) use ($rolesToAvoid): Builder {
            return $query->whereNotIn('id', $rolesToAvoid);
        });
    }

    public function scopeWhereHasRolesAvoidingClients(Builder $query): Builder
    {
        $rolesToAvoid = [2]; // Client/Customer

        return $query->whereHas('roles', function (Builder $query) use ($rolesToAvoid): Builder {
            return $query->whereNotIn('id', $rolesToAvoid);
        });
    }

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereHasRolesAvoidingClients()
            ->whereIn('status', $statuses);
    }

    public function scopeBySuperAndAdmin(Builder $query, array $statuses = [1]): Builder
    {
        return $query->where('status', 1)
            ->whereHas('roles', function (Builder $query): Builder {
                return $query->whereIn('id', [1, 3]); // 1 - Superadmin, 3 - Admin
            });
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

        if (isset($this->additional_emails[0])) {
            foreach ($this->additional_emails as $email) {
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

    public function getDisplayBirthDateAttribute(): ?string
    {
        return isset($this->birth_date)
            ? ConvertEnToPtBrDate(date: $this->birth_date)
            : null;
    }

    public function getActiveCreciStageAttribute()
    {
        return $this->userCreciStages()
            ->latest()
            ->first();
    }

    public function getDisplayActiveCreciStageAttribute(): ?string
    {
        return $this->active_creci_stage?->creciControlStage->name;
    }

    public function getRequiredAttachmentsAttribute()
    {
        return $this->media()
            ->whereIn('collection_name', [
                'birth',
                'identity',
                'address',
                'reservist',
                'civil_negative',
                'criminal_negative',
                'high_school',
            ])
            ->get()
            ->sortBy('order_column');
    }

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('attachments')
            ->sortBy('order_column');
    }
}
