<?php

namespace App\Observers\Support;

use App\Models\Support\TicketComment;

class TicketCommentObserver
{
    /**
     * Handle the TicketComment "created" event.
     */
    public function created(TicketComment $ticketComment): void
    {
        //
    }

    /**
     * Handle the TicketComment "updated" event.
     */
    public function updated(TicketComment $ticketComment): void
    {
        //
    }

    public function deleted(TicketComment $ticketComment): void
    {
        $ticketComment->slug = $ticketComment->slug . '//deleted_' . md5(uniqid());
        $ticketComment->save();
    }

    /**
     * Handle the TicketComment "restored" event.
     */
    public function restored(TicketComment $ticketComment): void
    {
        //
    }

    /**
     * Handle the TicketComment "force deleted" event.
     */
    public function forceDeleted(TicketComment $ticketComment): void
    {
        //
    }
}
