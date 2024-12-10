<?php

namespace App\Filament\Pages\Crm\Business;

use App\Filament\Resources\Crm\Business\BusinessResource;
use App\Models\Activities\Notification;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Funnels\FunnelStage;
use App\Services\Crm\Business\BusinessService;
use App\Services\Crm\Funnels\FunnelService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;

class BusinessKanbanBoard extends KanbanBoard
{
    protected static string $model = Business::class;

    // protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'crm/business/kanban-board/funnel/{funnel?}';

    // public bool $disableEditModal = true;

    // protected static ?string $title = 'Negócio';

    protected static ?string $navigationLabel = 'Kanban';

    protected ?string $heading = 'Negócios / Kanban';

    // protected ?string $subheading = 'Custom Page Subheading';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationParentItem = 'Negócios';

    protected static string $recordTitleAttribute = 'name';

    protected static string $recordStatusAttribute = 'funnel_stage_id';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected string $editModalTitle = 'Editar Negócio';

    protected string $editModalWidth = '2xl';

    protected string $editModalSaveButtonLabel = 'Editar';

    protected string $editModalCancelButtonLabel = 'Cancelar';

    // protected bool $editModalSlideOver = true;

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
                    $url = BusinessKanbanBoard::getUrl(['funnel' => $data['funnel_id']]);
                    return redirect()->to($url);
                })
                ->modalHeading(__('Escolha o Funil')),
            Actions\Action::make('kanban-board')
                ->label(__('Visualizar Lista'))
                ->button()
                ->color('gray')
                ->icon('heroicon-m-numbered-list')
                ->url(
                    fn(): string =>
                    BusinessResource::getUrl('index', ['funnel' => $this->currentFunnel->id]),
                ),
            Actions\Action::make('create')
                ->label(__('Criar Negócio'))
                ->button()
                ->url(
                    fn(): string =>
                    BusinessResource::getUrl('create'),
                )
                ->hidden(
                    fn(): bool =>
                    !auth()->user()->can('Cadastrar [CRM] Negócios')
                ),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $funnelId = request()->route('funnel');

        $this->currentFunnel = Funnel::byStatuses([1]) // 1 - Ativo
            ->where('id', $funnelId)
            ->first();

        if (!$this->currentFunnel) {
            $service = app(BusinessService::class);
            $this->currentFunnel = $service->getBusinessDefaultFunnel();
        }

        $this->updateTitle();
    }

    protected function statuses(): Collection
    {
        return $this->currentFunnel->stages()
            ->get()
            ->map(function ($stage) {
                return [
                    'id'    => $stage->id,
                    'title' => $stage->name
                ];
            });
    }

    protected function records(): Collection
    {
        $user = auth()->user();

        $query = $this->getEloquentQuery()
            ->whereHas('businessFunnelStages', function (Builder $query): Builder {
                return $query->where('funnel_id', $this->currentFunnel->id)
                    ->orderBy('business_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            })
            ->ordered()
            ->orderBy('business_at', 'desc');

        if ($user->hasAnyRole(['Superadministrador', 'Administrador'])) {
            return $query->get();
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            $teamUserIds = $user->teams()
                ->with('users:id')
                ->get()
                ->pluck('users.*.id')
                ->flatten()
                ->unique()
                ->toArray();

            return $query->whereHas('users', function (Builder $query) use ($teamUserIds): Builder {
                return $query->whereIn('user_id', $teamUserIds)
                    ->orderBy('business_at', 'desc')
                    ->limit(1);
            })
                ->get();
        }

        return $query->whereHas('users', function (Builder $query) use ($user): Builder {
            return $query->where('user_id', $user->id)
                ->orderBy('business_at', 'desc')
                ->limit(1);
        })
            ->get();
    }

    public function onStatusChanged(int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        $business = Business::findOrFail($recordId);

        $currentStage = $business->currentBusinessFunnelStage();
        $currentUser = $business->currentUser();

        $funnel = $currentStage->funnel;
        $funnelStage = FunnelStage::findOrFail($status);
        $funnelSubstage = null;
        // $funnelSubstage = $funnelStage->substages()
        //     ->first();

        $business->update([
            'funnel_id'          => $funnel->id,
            'funnel_stage_id'    => $funnelStage->id,
            'funnel_substage_id' => $funnelSubstage?->id,
        ]);

        $business->businessFunnelStages()
            ->create([
                'funnel_id'          => $funnel->id,
                'funnel_stage_id'    => $funnelStage->id,
                'funnel_substage_id' => $funnelSubstage?->id,
                'loss_reason'        => null,
                'business_at'        => now(),
            ]);

        $description = "Etapa do negócio atualizada => {$funnel->name} / Etapa: {$funnelStage->name}";

        if ($funnelSubstage) {
            $description .= " / Sub-etapa: {$funnelSubstage->name}";
        }

        $activityNotification = Notification::create();
        $activity = $activityNotification->activity()
            ->create([
                'user_id'     => auth()->user()->id,
                'description' => $description
            ]);

        $activity->users()
            ->attach($currentUser->id);

        $activity->contacts()
            ->attach($business->contact->id);

        $activity->business()
            ->attach($business->id);

        // Business won
        if ($funnelStage->business_probability === 100) {
            $contact = $business->contact;
            $roleId = 3; // Cliente

            if (!$contact->roles->contains($roleId)) {
                $contact->roles()
                    ->attach($roleId);
            }
        }

        Business::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int $recordId, string $status, array $orderedIds): void
    {
        Business::setNewOrder($orderedIds);
    }

    protected function getEditModalFormSchema(null|int $recordId): array
    {
        return [
            Forms\Components\Select::make('funnel_id')
                ->label(__('Funil'))
                // ->options(
                //     fn(FunnelService $service): array =>
                //     $service->getOptionsByFunnels(),
                // )
                ->relationship(
                    name: 'funnel',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(FunnelService $service, Builder $query): Builder =>
                    $service->getQueryByFunnels(query: $query)
                )
                ->searchable()
                ->preload()
                ->selectablePlaceholder(false)
                ->required()
                ->live()
                ->afterStateUpdated(
                    function (callable $set): void {
                        $set('funnel_stage_id', null);
                        $set('funnel_substage_id', null);
                    }
                )
                ->disabled()
                ->columnSpanFull(),
            Forms\Components\Select::make('funnel_stage_id')
                ->label(__('Etapa do negócio'))
                // ->options(
                //     fn(FunnelService $service, callable $get): array =>
                //     $service->getOptionsByFunnelStagesFunnel(funnelId: $get('funnel_id')),
                // )
                ->relationship(
                    name: 'stage',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(FunnelService $service, Builder $query, callable $get): Builder =>
                    $service->getQueryByFunnelStagesFunnel(query: $query, funnelId: $get('funnel_id')),
                )
                ->searchable()
                ->preload()
                ->selectablePlaceholder(false)
                ->required()
                ->live()
                ->afterStateUpdated(
                    fn(callable $set) =>
                    $set('funnel_substage_id', null),
                )
                ->columnSpanFull(),
            Forms\Components\Select::make('funnel_substage_id')
                ->label(__('Sub-etapa do negócio'))
                // ->options(
                //     fn(FunnelService $service, callable $get): array =>
                //     $service->getOptionsByFunnelSubstagesFunnelStage(funnelStageId: $get('funnel_stage_id')),
                // )
                ->relationship(
                    name: 'substage',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(FunnelService $service, Builder $query, callable $get): Builder =>
                    $service->getQueryByFunnelSubstagesFunnelStage(query: $query, funnelStageId: $get('funnel_stage_id'))
                )
                ->searchable()
                ->preload()
                ->visible(
                    function (FunnelService $service, callable $get): bool {
                        $funnelStage = $service->getFunnelStageData(funnelStageId: $get('funnel_stage_id'));

                        if (empty($get('funnel_id')) || !$funnelStage || $funnelStage->substages->count() === 0) {
                            return false;
                        }

                        return true;
                    }
                )
                ->columnSpanFull(),
            Forms\Components\Select::make('loss_reason')
                ->label(__('Motivo da perda'))
                ->options(LossReasonEnum::class)
                ->selectablePlaceholder(false)
                ->required(
                    function (FunnelService $service, callable $get): bool {
                        $funnelStage = $service->getFunnelStageData(funnelStageId: $get('funnel_stage_id'));

                        if (!$funnelStage || $funnelStage->business_probability !== 0) {
                            return false;
                        }

                        return $funnelStage->business_probability === 0;
                    }
                )
                ->visible(
                    function (FunnelService $service, callable $get): bool {
                        $funnelStage = $service->getFunnelStageData(funnelStageId: $get('funnel_stage_id'));

                        if (!$funnelStage || $funnelStage->business_probability !== 0) {
                            return false;
                        }

                        return $funnelStage->business_probability === 0;
                    }
                )
                ->native(false)
                ->columnSpanFull(),
        ];
    }

    // protected function editRecord($recordId, array $data, array $state): void
    // {
    //     Model::find($recordId)->update([
    //         'phone' => $data['phone']
    //     ]);
    // }

    protected function updateTitle(): void
    {
        $title = BusinessResource::$title ?? BusinessResource::getTitleCasePluralModelLabel();

        // if ($this->currentFunnel) {
        //     $title .= ' / ' . $this->currentFunnel->name;
        // }

        $this->customTitle = $title;
    }

    public function getTitle(): string
    {
        return $this->customTitle;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user->hasAnyRole(['Superadministrador']) ||
            $user->hasPermissionTo(permission: 'Visualizar [CRM] Negócios');
    }
}
