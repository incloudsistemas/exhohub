<?php

namespace App\Filament\Resources\Crm\Business\BusinessResource\Pages;

use App\Filament\Resources\Crm\Business\BusinessResource;
use App\Models\Activities\Notification;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Funnels\FunnelStage;
use App\Models\Crm\Funnels\FunnelSubstage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected Funnel $funnel;

    protected FunnelStage $funnelStage;

    protected ?FunnelSubstage $funnelSubstage = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $contact = Contact::findOrFail($data['contact_id']);
        $data['name'] = $contact->name;

        $data['user_id'] = auth()->user()->id;

        return $data;
    }

    protected function beforeCreate(): void
    {
        $this->funnel = Funnel::findOrFail($this->data['funnel_id']);
        $this->funnelStage = FunnelStage::findOrFail($this->data['funnel_stage_id']);

        if ($this->data['funnel_substage_id']) {
            $this->funnelSubstage = FunnelSubstage::find($this->data['funnel_substage_id']);
        }
    }

    protected function afterCreate(): void
    {
        $this->attachUserCollector();
        $this->createBusinessFunnelStage();
        $this->createNotificationActivity();

        // Business won
        if ($this->funnelStage->business_probability === 100) {
            $this->updateContactRolesToCustomer();
        }
    }

    protected function attachUserCollector(): void
    {
        $this->record->users()
            ->attach($this->data['user_collector_id'], ['business_at' => $this->data['business_at']]);
    }

    protected function createBusinessFunnelStage(): void
    {
        $this->record->businessFunnelStages()
            ->create([
                'funnel_id'          => $this->data['funnel_id'],
                'funnel_stage_id'    => $this->data['funnel_stage_id'],
                'funnel_substage_id' => $this->data['funnel_substage_id'],
                'loss_reason'        => $this->data['loss_reason'] ?? null,
                'business_at'        => $this->data['business_at'],
            ]);
    }

    protected function createNotificationActivity(): void
    {
        $userName = auth()->user()->name;
        $description = "Novo negócio criado por: {$userName} => {$this->funnel->name} / Etapa: {$this->funnelStage->name}";

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

    protected function updateContactRolesToCustomer(): void
    {
        $contact = $this->record->contact;
        $roleId = 3; // Cliente

        if (!$contact->roles->contains($roleId)) {
            $contact->roles()
                ->attach($roleId);
        }
    }
}
