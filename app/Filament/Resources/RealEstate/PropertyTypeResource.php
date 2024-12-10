<?php

namespace App\Filament\Resources\RealEstate;

use App\Enums\DefaultStatusEnum;
use App\Enums\RealEstate\PropertyTypeUsageEnum;
use App\Filament\Resources\RealEstate\PropertyTypeResource\Pages;
use App\Filament\Resources\RealEstate\PropertyTypeResource\RelationManagers;
use App\Models\RealEstate\PropertyType;
use App\Services\RealEstate\PropertyTypeService;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PropertyTypeResource extends Resource
{
    protected static ?string $model = PropertyType::class;

    protected static ?string $modelLabel = 'Tipo de Imóvel';

    protected static ?string $pluralModelLabel = 'Tipos de Imóveis';

    protected static ?string $navigationGroup = 'Imóveis';

    protected static ?int $navigationSort = 98;

    protected static ?string $navigationLabel = 'Tipos de Imóveis';

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->live(debounce: 1000)
                    ->afterStateUpdated(
                        fn (callable $set, ?string $state): ?string =>
                        $set('slug', Str::slug($state))
                    ),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('abbr')
                    ->label(__('Cód. / Abreviatura'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->minLength(2)
                    ->maxLength(255)
                    ->helperText(__('Ex: Apartamento => apt')),
                Forms\Components\Select::make('usage')
                    ->label(__('Tipo para imóvel'))
                    ->options(PropertyTypeUsageEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('subtypes')
                    ->label(__('Subtipo(s) de imóvel(is)'))
                    ->relationship(
                        name: 'subtypes',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder =>
                        $query->where('status', 1),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(DefaultStatusEnum::class)
                    ->default(1)
                    ->required()
                    ->native(false),
            ]);
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
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn (PropertyTypeService $service, Tables\Actions\DeleteAction $action, PropertyType $record) =>
                            $service->preventPropertyTypeDeleteIf(action: $action, propertyType: $record)
                        ),
                ])
                    ->label(__('Ações')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Tipo'))
                ->description(
                    fn(PropertyType $record): string =>
                    $record->abbr,
                )
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('usage')
                ->label(__('Tipo para imóvel'))
                ->badge()
                ->searchable()
                ->searchable(
                    query: fn (PropertyTypeService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByUsage(query: $query, search: $search)
                )
                ->sortable(
                    query: fn (PropertyTypeService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByUsage(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('subtypes.name')
                ->label(__('Subtipo(s) de imóvel(is)'))
                ->badge()
                ->searchable()
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn (PropertyTypeService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn (PropertyTypeService $service, Builder $query, string $direction): Builder =>
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
            Tables\Filters\SelectFilter::make('usage')
                ->label(__('Tipo(s) para imóvel(is)'))
                ->multiple()
                ->options(PropertyTypeUsageEnum::class)
                ->query(
                    fn (PropertyTypeService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUsage(query: $query, data: $data)
                ),
            Tables\Filters\SelectFilter::make('subtypes')
                ->label(__('Subtipo(s) de imóvel(is)'))
                ->multiple()
                ->options(
                    fn (PropertyTypeService $service): array =>
                    $service->getOptionsBySubtypesWhereHasTypes()
                )
                ->query(
                    fn (PropertyTypeService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterBySubtypes(query: $query, data: $data)
                ),
            Tables\Filters\SelectFilter::make('status')
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
                    fn (PropertyTypeService $service, Builder $query, array $data): Builder =>
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
                    fn (PropertyTypeService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Tipo')),
                Infolists\Components\TextEntry::make('abbr')
                    ->label(__('Cód. / Abreviatura')),
                Infolists\Components\TextEntry::make('usage')
                    ->label(__('Tipo para imóvel'))
                    ->badge(),
                Infolists\Components\TextEntry::make('subtypes.name')
                    ->label(__('Subtipo(s) de imóvel(is)'))
                    ->badge()
                    ->visible(
                        fn (?array $state): bool =>
                        !empty($state),
                    )
                    ->columnSpan(2),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePropertyTypes::route('/'),
        ];
    }
}
