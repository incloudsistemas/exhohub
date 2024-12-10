<?php

namespace App\Filament\Resources\System\UserResource\Pages;

use App\Filament\Resources\System\UserResource;
use App\Mail\System\NewUserCreatedAlert;
use Filament\Actions;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make($data['password']);

        unset($data['password_confirmation']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->attachTeams();
        $this->createAddress();
        $this->sendEmailNotification();
    }

    protected function attachTeams(): void
    {
        // Attach director teams
        if (!empty($this->data['teams']['director'])) {
            $directorData = collect($this->data['teams']['director'])
                ->mapWithKeys(function ($id) {
                    return [$id => ['role' => 1]]; // '1' - directors
                })
                ->all();

            $this->record->teams()
                ->attach($directorData);
        }

        // Attach manager teams
        if (!empty($this->data['teams']['manager'])) {
            $managerData = collect($this->data['teams']['manager'])
                ->mapWithKeys(function ($id) {
                    return [$id => ['role' => 2]]; // '2' - managers
                })
                ->all();

            $this->record->teams()
                ->attach($managerData);
        }

        // Attach realtor teams
        if (!empty($this->data['teams']['realtor'])) {
            $realtorData = collect($this->data['teams']['realtor'])
                ->mapWithKeys(function ($id) {
                    return [$id => ['role' => 3]]; // '3' - realtors
                })
                ->all();

            $this->record->teams()
                ->attach($realtorData);
        }
    }

    protected function createAddress(): void
    {
        $this->data['address']['is_main'] = true;

        $this->record->address()
            ->create($this->data['address']);
    }

    protected function sendEmailNotification(): void
    {
        $this->data['action'] = Dashboard::getUrl();

        Mail::to($this->record->email)
            ->send(new NewUserCreatedAlert($this->data));
    }
}
