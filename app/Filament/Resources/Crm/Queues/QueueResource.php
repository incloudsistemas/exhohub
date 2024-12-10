<?php

namespace App\Filament\Resources\Crm\Queues;

use App\Enums\Crm\Queues\DistributionSettingsEnum;
use App\Enums\Crm\Queues\PropertiesSettingsEnum;
use App\Enums\Crm\Queues\QueueRoleEnum;
use App\Enums\Crm\Queues\UsersSettingsEnum;
use App\Enums\DefaultStatusEnum;
use App\Filament\Resources\Crm\Queues\QueueResource\Pages;
use App\Filament\Resources\Crm\Queues\QueueResource\RelationManagers;
use App\Models\Crm\Queues\Queue;
use App\Services\Crm\Funnels\FunnelService;
use App\Services\Crm\Queues\QueueService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $slug = 'crm/queues';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Fila';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationParentItem = 'Negócios';

    protected static ?int $navigationSort = 98;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getGeneralInfosFormSection(),
                static::getSettingsFormSection(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre a fila.'))
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome da fila'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(__('Descrição'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->label(__('Ordem'))
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(100),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(DefaultStatusEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getSettingsFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Configurações da Fila'))
            ->description(__('Defina as integrações e demais configurções específicas da fila.'))
            ->schema([
                Forms\Components\Select::make('role')
                    ->label(__('Integração da fila'))
                    ->options(QueueRoleEnum::class)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(
                        function (callable $set, ?string $state): void {
                            // 3 - Meta Ads
                            if ((int) $state === 3) {
                                $set('properties_settings', 1); // 1 - Customizar os imóveis
                            }

                            $set('distribution_settings', '');
                        }
                    )
                    ->disabled(
                        fn(string $operation): bool =>
                        $operation === 'edit'
                    ),
                Forms\Components\Select::make('properties_settings')
                    ->label(__('Configurações dos imóveis'))
                    ->options(PropertiesSettingsEnum::class)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false)
                    ->disabled(
                        fn(callable $get): bool =>
                        (int) $get('role') === 3, // 3 - Meta Ads
                    )
                    ->dehydrated(),
                Forms\Components\TextInput::make('account_id')
                    ->label(__('ID. da página'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->visible(
                        fn(callable $get): bool =>
                        (int) $get('role') === 3, // 3 - Meta Ads
                    ),
                Forms\Components\TextInput::make('campaign_id')
                    ->label(__('ID. do formulário'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->visible(
                        fn(callable $get): bool =>
                        (int) $get('role') === 3, // 3 - Meta Ads
                    ),
                Forms\Components\Select::make('users_settings')
                    ->label(__('Configurações dos usuários/corretores'))
                    ->options(UsersSettingsEnum::class)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('distribution_settings')
                    ->label(__('Configurações de distribuição'))
                    ->helperText(
                        fn(?int $state): string =>
                        match ($state) {
                            1       => 'Lead vai para o próprio captador do imóvels',
                            2       => 'Round Robin - Distribuição alternada entre os corretores disponíveis',
                            3       => 'Corretores com melhores performances recebem leads prioritariamente',
                            4       => 'Lead vai para corretores com menor carga de trabalho atual',
                            default => '',
                        }
                    )
                    ->options(function (callable $get) {
                        $options = DistributionSettingsEnum::getOptions();

                        // 3 - Meta Ads
                        if ((int) $get('role') === 3) {
                            unset($options[1]);
                        }

                        return $options;
                    })
                    ->selectablePlaceholder(false)
                    ->live()
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('funnel')
                    ->label(__('Funil'))
                    ->helperText('Deseja relacionar a fila a um funil específico?')
                    ->relationship(
                        name: 'funnel',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(FunnelService $service, Builder $query): Builder =>
                        $service->getQueryByFunnels(query: $query)
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort(column: 'id', direction: 'desc')
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
                                        fn(Queue $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('Editar [CRM] Lista')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(QueueService $service, Tables\Actions\DeleteAction $action, Queue $record) =>
                            $service->preventQueueDeleteIf(action: $action, queue: $record)
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
            Tables\Columns\TextColumn::make('name')
                ->label(__('Fila'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('role')
                ->label(__('Integração'))
                ->badge()
                ->searchable(
                    query: fn(QueueService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByRole(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(QueueService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByRole(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('funnel.name')
                ->label(__('Funil'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('order')
                ->label(__('Ordem'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(QueueService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(QueueService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByStatus(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Cadastro'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: false),
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
            Tables\Filters\SelectFilter::make('role')
                ->label(__('Integração(ões)'))
                ->multiple()
                ->options(QueueRoleEnum::class),
            Tables\Filters\SelectFilter::make('status')
                ->label(__('Status'))
                ->multiple()
                ->options(DefaultStatusEnum::class),
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
                    fn(QueueService $service, Builder $query, array $data): Builder =>
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
                    fn(QueueService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Fila')),
                Infolists\Components\TextEntry::make('description')
                    ->label(__('Descrição'))
                    ->visible(
                        fn(?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('role')
                    ->label(__('Integração da fila'))
                    ->badge(),
                Infolists\Components\TextEntry::make('properties_settings')
                    ->label(__('Configurações dos imóveis')),
                Infolists\Components\TextEntry::make('users_settings')
                    ->label(__('Configurações dos usuários/corretores')),
                Infolists\Components\TextEntry::make('distribution_settings')
                    ->label(__('Configurações de distribuição')),
                Infolists\Components\TextEntry::make('funnel.name')
                    ->label(__('Funil'))
                    ->visible(
                        fn(?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('order')
                    ->label(__('Ordem')),
                Infolists\Components\Grid::make(['default' => 3])
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('Cadastro'))
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label(__('Últ. atualização'))
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PropertiesRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\AgenciesRelationManager::class,
            RelationManagers\TeamsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'edit'   => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
