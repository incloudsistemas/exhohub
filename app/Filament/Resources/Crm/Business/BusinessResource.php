<?php

namespace App\Filament\Resources\Crm\Business;

use App\Enums\Activities\LossReasonEnum;
use App\Enums\Crm\Business\PriorityEnum;
use App\Filament\Resources\Crm\Business\BusinessResource\Pages;
use App\Filament\Resources\Crm\Business\BusinessResource\RelationManagers;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Funnels\Funnel;
use App\Services\Crm\Business\BusinessService;
use App\Services\Crm\Contacts\ContactService;
use App\Services\Crm\Funnels\FunnelService;
use App\Services\Crm\SourceService;
use App\Services\System\UserService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $slug = 'crm/business';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Negócio';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getGeneralInfosFormSection(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o negócio.'))
            ->schema([
                // Forms\Components\TextInput::make('name')
                //     ->label(__('Nome do negócio'))
                //     ->required()
                //     ->minLength(2)
                //     ->maxLength(255)
                //     ->columnSpanFull(),
                Forms\Components\Select::make('contact_id')
                    ->label(__('Contato'))
                    ->searchable()
                    ->preload()
                    ->selectablePlaceholder(false)
                    ->getSearchResultsUsing(
                        fn(ContactService $service, string $search): array =>
                        $service->getContactOptionsBySearch(search: $search),
                    )
                    ->getOptionLabelUsing(
                        fn(ContactService $service, int $value): string =>
                        $service->getContactOptionLabel(value: $value),
                    )
                    ->when(
                        auth()->user()->can('Cadastrar [CRM] Contatos'),
                        fn(Forms\Components\Select $component): Forms\Components\Select =>
                        $component->suffixAction(
                            fn(ContactService $service): Forms\Components\Actions\Action =>
                            $service->getQuickCreateActionByContacts(field: 'contact_id'),
                        ),
                    )
                    ->required()
                    ->columnSpanFull(),
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
                    ->disabled(
                        fn(string $operation): bool =>
                        $operation === 'edit'
                    )
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
                Forms\Components\TextInput::make('price')
                    ->label(
                        function (FunnelService $service, callable $get): string {
                            $funnelStage = $service->getFunnelStageData(funnelStageId: $get('funnel_stage_id'));

                            if (!$funnelStage || $funnelStage['business_probability'] !== 100) {
                                return __('VGV em potencial');
                            }

                            return $funnelStage['business_probability'] != 100
                                ? __('VGV em potencial')
                                : __('VGV');
                        }
                    )
                    // ->numeric()
                    ->prefix('R$')
                    ->mask(
                        Support\RawJs::make(<<<'JS'
                            $money($input, ',')
                        JS)
                    )
                    ->placeholder('0,00')
                    ->required(
                        function (FunnelService $service, callable $get): bool {
                            $funnelStage = $service->getFunnelStageData(funnelStageId: $get('funnel_stage_id'));

                            if (!$funnelStage || $funnelStage['business_probability'] !== 100) {
                                return false;
                            }

                            return $funnelStage['business_probability'] === 100;
                        }
                    )
                    ->maxValue(42949672.95),
                Forms\Components\Textarea::make('description')
                    ->label(__('Descrição/observações do negócio'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('source_id')
                    ->label(__('Origem do negócio'))
                    ->options(
                        fn(SourceService $service): array =>
                        $service->getOptionsByActiveSources(),
                    )
                    ->searchable()
                    ->preload()
                    ->when(
                        auth()->user()->can('Cadastrar [CRM] Origens dos Contatos/Negócios'),
                        fn(Forms\Components\Select $component): Forms\Components\Select =>
                        $component->suffixAction(
                            fn(SourceService $service): Forms\Components\Actions\Action =>
                            $service->getQuickCreateActionBySources(field: 'contact.source_id'),
                        ),
                    ),
                Forms\Components\Select::make('user_collector_id')
                    ->label(__('Responsável'))
                    ->getSearchResultsUsing(
                        fn(UserService $service, string $search): array =>
                        $service->getUserOptionsBySearch(search: $search),
                    )
                    ->getOptionLabelUsing(
                        fn(UserService $service, int $value): string =>
                        $service->getUserOptionLabel(value: $value),
                    )
                    ->searchable()
                    ->preload()
                    ->default(auth()->user()->id)
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->label(__('Prioridade'))
                    ->options(PriorityEnum::class)
                    ->native(false),
                Forms\Components\DateTimePicker::make('business_at')
                    ->label(__('Dt. competência'))
                    ->helperText(__('Data em que o negócio se iniciou.'))
                    ->displayFormat('d/m/Y H:i')
                    ->seconds(false)
                    ->default(now())
                    ->required(),
            ])
            ->columns(2)
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort(column: 'business_at', direction: 'desc')
            ->filters(static::getTableFilters(), layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make()
                            ->extraModalFooterActions([
                                Tables\Actions\Action::make('edit')
                                    ->label(__('Editar'))
                                    ->button()
                                    ->url(
                                        fn(Business $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('Editar [CRM] Negócios')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(BusinessService $service, Tables\Actions\DeleteAction $action, Business $record) =>
                            $service->preventBusinessDeleteIf(action: $action, business: $record)
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label(__('#ID'))
                ->searchable()
                ->sortable(),
            // Tables\Columns\TextColumn::make('name')
            //     ->label(__('Negócio'))
            //     ->searchable()
            //     ->sortable(),
            Tables\Columns\SpatieMediaLibraryImageColumn::make('contact.contactable.avatar')
                ->label('')
                ->collection('avatar')
                ->conversion('thumb')
                ->size(45)
                ->circular(),
            Tables\Columns\TextColumn::make('contact.name')
                ->label(__('Contato'))
                ->description(
                    fn(Business $record): ?string =>
                    $record->contact->contactable->cpf ?? $record->contact->contactable->cnpj
                )
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('contact.email')
                ->label(__('Email'))
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('contact.display_main_phone')
                ->label(__('Telefone'))
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('funnel.name')
                ->label(__('Funil'))
                ->badge()
                // ->searchable()
                // ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('stage.name')
                ->label(__('Etapa'))
                ->description(
                    fn(Business $record): ?string =>
                    $record->substage?->name
                )
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('display_price')
                ->label(__('VGV (R$)'))
                // ->sortable(
                //     query: fn (BusinessService $service, Builder $query, string $direction): Builder =>
                //     $service->tableSortByPrice(query: $query, direction: $direction)
                // )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('display_current_user')
                ->label(__('Responsável'))
                // ->searchable()
                // ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Captador'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('priority')
                ->label(__('Prioridade'))
                ->badge()
                ->searchable(
                    query: fn(BusinessService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPriority(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(BusinessService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByPriority(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('business_at')
                ->label(__('Dt. competência'))
                ->dateTime('d/m/Y H:i')
                ->searchable()
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Cadastro'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('Últ. atualização'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('funnel_stage_substages')
                ->label(__('Funis'))
                ->form([
                    // Forms\Components\Grid::make(['default' => 2])
                    //     ->schema([
                    //         Forms\Components\Select::make('funnel')
                    //             ->label(__('Funil'))
                    //             ->options(
                    //                 fn(BusinessService $service, callable $get): array =>
                    //                 $service->getOptionsByFunnelsWhereHasBusiness(),
                    //             )
                    //             // ->multiple()
                    //             ->default(
                    //                 function (BusinessService $service): int {

                    //                     // $funnelId = request()->route('funnel');
                    //                     // $defaultFunnel = Funnel::find($funnelId);

                    //                     // if (!$defaultFunnel) {
                    //                     //     $defaultFunnel = $service->getBusinessDefaultFunnel();
                    //                     // }

                    //                     // return $defaultFunnel->id;
                    //                 }
                    //             )
                    //             ->searchable()
                    //             ->preload()
                    //             ->selectablePlaceholder(false)
                    //             ->disabled()
                    //             ->live()
                    //             ->afterStateUpdated(
                    //                 function (callable $set): void {
                    //                     $set('stage', null);
                    //                     $set('substages', null);
                    //                 }
                    //             ),
                    //     ]),
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\Select::make('stage')
                                ->label(__('Etapa'))
                                ->options(
                                    function (FunnelService $service): array {
                                        $funnel = self::getCurrentFunnel();
                                        return $service->getOptionsByFunnelStagesFunnel(funnelId: $funnel?->id);
                                    }
                                )
                                // ->multiple()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(
                                    fn(callable $set) =>
                                    $set('substages', null),
                                ),
                            Forms\Components\Select::make('substages')
                                ->label(__('Sub-etapa(s)'))
                                ->options(
                                    fn(FunnelService $service, callable $get): array =>
                                    $service->getOptionsByFunnelSubstagesFunnelStage(funnelStageId: $get('stage')),
                                )
                                ->multiple()
                                ->searchable()
                                ->preload(),
                        ]),
                ])
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByFunnelStageAndSubstages(query: $query, data: $data)
                )
                ->columnSpanFull(),
            Tables\Filters\SelectFilter::make('user_collectors')
                ->label(__('Responsável(is)'))
                ->options(
                    fn(BusinessService $service): array =>
                    $service->tableFilterGetOptionsByOwners(),
                )
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterGetQueryByOwners(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('owners')
                ->label(__('Captador(es)'))
                ->options(
                    fn(BusinessService $service): array =>
                    $service->tableFilterGetOptionsByOwners(),
                )
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterGetQueryByOwners(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('priority')
                ->label(__('Prioridade(s)'))
                ->multiple()
                ->options(PriorityEnum::class),
            Tables\Filters\Filter::make('business_at')
                ->label(__('Dt. competência'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\DatePicker::make('business_from')
                                ->label(__('Dt. competência de'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('business_until')) && $state > $get('business_until')) {
                                            $set('business_until', $state);
                                        }
                                    }
                                ),
                            Forms\Components\DatePicker::make('business_until')
                                ->label(__('Dt. competência até'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('business_from')) && $state < $get('business_from')) {
                                            $set('business_from', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByBusinessAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('created_at')
                ->label(__('Cadastro'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\DatePicker::make('created_from')
                                ->label(__('Cadastro de'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('created_until')) && $state > $get('created_until')) {
                                            $set('created_until', $state);
                                        }
                                    }
                                ),
                            Forms\Components\DatePicker::make('created_until')
                                ->label(__('Cadastro até'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('created_from')) && $state < $get('created_from')) {
                                            $set('created_from', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByCreatedAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('created_at')
                ->label(__('Cadastro'))
                ->form([
                    Forms\Components\Grid::make([
                        'default' => 1,
                        'md'      => 2,
                    ])
                        ->schema([
                            Forms\Components\DatePicker::make('created_from')
                                ->label(__('Cadastro de'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('created_until')) && $state > $get('created_until')) {
                                            $set('created_until', $state);
                                        }
                                    }
                                ),
                            Forms\Components\DatePicker::make('created_until')
                                ->label(__('Cadastro até'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('created_from')) && $state < $get('created_from')) {
                                            $set('created_from', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByCreatedAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('updated_at')
                ->label(__('Últ. atualização'))
                ->form([
                    Forms\Components\Grid::make([
                        'default' => 1,
                        'md'      => 2,
                    ])
                        ->schema([
                            Forms\Components\DatePicker::make('updated_from')
                                ->label(__('Últ. atualização de'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('updated_until')) && $state > $get('updated_until')) {
                                            $set('updated_until', $state);
                                        }
                                    }
                                ),
                            Forms\Components\DatePicker::make('updated_until')
                                ->label(__('Últ. atualização até'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('updated_from')) && $state < $get('updated_from')) {
                                            $set('updated_from', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(BusinessService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Label')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make(__('Infos. Gerais'))
                            ->schema([
                                // Infolists\Components\TextEntry::make('id')
                                //     ->label(__('#ID')),
                                // Infolists\Components\TextEntry::make('name')
                                //     ->label(__('Negócio'))
                                //     ->visible(
                                //         fn(?string $state): bool =>
                                //         !empty($state),
                                //     ),
                                Infolists\Components\TextEntry::make('contact.name')
                                    ->label(__('Negócio'))
                                    ->helperText(
                                        fn(Business $record): string =>
                                        "#{$record->id}"
                                    ),
                                Infolists\Components\TextEntry::make('description')
                                    ->label(__('Descrição'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('funnel.name')
                                    ->label(__('Funil'))
                                    ->badge(),
                                Infolists\Components\TextEntry::make('stage.name')
                                    ->label(__('Etapa'))
                                    ->badge()
                                    ->helperText(
                                        fn(Business $record): ?string =>
                                        $record->substage?->name,
                                    ),
                                // Infolists\Components\TextEntry::make('substage.name')
                                //     ->label(__('Sub-etapa'))
                                //     ->visible(
                                //         fn(?string $state): bool =>
                                //         !empty($state),
                                //     ),
                                Infolists\Components\TextEntry::make('display_current_user')
                                    ->label(__('Responsável')),
                                Infolists\Components\TextEntry::make('owner.name')
                                    ->label(__('Captador')),
                                Infolists\Components\TextEntry::make('priority')
                                    ->label(__('Prioridade'))
                                    ->badge()
                                    ->visible(
                                        fn($state): bool =>
                                        isset($state) && !empty($state->value)
                                    ),
                                Infolists\Components\Grid::make(['default' => 3])
                                    ->schema([
                                        Infolists\Components\TextEntry::make('business_at')
                                            ->label(__('Dt. competência'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label(__('Cadastro'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('updated_at')
                                            ->label(__('Últ. atualização'))
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Infos. do Contato'))
                            ->schema([
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('contact.contactable.avatar')
                                    ->label(__('Avatar'))
                                    ->hiddenLabel()
                                    ->collection('avatar')
                                    ->conversion('thumb')
                                    ->visible(
                                        fn (?array $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.name')
                                    ->label(__('Nome')),
                                    Infolists\Components\TextEntry::make('contact.roles.name')
                                    ->label(__('Tipo(s)'))
                                    ->badge()
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('contact.email')
                                    ->label(__('Email'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.display_additional_emails')
                                    ->label(__('Emails adicionais'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('contact.display_main_phone_with_name')
                                    ->label(__('Telefone'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.display_additional_phones')
                                    ->label(__('Telefones adicionais'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('contact.contactable.cnpj')
                                    ->label(__('CNPJ'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.contactable.url')
                                    ->label(__('URL do site'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.contactable.cpf')
                                    ->label(__('CPF'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.contactable.rg')
                                    ->label(__('RG'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.contactable.gender')
                                    ->label(__('Sexo'))
                                    ->visible(
                                        fn (?GenderEnum $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.contactable.display_birth_date')
                                    ->label(__('Dt. nascimento'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contact.complement')
                                    ->label(__('Sobre'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    )
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBusinesses::route('/funnel/{funnel?}'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit'   => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery();

        $funnel = self::getCurrentFunnel();

        if (isset($funnel)) {
            $query->whereHas('businessFunnelStages', function (Builder $query) use ($funnel): Builder {
                return $query->where('funnel_id', $funnel->id)
                    ->orderBy('business_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            });
        }

        if ($user->hasAnyRole(['Superadministrador', 'Administrador'])) {
            return $query;
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
            });
        }

        return $query->whereHas('users', function (Builder $query) use ($user): Builder {
            return $query->where('user_id', $user->id)
                ->orderBy('business_at', 'desc')
                ->limit(1);
        });
    }

    protected static function getCurrentFunnel(): ?Funnel
    {
        $funnelId = request()->route('funnel');
        $currentFunnel = Funnel::find($funnelId);

        if (!$currentFunnel) {
            $service = app(BusinessService::class);
            $currentFunnel = $service->getBusinessDefaultFunnel();
        }

        return $currentFunnel;
    }
}
