<?php

namespace App\Observers\Crm\Contacts;

use App\Models\Crm\Contacts\LegalEntity;

class LegalEntityObserver
{
    /**
     * Handle the LegalEntity "created" event.
     */
    public function created(LegalEntity $legalEntity): void
    {
        //
    }

    /**
     * Handle the LegalEntity "updated" event.
     */
    public function updated(LegalEntity $legalEntity): void
    {
        //
    }

    public function deleted(LegalEntity $legalEntity): void
    {
        $legalEntity->cnpj = !empty($legalEntity->cnpj) ? $legalEntity->cnpj . '//deleted_' . md5(uniqid()) : null;
        $legalEntity->save();

        $legalEntity->contact->email = !empty($legalEntity->contact->email) ? $legalEntity->contact->email . '//deleted_' . md5(uniqid()) : null;
        $legalEntity->contact->save();

        $legalEntity->contact->delete();
    }

    /**
     * Handle the LegalEntity "restored" event.
     */
    public function restored(LegalEntity $legalEntity): void
    {
        //
    }

    /**
     * Handle the LegalEntity "force deleted" event.
     */
    public function forceDeleted(LegalEntity $legalEntity): void
    {
        //
    }
}
