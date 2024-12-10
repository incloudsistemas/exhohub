<?php

namespace App\Filament\Resources\Cms\PageResource\Pages;

use App\Filament\Resources\Cms\PageResource;
use App\Models\Cms\Page;
use App\Services\Cms\PageService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(PageService $service, Actions\DeleteAction $action, Page $record) =>
                    $service->preventPageDeleteIf(action: $action, page: $record),
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $cmsPost = $this->record->cmsPost;

        $data['cms_post']['title'] = $cmsPost->title;
        $data['cms_post']['slug'] = $cmsPost->slug;

        $data['cms_post']['categories'] = isset($cmsPost->postCategories)
            ? $cmsPost->postCategories->pluck('id')
            ->toArray()
            : [];

        $data['cms_post']['excerpt'] = $cmsPost->excerpt;
        $data['cms_post']['body'] = $cmsPost->body;
        $data['cms_post']['url'] = $cmsPost->url;
        $data['cms_post']['embed_video'] = $cmsPost->embed_video;
        $data['cms_post']['tags'] = $cmsPost->tags;
        $data['cms_post']['meta_title'] = $cmsPost->meta_title;
        $data['cms_post']['meta_description'] = $cmsPost->meta_description;
        $data['cms_post']['meta_keywords'] = $cmsPost->meta_keywords;
        $data['cms_post']['user_id'] = $cmsPost->user_id;
        $data['cms_post']['order'] = $cmsPost->order;
        $data['cms_post']['featured'] = $cmsPost->featured;
        $data['cms_post']['comment'] = $cmsPost->comment;
        $data['cms_post']['publish_at'] = $cmsPost->publish_at;
        $data['cms_post']['expiration_at'] = $cmsPost->expiration_at;
        $data['cms_post']['status'] = $cmsPost->status;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->updateCmsPost();
        $this->syncCategories();
    }

    protected function updateCmsPost(): void
    {
        $this->record->cmsPost->update($this->data['cms_post']);
    }

    protected function syncCategories(): void
    {
        $this->record->cmsPost->postCategories()
            ->sync($this->data['cms_post']['categories']);
    }
}
