<?php

namespace App\Filament\Resources\RealEstate\EnterpriseResource\Pages;

use App\Filament\Resources\RealEstate\EnterpriseResource;
use App\Models\RealEstate\Enterprise;
use App\Services\RealEstate\EnterpriseService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnterprise extends EditRecord
{
    protected static string $resource = EnterpriseResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn (EnterpriseService $service, Actions\DeleteAction $action, Enterprise $record) =>
                    $service->preventEnterpriseDeleteIf(action: $action, enterprise: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['min_useful_area'] = $this->record->display_min_useful_area;
        $data['max_useful_area'] = $this->record->display_max_useful_area;
        $data['min_total_area'] = $this->record->display_min_total_area;
        $data['max_total_area'] = $this->record->display_max_total_area;

        $data['min_price'] = $this->record->display_min_price;
        $data['max_price'] = $this->record->display_max_price;

        $data['property']['usage'] = $this->record->property->usage->value;
        $data['property']['type_id'] = $this->record->property->type_id;
        $data['property']['subtype_id'] = $this->record->property->subtype_id;

        $data['property']['embed_videos'] = $this->record->property->embed_videos;

        $data['property']['address']['zipcode'] = $this->record->property->address->zipcode;
        $data['property']['address']['uf'] = $this->record->property->address->uf->name;
        $data['property']['address']['city'] = $this->record->property->address->city;
        $data['property']['address']['district'] = $this->record->property->address->district;
        $data['property']['address']['address_line'] = $this->record->property->address->address_line;
        $data['property']['address']['number'] = $this->record->property->address->number;
        $data['property']['address']['complement'] = $this->record->property->address->complement;
        $data['property']['address']['reference'] = $this->record->property->address->reference;
        $data['property']['show_address'] = $this->record->property->show_address;

        $data['property']['characteristics']['differences'] = $this->record->property->characteristics()
            ->byRoles([1])
            ->pluck('id')
            ->toArray();

        $data['property']['characteristics']['leisure'] = $this->record->property->characteristics()
            ->byRoles([2])
            ->pluck('id')
            ->toArray();

        $data['property']['characteristics']['security'] = $this->record->property->characteristics()
            ->byRoles([3])
            ->pluck('id')
            ->toArray();

        $data['property']['characteristics']['infrastructure'] = $this->record->property->characteristics()
            ->byRoles([4])
            ->pluck('id')
            ->toArray();

        $data['property']['floors'] = $this->record->property->floors;
        $data['property']['units_per_floor'] = $this->record->property->units_per_floor;
        $data['property']['towers'] = $this->record->property->towers;
        $data['property']['construct_year'] = $this->record->property->construct_year;
        $data['property']['condo_characteristics'] = $this->record->property->condo_characteristics;

        $data['property']['code'] = $this->record->property->code;
        $data['property']['title'] = $this->record->property->title;
        $data['property']['slug'] = $this->record->property->slug;
        $data['property']['condo_price'] = $this->record->property->display_condo_price;
        $data['property']['tax_price'] = $this->record->property->display_tax_price;
        $data['property']['user_id'] = $this->record->property->user_id;
        $data['property']['body'] = $this->record->property->body;
        $data['property']['tags'] = $this->record->property->tags;
        $data['property']['show_watermark'] = $this->record->property->show_watermark;
        $data['property']['owner_notes'] = $this->record->property->owner_notes;

        $data['property']['contact_construction_companies'] = $this->record->property->contacts()
            ->wherePivot('role', 2) // '2' - Construction / Construtora(s)
            ->pluck('id')
            ->toArray();

        $data['property']['contact_development_companies'] = $this->record->property->contacts()
            ->wherePivot('role', 3) // '3' - Development / Incorporadora(s)
            ->pluck('id')
            ->toArray();

        $data['property']['publish_on'] = $this->record->property->publish_on;
        $data['property']['publish_on_data']['canal_pro']['publication_type'] = $this->record->property->publish_on_data['canal_pro']['publication_type'] ?? null;

        $data['property']['meta_title'] = $this->record->property->meta_title;
        $data['property']['meta_description'] = $this->record->property->meta_description;
        $data['property']['meta_keywords'] = $this->record->property->meta_keywords;
        $data['property']['order'] = $this->record->property->order;
        $data['property']['featured'] = $this->record->property->featured;
        $data['property']['comment'] = $this->record->property->comment;
        $data['property']['publish_at'] = $this->record->property->publish_at;
        $data['property']['expiration_at'] = $this->record->property->expiration_at;
        $data['property']['status'] = $this->record->property->status;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->updateProperty();
        $this->updateAddress();
        $this->syncCharacteristics();
        $this->syncContacts();
    }

    protected function updateProperty(): void
    {
        $this->data['property']['embed_videos'] = array_values($this->data['property']['embed_videos']);
        // $this->data['property']['status'] = $this->data['property']['publish_on']['portal_web'] ? 1 : 0;

        $this->record->property->update($this->data['property']);
    }

    protected function updateAddress(): void
    {
        $this->data['property']['address']['is_main'] = true;

        $this->record->property->address()
            ->updateOrCreate(
                ['addressable_type' => MorphMapByClass(model: get_class($this->record->property)), 'addressable_id' => $this->record->property->id],
                $this->data['property']['address']
            );
    }

    protected function syncCharacteristics(): void
    {
        $mergedCharacteristics = array_merge(
            $this->data['property']['characteristics']['differences'],
            $this->data['property']['characteristics']['leisure'],
            $this->data['property']['characteristics']['security'],
            $this->data['property']['characteristics']['infrastructure'],
        );

        $this->record->property->characteristics()
            ->sync($mergedCharacteristics);
    }

    protected function syncContacts(): void
    {
        // Sync contact construction companies
        $constructionCompanies = collect($this->data['property']['contact_construction_companies'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 2]]; // '2' - Construction / Construtora(s)
            })
            ->all();

        $this->record->property->contacts()
            ->wherePivot('role', 2)
            ->sync($constructionCompanies);

        // Sync contact development companies
        $developmentCompanies = collect($this->data['property']['contact_development_companies'])
            ->mapWithKeys(function ($id) {
                return [$id => ['role' => 3]]; // '3' - Development / Incorporadora(s)
            })
            ->all();

        $this->record->property->contacts()
            ->wherePivot('role', 3)
            ->sync($developmentCompanies);
    }
}
