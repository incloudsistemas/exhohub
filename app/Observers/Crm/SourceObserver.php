<?php

namespace App\Observers\Crm;

use App\Models\Crm\Source;

class SourceObserver
{
    /**
     * Handle the Source "created" event.
     */
    public function created(Source $source): void
    {
        //
    }

    /**
     * Handle the Source "updated" event.
     */
    public function updated(Source $source): void
    {
        //
    }

    public function deleted(Source $source): void
    {
        $source->slug = $source->slug . '//deleted_' . md5(uniqid());
        $source->save();
    }

    /**
     * Handle the Source "restored" event.
     */
    public function restored(Source $source): void
    {
        //
    }

    /**
     * Handle the Source "force deleted" event.
     */
    public function forceDeleted(Source $source): void
    {
        //
    }
}
