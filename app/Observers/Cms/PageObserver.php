<?php

namespace App\Observers\Cms;

use App\Models\Cms\Page;

class PageObserver
{
    /**
     * Handle the Page "created" event.
     */
    public function created(Page $page): void
    {
        //
    }

    /**
     * Handle the Page "updated" event.
     */
    public function updated(Page $page): void
    {
        //
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        $page->cmsPost->slug = $page->cmsPost->slug . '//deleted_' . md5(uniqid());
        $page->cmsPost->save();

        $page->cmsPost->delete();

        foreach ($page->subpages as $subpage) {
            $subpage->cmsPost->slug = $subpage->cmsPost->slug . '//deleted_' . md5(uniqid());
            $subpage->cmsPost->save();

            $subpage->cmsPost->delete();
            $subpage->delete();
        }
    }

    /**
     * Handle the Page "restored" event.
     */
    public function restored(Page $page): void
    {
        //
    }

    /**
     * Handle the Page "force deleted" event.
     */
    public function forceDeleted(Page $page): void
    {
        //
    }
}
