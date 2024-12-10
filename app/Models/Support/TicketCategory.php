<?php

namespace App\Models\Support;

use App\Enums\DefaultStatusEnum;
use App\Observers\Support\TicketCategoryObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketCategory extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'order',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Ticket::class,
            table: 'ticket_ticket_category',
            foreignPivotKey: 'category_id',
            relatedPivotKey: 'ticket_id'
        );
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Department::class,
            table: 'support_department_ticket_category',
            foreignPivotKey: 'category_id',
            relatedPivotKey: 'department_id'
        );
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TicketCategoryObserver::class);
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
