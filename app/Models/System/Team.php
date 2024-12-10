<?php

namespace App\Models\System;

use App\Enums\DefaultStatusEnum;
use App\Models\Crm\Queues\Queue;
use App\Observers\System\TeamObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Team extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agency_id',
        'name',
        'slug',
        'complement',
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

    public function queues(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Queue::class,
            table: 'crm_queue_team',
            foreignPivotKey: 'team_id',
            relatedPivotKey: 'queue_id'
        );
    }

    public function realtors(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->withPivot('role')
            ->wherePivot('role', '3'); // 3 - realtors
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->withPivot('role')
            ->wherePivot('role', '2'); // 2 - managers
    }

    public function directors(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->withPivot('role')
            ->wherePivot('role', '1'); // 1 - directors
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->withPivot('role');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(related: Agency::class);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
    }

    /**
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TeamObserver::class);
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

    public function getDisplayNameWithAgencyAttribute(): string
    {
        $display = $this->name;

        if (isset($this->agency)) {
            $display .= " ({$this->agency->name})";
        }

        return $display;
    }
}
