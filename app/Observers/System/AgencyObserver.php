<?php

namespace App\Observers\System;

use App\Models\System\Agency;

class AgencyObserver
{
    /**
     * Handle the Agency "created" event.
     */
    public function created(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "updated" event.
     */
    public function updated(Agency $agency): void
    {
        //
    }

    public function deleted(Agency $agency): void
    {
        $agency->slug = $agency->slug . '//deleted_' . md5(uniqid());
        $agency->save();
    }

    /**
     * Handle the Agency "restored" event.
     */
    public function restored(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "force deleted" event.
     */
    public function forceDeleted(Agency $agency): void
    {
        //
    }
}
