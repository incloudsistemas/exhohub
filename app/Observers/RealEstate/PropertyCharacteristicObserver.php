<?php

namespace App\Observers\RealEstate;

use App\Models\RealEstate\PropertyCharacteristic;

class PropertyCharacteristicObserver
{
    /**
     * Handle the PropertyCharacteristic "created" event.
     */
    public function created(PropertyCharacteristic $propertyCharacteristic): void
    {
        //
    }

    /**
     * Handle the PropertyCharacteristic "updated" event.
     */
    public function updated(PropertyCharacteristic $propertyCharacteristic): void
    {
        //
    }

    /**
     * Handle the PropertyCharacteristic "deleted" event.
     */
    public function deleted(PropertyCharacteristic $propertyCharacteristic): void
    {
        $propertyCharacteristic->slug = $propertyCharacteristic->slug . '//deleted_' . md5(uniqid());
        $propertyCharacteristic->save();
    }

    /**
     * Handle the PropertyCharacteristic "restored" event.
     */
    public function restored(PropertyCharacteristic $propertyCharacteristic): void
    {
        //
    }

    /**
     * Handle the PropertyCharacteristic "force deleted" event.
     */
    public function forceDeleted(PropertyCharacteristic $propertyCharacteristic): void
    {
        //
    }
}
