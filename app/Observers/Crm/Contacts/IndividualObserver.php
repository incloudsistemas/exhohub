<?php

namespace App\Observers\Crm\Contacts;

use App\Models\Crm\Contacts\Individual;

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

    public function deleted(Individual $individual): void
    {
        $individual->cpf = !empty($individual->cpf) ? $individual->cpf . '//deleted_' . md5(uniqid()) : null;
        $individual->save();

        $individual->contact->email = !empty($individual->contact->email) ? $individual->contact->email . '//deleted_' . md5(uniqid()) : null;
        $individual->contact->save();

        $individual->contact->delete();
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
