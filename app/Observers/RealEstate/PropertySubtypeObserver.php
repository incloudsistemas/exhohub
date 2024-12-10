<?php

namespace App\Observers\RealEstate;

use App\Models\RealEstate\PropertySubtype;

class PropertySubtypeObserver
{
    /**
     * Handle the PropertySubtype "created" event.
     */
    public function created(PropertySubtype $propertySubtype): void
    {
        //
    }

    /**
     * Handle the PropertySubtype "updated" event.
     */
    public function updated(PropertySubtype $propertySubtype): void
    {
        //
    }

    /**
     * Handle the PropertySubtype "deleted" event.
     */
    public function deleted(PropertySubtype $propertySubtype): void
    {
        $propertySubtype->slug = $propertySubtype->slug . '//deleted_' . md5(uniqid());
        $propertySubtype->save();
    }

    /**
     * Handle the PropertySubtype "restored" event.
     */
    public function restored(PropertySubtype $propertySubtype): void
    {
        //
    }

    /**
     * Handle the PropertySubtype "force deleted" event.
     */
    public function forceDeleted(PropertySubtype $propertySubtype): void
    {
        //
    }
}
