<?php

namespace App\Observers\System;

use App\Models\System\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    public function deleted(User $user): void
    {
        $user->email = $user->email . '//deleted_' . md5(uniqid());
        $user->cpf = !empty($user->cpf) ? $user->cpf . '//deleted_' . md5(uniqid()) : null;

        $user->save();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
