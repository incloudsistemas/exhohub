<?php

namespace App\Models\Support;

use App\Casts\DateTimeCast;
use App\Enums\DefaultStatusEnum;
use App\Enums\Support\TicketPriorityEnum;
use App\Enums\Support\TicketStatusEnum;
use App\Models\System\User;
use App\Observers\Support\TicketObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'priority',
        'order',
        'status',
        'opened_at',
        'closed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority'  => TicketPriorityEnum::class,
            'status'    => TicketStatusEnum::class,
            'opened_at' => DateTimeCast::class,
            'closed_at' => DateTimeCast::class,
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function responsibleUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'ticket_user',
            foreignPivotKey: 'ticket_id',
            relatedPivotKey: 'user_id'
        )
            ->wherePivot('role', '=', 1)
            ->withPivot('role');
    }

    public function applicantUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'ticket_user',
            foreignPivotKey: 'ticket_id',
            relatedPivotKey: 'user_id'
        )
            ->wherePivot('role', '=', 2)
            ->withPivot('role');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'ticket_user',
            foreignPivotKey: 'ticket_id',
            relatedPivotKey: 'user_id'
        )
            ->withPivot('role');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            related: TicketCategory::class,
            table: 'ticket_ticket_category',
            foreignPivotKey: 'ticket_id',
            relatedPivotKey: 'category_id'
        );
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Department::class,
            table: 'support_department_ticket',
            foreignPivotKey: 'ticket_id',
            relatedPivotKey: 'department_id'
        );
    }

    // public function registerMediaConversions(Media $media = null): void
    // {
    //     $this->addMediaConversion('thumb')
    //         ->fit(Fit::Crop, 150, 150)
    //         ->nonQueued();
    // }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TicketObserver::class);
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
