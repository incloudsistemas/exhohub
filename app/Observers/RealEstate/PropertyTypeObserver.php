<?php

namespace App\Observers\RealEstate;

use App\Models\RealEstate\PropertyType;

class PropertyTypeObserver
{
    /**
     * Handle the PropertyType "created" event.
     */
    public function created(PropertyType $propertyType): void
    {
        //
    }

    /**
     * Handle the PropertyType "updated" event.
     */
    public function updated(PropertyType $propertyType): void
    {
        //
    }

    /**
     * Handle the PropertyType "deleted" event.
     */
    public function deleted(PropertyType $propertyType): void
    {
        $propertyType->slug = $propertyType->slug . '//deleted_' . md5(uniqid());
        $propertyType->save();
    }

    /**
     * Handle the PropertyType "restored" event.
     */
    public function restored(PropertyType $propertyType): void
    {
        //
    }

    /**
     * Handle the PropertyType "force deleted" event.
     */
    public function forceDeleted(PropertyType $propertyType): void
    {
        //
    }
}
