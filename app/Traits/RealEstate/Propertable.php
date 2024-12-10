<?php

namespace App\Traits\RealEstate;

use App\Models\RealEstate\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait Propertable
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public function property(): MorphOne
    {
        return $this->morphOne(Property::class, 'propertable');
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

    public function scopeByRoles(Builder $query, array $roles): Builder
    {
        return $query->whereIn('role', $roles);
    }

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */

    public function getFeaturedImageAttribute(): ?Media
    {
        $featuredImage = $this->getFirstMedia('images');

        return $featuredImage ?? null;
    }

    public function getGalleryImagesAttribute(): ?Collection
    {
        $galleryImages = $this->getMedia('images')
            ->sortBy('order_column');

        return $galleryImages ?? null;
    }

    public function getFeaturedVideoAttribute(): ?Media
    {
        $featuredVideo = $this->getFirstMedia('videos');

        return $featuredVideo ?? null;
    }

    public function getGalleryVideosAttribute(): ?Collection
    {
        $galleryVideos = $this->getMedia('videos')
            ->sortBy('order_column');

        return $galleryVideos ?? null;
    }

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('attachments')
            ->sortBy('order_column');
    }
}
