<?php

namespace App\Filament\Resources\Cms\MainPostSliderResource\Pages;

use App\Filament\Resources\Cms\MainPostSliderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMainPostSlider extends CreateRecord
{
    protected static string $resource = MainPostSliderResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slideable_type'] = 'cms_pages';
        $data['slideable_id'] = 1; // 1 - Index

        return $data;
    }
}
