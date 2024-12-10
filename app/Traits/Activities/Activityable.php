<?php

namespace App\Traits\Activities;

use App\Models\Activities\Activity;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait Activityable
{
    use SoftDeletes, InteractsWithMedia, ClearsResponseCache;

    public function activity(): MorphOne
    {
        return $this->morphOne(related: Activity::class, name: 'activityable');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 345, 230)
            ->nonQueued();
    }

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
}
