<?php

namespace App\Filament\Resources\Cms\MainPostSliderResource\Pages;

use App\Filament\Resources\Cms\MainPostSliderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMainPostSliders extends ListRecords
{
    protected static string $resource = MainPostSliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
