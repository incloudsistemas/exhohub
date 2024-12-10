<?php

namespace App\Filament\Resources\Crm\Contacts;

use App\Enums\DefaultStatusEnum;
use App\Filament\Resources\Crm\Contacts\RoleResource\Pages;
use App\Filament\Resources\Crm\Contacts\RoleResource\RelationManagers;
use App\Models\Crm\Contacts\Role;
use App\Services\Crm\Contacts\RoleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Tipo de Contato';

    protected static ?string $pluralModelLabel = 'Tipos de Contatos';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationParentItem = 'Contatos';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Tipos de Contatos';

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
                        fn(callable $set, ?string $state): ?string =>
                        $set('slug', Str::slug($state))
                    ),
                Forms\Components\TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(DefaultStatusEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nome'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->searchable(
                        query: fn(RoleService $service, Builder $query, string $search): Builder =>
                        $service->tableSearchByStatus(query: $query, search: $search)
                    )
                    ->sortable(
                        query: fn(RoleService $service, Builder $query, string $direction): Builder =>
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
            ])
            ->defaultSort(column: 'created_at', direction: 'desc')
            ->filters([
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
                        fn(RoleService $service, Builder $query, array $data): Builder =>
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
                        fn(RoleService $service, Builder $query, array $data): Builder =>
                        $service->tableFilterByUpdatedAt(query: $query, data: $data)
                    ),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
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
                            fn(RoleService $service, Tables\Actions\DeleteAction $action, Role $record) =>
                            $service->preventRoleDeleteIf(action: $action, role: $record)
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Nome')),
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
            'index' => Pages\ManageRoles::route('/'),
        ];
    }
}
