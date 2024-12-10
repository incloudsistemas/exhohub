<?php

namespace App\Filament\Resources\System;

use App\Filament\Resources\System\PermissionResource\Pages;
use App\Filament\Resources\System\PermissionResource\RelationManagers;
use App\Models\System\Permission;
use App\Services\System\PermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    // protected static ?string $slug = 'permissions';

    // protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Permissão';

    protected static ?string $pluralModelLabel = 'Permissões';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $navigationParentItem = 'Usuários';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('roles')
                    ->label(__('Nível(is) de acesso(s)'))
                    ->multiple()
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(PermissionService $service, Builder $query): Builder =>
                        $service->getQueryAvoidingRoles(query: $query)
                    )
                    ->preload()
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('Nível(is) de acesso(s)'))
                    ->badge()
                    ->searchable()
                    ->sortable()
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
                Tables\Filters\SelectFilter::make('roles')
                    ->label(__('Nível(is) de acesso(s)'))
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(PermissionService $service, Builder $query): Builder =>
                        $service->getQueryAvoidingRoles(query: $query)
                    )
                    ->multiple()
                    ->preload(),
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
                        fn(PermissionService $service, Builder $query, array $data): Builder =>
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
                        fn(PermissionService $service, Builder $query, array $data): Builder =>
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
                    Tables\Actions\DeleteAction::make(),
                ]),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Nome')),
                Infolists\Components\TextEntry::make('roles.name')
                    ->label(__('Nível(is) de acesso(s)'))
                    ->badge()
                    ->columnSpan(2),
                Infolists\Components\TextEntry::make('created_at')
                    ->label(__('Cadastro'))
                    ->dateTime('d/m/Y H:i'),
                Infolists\Components\TextEntry::make('updated_at')
                    ->label(__('Últ. atualização'))
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(3);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePermissions::route('/'),
        ];
    }
}
