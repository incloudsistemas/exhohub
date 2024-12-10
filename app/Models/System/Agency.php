<?php

namespace App\Models\System;

use App\Enums\DefaultStatusEnum;
use App\Models\Crm\Queues\Queue;
use App\Observers\System\AgencyObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Agency extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
            table: 'agency_crm_queue',
            foreignPivotKey: 'agency_id',
            relatedPivotKey: 'queue_id'
        );
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->withPivot('role')
            ->wherePivot('role', '1'); // 1 - partners/default
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->withPivot('role');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(related: Team::class);
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
        self::observe(AgencyObserver::class);
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
