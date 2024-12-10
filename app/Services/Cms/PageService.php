<?php

namespace App\Services\Cms;

use App\Models\Cms\Page;
use App\Models\Cms\Post;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PageService extends BaseService
{
    public function __construct(protected Post $post, protected Page $page)
    {
        //
    }

    public function getOptionsByMainPages(?Page $page): array
    {
        return $this->page->whereNull('page_id')
            ->when($page, function ($query) use ($page) {
                return $query->where('id', '<>', $page->id);
            })
            ->get()
            ->mapWithKeys(function ($item): array {
                return [$item->id => $item->cmsPost->title];
            })
            ->toArray();
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventPageDeleteIf($action, Page $page): void
    {
        //
    }
}
