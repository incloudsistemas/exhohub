<?php

namespace App\Models\Financial;

use App\Enums\DefaultStatusEnum;
use App\Observers\Financial\BankInstitutionObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankInstitution extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'financial_bank_institutions';

    protected $fillable = [
        'code',
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(related: BankAccount::class, foreignKey: 'bank_institution_id');
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(BankInstitutionObserver::class);
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
}
