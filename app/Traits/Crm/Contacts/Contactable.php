<?php

namespace App\Traits\Crm\Contacts;

use App\Models\Crm\Contacts\Contact;
use App\Models\Polymorphics\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait Contactable
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public function addresses(): MorphMany
    {
        return $this->morphMany(related: Address::class, name: 'addressable');
    }

    public function contact(): MorphOne
    {
        return $this->morphOne(related: Contact::class, name: 'contactable');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
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

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('attachments')
            ->sortBy('order_column');
    }
}
