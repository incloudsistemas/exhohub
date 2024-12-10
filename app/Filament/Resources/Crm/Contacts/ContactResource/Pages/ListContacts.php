<?php

namespace App\Filament\Resources\Crm\Contacts\ContactResource\Pages;

use App\Filament\Resources\Crm\Contacts\ContactResource;
use App\Filament\Resources\Crm\Contacts\IndividualResource;
use App\Filament\Resources\Crm\Contacts\LegalEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('create-individual')
                    ->label(__('Criar Pessoa'))
                    ->url(
                        IndividualResource::getUrl('create'),
                    )
                    ->hidden(
                        fn (): bool =>
                        !auth()->user()->can('Cadastrar [CRM] Contatos')
                    ),
                Actions\Action::make('create-legal-entity')
                    ->label(__('Criar Empresa'))
                    ->url(
                        LegalEntityResource::getUrl('create'),
                    )
                    ->hidden(
                        fn (): bool =>
                        !auth()->user()->can('Cadastrar [CRM] Contatos')
                    ),
            ])
                ->label(__('Criar Contato'))
                ->icon('heroicon-m-chevron-down')
                ->color('primary')
                ->button()
                ->hidden(
                    fn (): bool =>
                    !auth()->user()->can('Cadastrar [CRM] Contatos')
                ),
        ];
    }
}
