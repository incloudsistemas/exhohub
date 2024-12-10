<?php

namespace App\Observers\Support;

use App\Models\Support\TicketCategory;

class TicketCategoryObserver
{
    /**
     * Handle the TicketCategory "created" event.
     */
    public function created(TicketCategory $ticketCategory): void
    {
        //
    }

    /**
     * Handle the TicketCategory "updated" event.
     */
    public function updated(TicketCategory $ticketCategory): void
    {
        //
    }

    public function deleted(TicketCategory $ticketCategory): void
    {
        $ticketCategory->slug = $ticketCategory->slug . '//deleted_' . md5(uniqid());
        $ticketCategory->save();
    }

    /**
     * Handle the TicketCategory "restored" event.
     */
    public function restored(TicketCategory $ticketCategory): void
    {
        //
    }

    /**
     * Handle the TicketCategory "force deleted" event.
     */
    public function forceDeleted(TicketCategory $ticketCategory): void
    {
        //
    }
}
