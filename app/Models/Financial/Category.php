<?php

namespace App\Models\Financial;

use App\Enums\DefaultStatusEnum;
use App\Enums\Financial\CategoryRoleEnum;
use App\Observers\Financial\CategoryObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'financial_categories';

    protected $fillable = [
        'category_id',
        'role',
        'name',
        'slug',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role'   => CategoryRoleEnum::class,
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function financialTransactions(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Transaction::class,
            table: 'financial_category_financial_transaction',
            foreignPivotKey: 'category_id',
            relatedPivotKey: 'transaction_id'
        );
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(related: Self::class, foreignKey: 'category_id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(related: Self::class, foreignKey: 'category_id');
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(CategoryObserver::class);
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
