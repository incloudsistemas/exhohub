<?php

namespace App\Filament\Resources\Crm\Business\BusinessResource\Pages;

use App\Filament\Resources\Crm\Business\BusinessResource;
use App\Models\Activities\Notification;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Business\BusinessFunnelStage;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Funnels\FunnelStage;
use App\Models\Crm\Funnels\FunnelSubstage;
use App\Services\Crm\Business\BusinessService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected Funnel $funnel;

    protected FunnelStage $funnelStage;

    protected ?FunnelSubstage $funnelSubstage = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(BusinessService $service, Actions\DeleteAction $action, Business $record) =>
                    $service->preventBusinessDeleteIf(action: $action, business: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $currentStage = $this->record->currentBusinessFunnelStage();

        $data['funnel_id'] = $currentStage->funnel_id;
        $data['funnel_stage_id'] = $currentStage->funnel_stage_id;
        $data['funnel_substage_id'] = $currentStage->funnel_substage_id ?? null;
        $data['loss_reason'] = $currentStage->loss_reason ?? null;

        $currentUser = $this->record->currentUser();

        $data['user_collector_id'] = $currentUser->id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $contact = Contact::findOrFail($data['contact_id']);
        $data['name'] = $contact->name;

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->funnel = Funnel::findOrFail($this->data['funnel_id']);
        $this->funnelStage = FunnelStage::findOrFail($this->data['funnel_stage_id']);

        if ($this->data['funnel_substage_id']) {
            $this->funnelSubstage = FunnelSubstage::find($this->data['funnel_substage_id']);
        }
    }

    protected function afterSave(): void
    {
        $this->syncUserCollector();
        $this->updateBusinessFunnelStage();
        $this->updateNotificationActivity();

        // Business won
        if ($this->funnelStage->business_probability === 100) {
            $this->updateContactRolesToCustomer();
        }
    }

    protected function syncUserCollector(): void
    {
        if ($this->isNewUserCollectorDifferent()) {
            $this->record->users()
                ->attach($this->data['user_collector_id'], ['business_at' => now()]);
        }
    }

    protected function updateBusinessFunnelStage(): void
    {
        if ($this->isNewFunnelOrStageDifferent()) {
            $this->record->businessFunnelStages()
                ->create([
                    'funnel_id'          => $this->data['funnel_id'],
                    'funnel_stage_id'    => $this->data['funnel_stage_id'],
                    'funnel_substage_id' => $this->data['funnel_substage_id'],
                    'loss_reason'        => $this->data['loss_reason'] ?? null,
                    'business_at'        => now(),
                ]);
        }
    }

    protected function updateNotificationActivity(): void
    {
        if ($this->isNewFunnelOrStageDifferent()) {
            $description = "Etapa do negócio atualizada => {$this->funnel->name} / Etapa: {$this->funnelStage->name}";

            if ($this->funnelSubstage) {
                $description .= " / Sub-etapa: {$this->funnelSubstage->name}";
            }

            $activityNotification = Notification::create();
            $activity = $activityNotification->activity()
                ->create([
                    'user_id'     => auth()->user()->id,
                    'description' => $description
                ]);

            $activity->users()
                ->attach($this->data['user_collector_id']);

            $activity->contacts()
                ->attach($this->data['contact_id']);

            $activity->business()
                ->attach($this->record->id);
        }

        if ($this->isNewUserCollectorDifferent()) {
            $userName = auth()->user()->name;
            $description = "Novo negócio atribuído à {$this->record->currentUser()->name} por: {$userName}";

            $activityNotification = Notification::create();
            $activity = $activityNotification->activity()
                ->create([
                    'user_id'     => auth()->user()->id,
                    'description' => $description
                ]);

            $activity->users()
                ->attach($this->data['user_collector_id']);

            $activity->business()
                ->attach($this->record->id);
        }
    }

    protected function updateContactRolesToCustomer(): void
    {
        $contact = $this->record->contact;
        $roleId = 3; // Cliente/Customer

        if (!$contact->roles->contains($roleId)) {
            $contact->roles()
                ->attach($roleId);
        }
    }

    protected function isNewUserCollectorDifferent(): bool
    {
        $currentUser = $this->record->currentUser();

        return $currentUser->id !== $this->data['user_collector_id'];
    }

    protected function isNewFunnelOrStageDifferent(): bool
    {
        $currentStage = $this->record->currentBusinessFunnelStage();

        return !$currentStage ||
            $currentStage->funnel_id !== $this->data['funnel_id'] ||
            $currentStage->funnel_stage_id !== $this->data['funnel_stage_id'] ||
            $currentStage->funnel_substage_id !== $this->data['funnel_substage_id'];
    }
}
