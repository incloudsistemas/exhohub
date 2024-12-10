<?php

namespace App\Observers\System;

use App\Models\System\CreciControlStage;

class CreciControlStageObserver
{
    /**
     * Handle the CreciControlStage "created" event.
     */
    public function created(CreciControlStage $creciControlStage): void
    {
        //
    }

    /**
     * Handle the CreciControlStage "updated" event.
     */
    public function updated(CreciControlStage $creciControlStage): void
    {
        //
    }

    /**
     * Handle the CreciControlStage "deleted" event.
     */
    public function deleted(CreciControlStage $creciControlStage): void
    {
        //
    }

    /**
     * Handle the CreciControlStage "restored" event.
     */
    public function restored(CreciControlStage $creciControlStage): void
    {
        $creciControlStage->slug = $creciControlStage->slug . '//deleted_' . md5(uniqid());
        $creciControlStage->save();
    }

    /**
     * Handle the CreciControlStage "force deleted" event.
     */
    public function forceDeleted(CreciControlStage $creciControlStage): void
    {
        //
    }
}
