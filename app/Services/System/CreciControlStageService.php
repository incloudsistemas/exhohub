<?php

namespace App\Services\System;

use App\Enums\System\CreciControlStageRoleEnum;
use App\Models\System\CreciControlStage;
use App\Services\BaseService;
use Filament\Notifications\Notification;

class CreciControlStageService extends BaseService
{
    public function __construct(protected CreciControlStage $creciControlStage)
    {
        //
    }

    public function getOptionsByActiveControlStages(): array
    {
        $stages = $this->creciControlStage->byStatuses([1])
            ->get()
            ->groupBy('role');

        $options = [];
        foreach ($stages as $role => $stagesGroup) {
            $roleLabel = CreciControlStageRoleEnum::from($role)
                ->getLabel();

            $options[$roleLabel] = $stagesGroup->pluck('name', 'id')
                ->toArray();
        }

        return $options;
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventRoleDeleteIf($action, CreciControlStage $creciControlStage): void
    {
        if ($creciControlStage->userCreciStages->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de estágios de controle do CRECI'))
                ->warning()
                ->body(__('Este estágio possui usuários/corretores associados. Para excluir, você deve primeiro desvincular todos os usuários/corretores que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
