<?php

namespace App\Filament\Resources\System;

use App\Enums\DefaultStatusEnum;
use App\Filament\Resources\System\TeamResource\Pages;
use App\Filament\Resources\System\TeamResource\RelationManagers;
use App\Models\System\Team;
use App\Services\System\AgencyService;
use App\Services\System\TeamService;
use App\Services\System\UserService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    // protected static ?string $slug = 'teams';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Equipes';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $navigationParentItem = 'Agências';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getGeneralInfosFormSection(),
                static::getUsersFormSection(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o time.'))
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Equipe'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->live(debounce: 1000)
                    ->afterStateUpdated(
                        fn(callable $set, ?string $state): ?string =>
                        $set('slug', Str::slug($state))
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('agency_id')
                    ->label(__('Agência'))
                    ->relationship(
                        name: 'agency',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(AgencyService $service, Builder $query): Builder =>
                        $service->getQueryByActiveAgencies(query: $query)
                    )
                    ->searchable()
                    ->preload()
                    // ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('complement')
                    ->label(__('Sobre'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('Avatar'))
                    ->helperText(__('Tipos de arquivo permitidos: .png, .jpg, .jpeg, .gif. // Máx. 500x500px // 5 mb.'))
                    ->collection('avatar')
                    ->image()
                    // ->responsiveImages()
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('name'))),
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('500')
                    ->imageResizeTargetHeight('500')
                    ->imageResizeUpscale(false)
                    ->maxSize(5120),
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

    protected static function getUsersFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Usuários da Equipe'))
            ->description(__('Gerencie os membros da equipe e atribua papéis.'))
            ->schema([
                Forms\Components\Select::make('directors')
                    ->label(__('Diretor(es)'))
                    ->multiple()
                    ->getSearchResultsUsing(
                        fn(UserService $service, string $search): array =>
                        $service->getUserByRolesOptionsBySearch(search: $search, roles: [4]), // 4 - directors
                    )
                    ->getOptionLabelsUsing(
                        fn(UserService $service, array $values): array =>
                        $service->getUserOptionsLabel(values: $values),
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Forms\Components\Select::make('managers')
                    ->label(__('Gerente(s)'))
                    ->multiple()
                    ->getSearchResultsUsing(
                        fn(UserService $service, string $search): array =>
                        $service->getUserByRolesOptionsBySearch(search: $search, roles: [5]), // 5 - managers
                    )
                    ->getOptionLabelsUsing(
                        fn(UserService $service, array $values): array =>
                        $service->getUserOptionsLabel(values: $values),
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Forms\Components\Select::make('realtors')
                    ->label(__('Corretor(es)'))
                    ->multiple()
                    ->getSearchResultsUsing(
                        fn(UserService $service, string $search): array =>
                        $service->getUserByRolesOptionsBySearch(search: $search, roles: [6]), // 6 - realtors
                    )
                    ->getOptionLabelsUsing(
                        fn(UserService $service, array $values): array =>
                        $service->getUserOptionsLabel(values: $values),
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort(column: 'created_at', direction: 'desc')
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
                                        fn(Team $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('Editar Times')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(TeamService $service, Tables\Actions\DeleteAction $action, Team $record) =>
                            $service->preventTeamDeleteIf(action: $action, team: $record)
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
            Tables\Columns\SpatieMediaLibraryImageColumn::make('avatar')
                ->label('')
                ->collection('avatar')
                ->conversion('thumb')
                ->size(45)
                ->circular(),
            Tables\Columns\TextColumn::make('name')
                ->label(__('Equipe'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('agency.name')
                ->label(__('Agência'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('directors.name')
                ->label(__('Diretor(es)'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(TeamService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(TeamService $service, Builder $query, string $direction): Builder =>
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
            Tables\Filters\SelectFilter::make('agencies')
                ->label(__('Agência(s)'))
                ->options(
                    fn(TeamService $service): array =>
                    $service->getOptionsByAgenciesWhereHasTeams(),
                )
                ->query(
                    fn(TeamService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByAgencies(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('directors')
                ->label(__('Diretor(es)'))
                ->options(
                    fn(TeamService $service): array =>
                    $service->getOptionsByDirectorsWhereHasTeams(),
                )
                ->query(
                    fn(TeamService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByDirectors(query: $query, data: $data)
                )
                ->multiple(),
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
                    fn(UserService $service, Builder $query, array $data): Builder =>
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
                    fn(UserService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\SpatieMediaLibraryImageEntry::make('avatar')
                    ->label(__('Avatar'))
                    ->hiddenLabel()
                    ->collection('avatar')
                    ->conversion('thumb')
                    ->circular()
                    ->visible(
                        fn (?array $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Equipe')),
                Infolists\Components\TextEntry::make('agency.name')
                    ->label(__('Agência'))
                    ->badge()
                    ->visible(
                        fn(array|string|null $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('directors.name')
                    ->label(__('Diretor(es)'))
                    ->visible(
                        fn(array|string|null $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('managers.name')
                    ->label(__('Gerente(s)'))
                    ->visible(
                        fn(array|string|null $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('realtors.name')
                    ->label(__('Corretor(es)'))
                    ->visible(
                        fn(array|string|null $state): bool =>
                        !empty($state),
                    )
                    ->columnSpanFull(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit'   => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
