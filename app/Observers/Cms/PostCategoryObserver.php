<?php

namespace App\Observers\Cms;

use App\Models\Cms\PostCategory;

class PostCategoryObserver
{
    /**
     * Handle the PostCategory "created" event.
     */
    public function created(PostCategory $postCategory): void
    {
        //
    }

    /**
     * Handle the PostCategory "updated" event.
     */
    public function updated(PostCategory $postCategory): void
    {
        //
    }

    /**
     * Handle the PostCategory "deleted" event.
     */
    public function deleted(PostCategory $postCategory): void
    {
        $postCategory->slug = $postCategory->slug . '//deleted_' . md5(uniqid());
        $postCategory->save();
    }

    /**
     * Handle the PostCategory "restored" event.
     */
    public function restored(PostCategory $postCategory): void
    {
        //
    }

    /**
     * Handle the PostCategory "force deleted" event.
     */
    public function forceDeleted(PostCategory $postCategory): void
    {
        //
    }
}
