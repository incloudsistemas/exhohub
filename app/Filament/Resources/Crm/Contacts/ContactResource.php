<?php

namespace App\Filament\Resources\Crm\Contacts;

use App\Enums\ProfileInfos\GenderEnum;
use App\Filament\Resources\Crm\Contacts\ContactResource\Pages;
use App\Filament\Resources\Crm\Contacts\ContactResource\RelationManagers;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Contacts\Individual;
use App\Models\Crm\Contacts\LegalEntity;
use App\Models\Crm\Contacts\Role;
use App\Services\Crm\Contacts\ContactService;
use App\Services\Crm\Contacts\RoleService;
use App\Services\Crm\SourceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $slug = 'crm/contacts';

    protected static ?string $modelLabel = 'Contato';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                        Tables\Actions\ViewAction::make()
                            ->extraModalFooterActions([
                                Tables\Actions\Action::make('edit')
                                    ->label(__('Editar'))
                                    ->button()
                                    ->url(
                                        function (Contact $record): string {
                                            if ($record->contactable_type === MorphMapByClass(model: Individual::class)) {
                                                return IndividualResource::getUrl('edit', ['record' => $record->contactable]);
                                            }

                                            return LegalEntityResource::getUrl('edit', ['record' => $record->contactable]);
                                        }
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('[CRM] Editar Contatos')
                                    ),
                            ]),
                        Tables\Actions\Action::make('edit-individual')
                            ->label(__('Editar'))
                            ->icon('heroicon-m-pencil-square')
                            ->url(
                                fn (Contact $record): string =>
                                IndividualResource::getUrl('edit', ['record' => $record->contactable]),
                            )
                            ->hidden(
                                fn (Contact $record): bool =>
                                $record->contactable_type !== MorphMapByClass(model: Individual::class) ||
                                !auth()->user()->can('Editar [CRM] Contatos')
                            ),
                        Tables\Actions\Action::make('edit-legal-entity')
                            ->label(__('Editar'))
                            ->icon('heroicon-m-pencil-square')
                            ->url(
                                fn (Contact $record): string =>
                                LegalEntityResource::getUrl('edit', ['record' => $record->contactable]),
                            )
                            ->hidden(
                                fn (Contact $record): bool =>
                                $record->contactable_type !== MorphMapByClass(model: LegalEntity::class) ||
                                !auth()->user()->can('Editar [CRM] Contatos')
                            ),
                    ])
                        ->dropdown(false),
                    Tables\Actions\Action::make('delete-individual')
                        ->label(__('Excluir'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(
                            fn (Contact $record) =>
                            $record->contactable->delete(),
                        )
                        ->hidden(
                            fn (Contact $record): bool =>
                            $record->contactable_type !== MorphMapByClass(model: Individual::class) ||
                            !auth()->user()->can('Deletar [CRM] Contatos')
                        ),
                    Tables\Actions\Action::make('delete-legal-entity')
                        ->label(__('Excluir'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(
                            fn (Contact $record) =>
                            $record->contactable->delete(),
                        )
                        ->hidden(
                            fn (Contact $record): bool =>
                            $record->contactable_type != MorphMapByClass(model: LegalEntity::class) ||
                            !auth()->user()->can('Deletar [CRM] Contatos')
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('create-individual')
                        ->label(__('Criar Pessoa'))
                        ->url(
                            IndividualResource::getUrl('create'),
                        ),
                    Tables\Actions\Action::make('create-legal-entity')
                        ->label(__('Criar Empresa'))
                        ->url(
                            LegalEntityResource::getUrl('create'),
                        ),
                ])
                    ->label(__('Criar Contato'))
                    ->icon('heroicon-m-chevron-down')
                    ->color('primary')
                    ->button()
                    ->hidden(
                        fn (): bool =>
                        !auth()->user()->can('Cadastrar [CRM] Contatos')
                    ),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\SpatieMediaLibraryImageColumn::make('contactable.avatar')
                ->label('')
                ->collection('avatar')
                ->conversion('thumb')
                ->size(45)
                ->circular(),
            Tables\Columns\TextColumn::make('name')
                ->label(__('Nome'))
                ->description(
                    function (Contact $record): ?string {
                        $cpfCnpj = $record->contactable->cpf ?? $record->contactable->cnpj;

                        $description = '';
                        if ($record->contactable_type == 'crm_contact_legal_entities') {
                            $description .= $record->display_contactable_type;
                        }

                        return !empty($cpfCnpj)
                            ? trim($description . ' ' . $cpfCnpj)
                            : $description;
                    }
                )
                ->searchable(
                    query: fn (ContactService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByNameAndContactableCpfOrCnpj(query: $query, search: $search)
                )
                ->sortable(),
            Tables\Columns\TextColumn::make('roles.name')
                ->label(__('Tipo(s)'))
                ->badge()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('email')
                ->label(__('Email'))
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('display_main_phone')
                ->label(__('Telefone'))
                ->searchable(
                    query: fn (ContactService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPhone(query: $query, search: $search)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('source.name')
                ->label(__('Origem'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Captador'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn (ContactService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn (ContactService $service, Builder $query, string $direction): Builder =>
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
            Tables\Filters\SelectFilter::make('contactable_type')
                ->label(__('Tipo de pessoa'))
                ->options([
                    MorphMapByClass(model: Individual::class)  => 'Pessoas',
                    MorphMapByClass(model: LegalEntity::class) => 'Empresas',
                ])
                ->native(false),
            Tables\Filters\SelectFilter::make('roles')
                ->label(__('Tipo(s)'))
                ->relationship(
                    name: 'roles',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (RoleService $service, Builder $query): Builder =>
                    $service->getQueryByRolesWhereHasContacts(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('sources')
                ->label(__('Origem(s)'))
                ->relationship(
                    name: 'source',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (ContactService $service, Builder $query): Builder =>
                    $service->getQueryBySourcesWhereHasContacts(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('owners')
                ->label(__('Captador(es)'))
                ->relationship(
                    name: 'owner',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (ContactService $service, Builder $query): Builder =>
                    $service->getQueryByOwnersWhereHasContacts(query: $query)
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
                    fn (ContactService $service, Builder $query, array $data): Builder =>
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
                    fn (ContactService $service, Builder $query, array $data): Builder =>
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
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('contactable.avatar')
                                    ->label(__('Avatar'))
                                    ->hiddenLabel()
                                    ->collection('avatar')
                                    ->conversion('thumb')
                                    ->visible(
                                        fn (?array $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('Nome')),
                                Infolists\Components\TextEntry::make('roles.name')
                                    ->label(__('Tipo(s)'))
                                    ->badge()
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('email')
                                    ->label(__('Email'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_additional_emails')
                                    ->label(__('Emails adicionais'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('display_main_phone_with_name')
                                    ->label(__('Telefone'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_additional_phones')
                                    ->label(__('Telefones adicionais'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('contactable.cnpj')
                                    ->label(__('CNPJ'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contactable.url')
                                    ->label(__('URL do site'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contactable.cpf')
                                    ->label(__('CPF'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contactable.rg')
                                    ->label(__('RG'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contactable.gender')
                                    ->label(__('Sexo'))
                                    ->visible(
                                        fn (?GenderEnum $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('contactable.display_birth_date')
                                    ->label(__('Dt. nascimento'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('complement')
                                    ->label(__('Sobre'))
                                    ->visible(
                                        fn (?string $state): bool =>
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
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Anexos'))
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('contactable.attachments')
                                    ->label('Arquivo(s)')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label(__('Nome')),
                                        Infolists\Components\TextEntry::make('mime_type')
                                            ->label(__('Mime')),
                                        Infolists\Components\TextEntry::make('size')
                                            ->label(__('Tamanho'))
                                            ->state(
                                                fn (Media $record): string =>
                                                AbbrNumberFormat($record->size),
                                            )
                                            ->hint(
                                                fn (Media $record): HtmlString =>
                                                new HtmlString('<a href="' . url('storage/' . $record->file_name) . '" target="_blank">Download</a>')
                                            )
                                            ->hintIcon('heroicon-s-arrow-down-tray')
                                            ->hintColor('primary'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                            ->visible(
                                fn (Contact $record): bool =>
                                $record->contactable->attachments->count() > 0
                            ),
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
            'index'  => Pages\ListContacts::route('/'),
            // 'create' => Pages\CreateContact::route('/create'),
            // 'edit'   => Pages\EditContact::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->with('contactable')
            ->whereHas('contactable');

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

            return $query->whereIn('user_id', $teamUserIds);
        }

        return $query->where('user_id', $user->id);
    }
}
