<?php

namespace App\Filament\Resources\Cms\PageResource\Pages;

use App\Filament\Resources\Cms\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->createCmsPost();
        $this->attachCategories();
    }

    protected function createCmsPost(): void
    {
        $this->record->cmsPost()
            ->create($this->data['cms_post']);
    }

    protected function attachCategories(): void
    {
        $this->record->cmsPost->postCategories()
            ->attach($this->data['cms_post']['categories']);
    }
}
