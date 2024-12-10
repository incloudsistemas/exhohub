<?php

namespace App\Filament\Resources\Crm\Business\BusinessResource\Pages;

use App\Filament\Pages\Crm\Business\BusinessKanbanBoard;
use App\Filament\Resources\Crm\Business\BusinessResource;
use App\Models\Crm\Funnels\Funnel;
use App\Services\Crm\Business\BusinessService;
use App\Services\Crm\Funnels\FunnelService;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Forms\Form;

class ListBusinesses extends ListRecords
{
    protected static string $resource = BusinessResource::class;

    public ?string $customTitle = null;

    public ?Funnel $currentFunnel = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('funnel')
                // ->label(__('Alterar Funil'))
                ->label(__($this->currentFunnel->name))
                ->button()
                ->color('gray')
                ->icon('heroicon-o-funnel')
                ->form([
                    Forms\Components\Select::make('funnel_id')
                        ->label(__('Funil'))
                        ->options(
                            fn(FunnelService $service): array =>
                            $service->getOptionsByFunnels(),
                        )
                        ->default($this->currentFunnel->id)
                        ->searchable()
                        ->preload()
                        ->selectablePlaceholder(false)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $url = BusinessResource::getUrl('index', ['funnel' => $data['funnel_id']]);
                    return redirect()->to($url);
                })
                ->modalHeading(__('Escolha o Funil')),
            Actions\Action::make('kanban-board')
                ->label(__('Visualizar Kanban'))
                ->button()
                ->color('gray')
                ->icon('heroicon-o-rectangle-stack')
                ->url(
                    fn(): string =>
                    BusinessKanbanBoard::getUrl(['funnel' => $this->currentFunnel->id]),
                ),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $funnelId = request()->route('funnel');
        $this->setCurrentFunnel(funnelId: $funnelId);

        $tabs = [
            null => Tab::make(__('Todos')),
        ];

        foreach ($this->currentFunnel->stages as $key => $funnelStage) {
            $tabs[$key] = Tab::make(__($funnelStage->name))
                ->query(
                    fn($query) =>
                    $query->where('funnel_stage_id', $funnelStage->id)
                );
        }

        return $tabs;
    }

    public function mount(): void
    {
        parent::mount();

        $funnelId = request()->route('funnel');
        $this->setCurrentFunnel(funnelId: $funnelId);

        $this->updateTitle();
    }

    public function updatedTableFiltersFunnelStageSubstagesFunnel(): void
    {
        $funnelId = $this->tableFilters['funnel_stage_substages']['funnel'];

        $this->setCurrentFunnel(funnelId: $funnelId);
        $this->updateTitle();
    }

    protected function setCurrentFunnel(int|string|null $funnelId): void
    {
        if (!$funnelId) {
            $funnelId = request()->input('tableFilters.funnel_stage_substages.funnel');
        }

        $this->currentFunnel = Funnel::byStatuses([1]) // 1 - Ativo
            ->where('id', $funnelId)
            ->first();

        if (!$this->currentFunnel) {
            $service = app(BusinessService::class);
            $this->currentFunnel = $service->getBusinessDefaultFunnel();
        }
    }

    protected function updateTitle(): void
    {
        $title = static::$title ?? static::getResource()::getTitleCasePluralModelLabel();

        // if ($this->currentFunnel) {
        //     $title .= ' / ' . $this->currentFunnel->name;
        // }

        $this->customTitle = $title;
    }

    public function getTitle(): string
    {
        return $this->customTitle ?? '';
    }
}
