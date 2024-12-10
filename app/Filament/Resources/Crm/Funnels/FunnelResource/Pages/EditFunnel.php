<?php

namespace App\Filament\Resources\Crm\Funnels\FunnelResource\Pages;

use App\Filament\Resources\Crm\Funnels\FunnelResource;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Funnels\FunnelStage;
use App\Services\Crm\Funnels\FunnelService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFunnel extends EditRecord
{
    protected static string $resource = FunnelResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(FunnelService $service, Actions\DeleteAction $action, Funnel $record) =>
                    $service->preventFunnelDeleteIf(action: $action, funnel: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $doneFunnelStage = $this->getFunnelStage(probability: 100);
        $lostFunnelStage = $this->getFunnelStage(probability: 0);

        if ($doneFunnelStage) {
            $data['closing_stages']['done']['name'] = $doneFunnelStage->name;
            $data['closing_stages']['done']['business_probability'] = $doneFunnelStage->business_probability;
        }

        if ($lostFunnelStage) {
            $data['closing_stages']['lost']['name'] = $lostFunnelStage->name;
            $data['closing_stages']['lost']['business_probability'] = $lostFunnelStage->business_probability;
        }

        return $data;
    }

    protected function getFunnelStage(int $probability): ?FunnelStage
    {
        return $this->record->stages
            ->whereNotNull('business_probability')
            ->where('business_probability', $probability)
            ->first();
    }

    protected function afterSave(): void
    {
        $maxOrder = $this->record->stages()
            ->where(function ($query) {
                $query->whereNotIn('business_probability', [100, 0])
                    ->orWhereNull('business_probability');
            })
            ->max('order');

        $this->updateClosingStage(stage: 'done', probability: 100, order: $maxOrder + 1);
        $this->updateClosingStage(stage: 'lost', probability: 0, order: $maxOrder + 2);
    }

    protected function updateClosingStage(string $stage, int $probability, int $order): void
    {
        $this->data['closing_stages'][$stage]['order'] = $order;

        $this->record->stages()
            ->whereNotNull('business_probability')
            ->where('business_probability', $probability)
            ->first()
            ->update($this->data['closing_stages'][$stage]);
    }
}
