<?php

namespace App\Observers\RealEstate;

use App\Models\RealEstate\Individual;

class IndividualObserver
{
    /**
     * Handle the Individual "created" event.
     */
    public function created(Individual $individual): void
    {
        //
    }

    /**
     * Handle the Individual "updated" event.
     */
    public function updated(Individual $individual): void
    {
        //
    }

    /**
     * Handle the Individual "deleted" event.
     */
    public function deleted(Individual $individual): void
    {
        $individual->property->code = $individual->property->code . '//deleted_' . md5(uniqid());
        $individual->property->slug = $individual->property->slug . '//deleted_' . md5(uniqid());
        $individual->property->save();

        $individual->property->delete();
    }

    /**
     * Handle the Individual "restored" event.
     */
    public function restored(Individual $individual): void
    {
        //
    }

    /**
     * Handle the Individual "force deleted" event.
     */
    public function forceDeleted(Individual $individual): void
    {
        //
    }
}
