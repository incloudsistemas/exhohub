<?php

namespace App\Models\Financial;

use App\Casts\DateCast;
use App\Casts\FloatCast;
use App\Enums\DefaultStatusEnum;
use App\Enums\Financial\BankAccountRoleEnum;
use App\Enums\Financial\BankAccountTypeEnum;
use App\Models\System\Agency;
use App\Observers\Financial\BankAccountObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BankAccount extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    protected $table = 'financial_bank_accounts';

    protected $fillable = [
        'agency_id',
        'bank_institution_id',
        'role',
        'type_person',
        'name',
        'is_main',
        'balance_date',
        'balance',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role'         => BankAccountRoleEnum::class,
            'type_person'  => BankAccountTypeEnum::class,
            'is_main'      => 'boolean',
            'balance_date' => DateCast::class,
            'balance'      => FloatCast::class,
            'status'       => DefaultStatusEnum::class,
        ];
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(related: Transaction::class, foreignKey: 'bank_account_id');
    }

    public function bankInstitution(): BelongsTo
    {
        return $this->belongsTo(related: BankInstitution::class, foreignKey: 'bank_institution_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(related: Agency::class, foreignKey: 'agency_id');
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

    protected static function boot()
    {
        parent::boot();
        self::observe(BankAccountObserver::class);
    }

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

    public function getDisplayBalanceAttribute(): ?string
    {
        return $this->balance ? number_format($this->balance, 2, ',', '.') : null;
    }
}
