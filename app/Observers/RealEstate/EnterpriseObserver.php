<?php

namespace App\Observers\RealEstate;

use App\Models\RealEstate\Enterprise;

class EnterpriseObserver
{
    /**
     * Handle the Enterprise "created" event.
     */
    public function created(Enterprise $enterprise): void
    {
        //
    }

    /**
     * Handle the Enterprise "updated" event.
     */
    public function updated(Enterprise $enterprise): void
    {
        //
    }

    /**
     * Handle the Enterprise "deleted" event.
     */
    public function deleted(Enterprise $enterprise): void
    {
        $enterprise->property->code = $enterprise->property->code . '//deleted_' . md5(uniqid());
        $enterprise->property->slug = $enterprise->property->slug . '//deleted_' . md5(uniqid());
        $enterprise->property->save();

        $enterprise->property->delete();
    }

    /**
     * Handle the Enterprise "restored" event.
     */
    public function restored(Enterprise $enterprise): void
    {
        //
    }

    /**
     * Handle the Enterprise "force deleted" event.
     */
    public function forceDeleted(Enterprise $enterprise): void
    {
        //
    }
}
