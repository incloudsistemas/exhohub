<?php

namespace App\Filament\Resources\System\UserResource\Pages;

use App\Filament\Resources\System\UserResource;
use App\Mail\System\NewUserApprovedAlert;
use App\Models\System\User;
use App\Services\System\UserService;
use Filament\Actions;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected int $currentStatus;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn (UserService $service, Actions\DeleteAction $action, User $record) =>
                    $service->preventUserDeleteIf(action: $action, user: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['email_confirmation'] = $data['email'];

        $data['teams']['director'] = $this->record->teams()
            ->wherePivot('role', 1) // '1' - Diretor/Directors
            ->pluck('id')
            ->toArray();

        $data['teams']['manager'] = $this->record->teams()
            ->wherePivot('role', 2) // '2' - Gerente/Managers
            ->pluck('id')
            ->toArray();

        $data['teams']['realtor'] = $this->record->teams()
            ->wherePivot('role', 3) // '3' - Corretor/Realtors
            ->pluck('id')
            ->toArray();

        $data['address']['zipcode'] = $this->record->address?->zipcode;
        $data['address']['uf'] = $this->record->address?->uf?->name;
        $data['address']['city'] = $this->record->address?->city;
        $data['address']['district'] = $this->record->address?->district;
        $data['address']['address_line'] = $this->record->address?->address_line;
        $data['address']['number'] = $this->record->address?->number;
        $data['address']['complement'] = $this->record->address?->complement;
        $data['address']['reference'] = $this->record->address?->reference;

        $creciStage = $this->record->userCreciStages()
            ->latest()
            ->first();

        if (isset($creciStage)) {
            $data['creci']['creci_control_stage_id'] = $creciStage->creci_control_stage_id;
            $data['creci']['member_since'] = $creciStage->member_since;
            $data['creci']['valid_thru'] = $creciStage->valid_thru;

            // STEP 2
            $data['creci']['attachments']['stage_2']['registration']['file_name'] = $creciStage->getMedia('stage_2_registration')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            $data['creci']['attachments']['stage_2']['payment']['file_name'] = $creciStage->getMedia('stage_2_payment')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            // STEP 3
            $data['creci']['attachments']['stage_3']['internship']['file_name'] = $creciStage->getMedia('stage_3_internship')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            $data['creci']['attachments']['stage_3']['frequency']['file_name'] = $creciStage->getMedia('stage_3_frequency')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            $data['creci']['attachments']['stage_3']['protocol']['file_name'] = $creciStage->getMedia('stage_3_protocol')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            // STEP 4
            $data['creci']['attachments']['stage_4']['protocol']['file_name'] = $creciStage->getMedia('stage_4_protocol')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            // STEP 5
            $data['creci']['attachments']['stage_5']['protocol']['file_name'] = $creciStage->getMedia('stage_5_protocol')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;

            $data['creci']['attachments']['stage_5']['creci']['file_name'] = $creciStage->getMedia('stage_5_creci')
                ->sortByDesc('order_column')
                ->first()
                ?->file_name;
        }

        $data['creci']['attachments']['stage_2']['registration']['collection_name'] = 'stage_2_registration';
        $data['creci']['attachments']['stage_2']['registration']['name'] = 'Comprovante de matrícula';
        $data['creci']['attachments']['stage_2']['payment']['collection_name'] = 'stage_2_payment';
        $data['creci']['attachments']['stage_2']['payment']['name'] = 'Comprovante de pagamento';

        $data['creci']['attachments']['stage_3']['internship']['collection_name'] = 'stage_3_internship';
        $data['creci']['attachments']['stage_3']['internship']['name'] = 'Termo de compromisso de estágio';
        $data['creci']['attachments']['stage_3']['frequency']['collection_name'] = 'stage_3_frequency';
        $data['creci']['attachments']['stage_3']['frequency']['name'] = 'Declaração de frequência';
        $data['creci']['attachments']['stage_3']['protocol']['collection_name'] = 'stage_3_protocol';
        $data['creci']['attachments']['stage_3']['protocol']['name'] = 'Nº de protocolo';

        $data['creci']['attachments']['stage_4']['protocol']['collection_name'] = 'stage_4_protocol';
        $data['creci']['attachments']['stage_4']['protocol']['name'] = 'Nº de protocolo de renovação';

        $data['creci']['attachments']['stage_5']['protocol']['collection_name'] = 'stage_5_protocol';
        $data['creci']['attachments']['stage_5']['protocol']['name'] = 'Nº de protocolo';
        $data['creci']['attachments']['stage_5']['creci']['collection_name'] = 'stage_5_creci';
        $data['creci']['attachments']['stage_5']['creci']['name'] = 'Carteira CRECI';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['password']) && !empty(trim($data['password']))) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        $this->currentStatus = $this->record->status->value;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncTeams();
        $this->updateAddress();
        $this->createUserCreciStages();
        $this->sendEmailNotification();
    }

    protected function syncTeams(): void
    {
        // Attach director teams
        if (!empty($this->data['teams']['director'])) {
            $directorData = collect($this->data['teams']['director'])
                ->mapWithKeys(function ($id) {
                    return [$id => ['role' => 1]]; // '1' - Diretor/Directors
                })
                ->all();

            $this->record->teams()
                ->wherePivot('role', 1)
                ->sync($directorData);
        }

        // Attach manager teams
        if (!empty($this->data['teams']['manager'])) {
            $managerData = collect($this->data['teams']['manager'])
                ->mapWithKeys(function ($id) {
                    return [$id => ['role' => 2]]; // '2' - Gerente/Managers
                })
                ->all();

            $this->record->teams()
                ->wherePivot('role', 2)
                ->sync($managerData);
        }

        // Attach realtor teams
        if (!empty($this->data['teams']['realtor'])) {
            $realtorData = collect($this->data['teams']['realtor'])
                ->mapWithKeys(function ($id) {
                    return [$id => ['role' => 3]]; // '3' - Corretor/Realtors
                })
                ->all();

            $this->record->teams()
                ->wherePivot('role', 3)
                ->sync($realtorData);
        }
    }

    protected function updateAddress(): void
    {
        $this->data['address']['is_main'] = true;

        $this->record->address()
            ->updateOrCreate(
                ['addressable_type' => MorphMapByClass(model: get_class($this->record)), 'addressable_id' => $this->record->id],
                $this->data['address']
            );
    }

    protected function createUserCreciStages(): void
    {
        if (!$this->data['creci']['creci_control_stage_id']) {
            return;
        }

        $creciStage = $this->record->userCreciStages()
            ->latest()
            ->first();

        $fileNames = $creciStage?->media()
            ->pluck('file_name')
            ->toArray() ?? [];

        if (!isset($creciStage) || isset($creciStage) && $creciStage->creci_control_stage_id != $this->data['creci']['creci_control_stage_id']) {
            $creciStage = $this->record->userCreciStages()
                ->create($this->data['creci']);
        }

        foreach ($this->data['creci']['attachments'] as $stepAttachments) {
            foreach ($stepAttachments as $attachment) {
                if (!empty($attachment['file_name'])) {
                    $attachment['file_name'] = array_values($attachment['file_name'])[0];

                    if (!in_array($attachment['file_name'], $fileNames)) {
                        $this->createMedia(ownerRecord: $creciStage, data: $attachment);
                    }
                }
            }
        }
    }

    protected function createMedia(Model $ownerRecord, array $data): void
    {
        $data['collection_name'] = $data['collection_name'] ?? 'attachments';
        $data['disk'] = $data['disk'] ?? 'public';
        $data['conversions_disk'] = $data['conversions_disk'] ?? 'public';
        $data['manipulations'] = $data['manipulations'] ?? [];
        $data['custom_properties'] = $data['custom_properties'] ?? [];
        $data['generated_conversions'] = $data['generated_conversions'] ?? [];
        $data['responsive_images'] = $data['responsive_images'] ?? [];

        $data['mime_type'] = Storage::disk('public')
            ->mimeType($data['file_name']);

        $data['size'] = Storage::disk('public')
            ->size($data['file_name']);

        $ownerRecord->media()
            ->create($data);
    }

    protected function sendEmailNotification(): void
    {
        if ((int) $this->currentStatus === 2 && (int) $this->data['status'] === 1) {
            $this->data['action'] = Dashboard::getUrl();

            Mail::to($this->record->email)
                ->send(new NewUserApprovedAlert($this->data));
        }
    }
}
