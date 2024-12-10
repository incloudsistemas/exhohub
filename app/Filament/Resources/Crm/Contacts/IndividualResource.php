<?php

namespace App\Filament\Resources\Crm\Contacts;

use App\Enums\ProfileInfos\GenderEnum;
use App\Enums\ProfileInfos\UserStatusEnum;
use App\Filament\Resources\Crm\Contacts\IndividualResource\Pages;
use App\Filament\Resources\Crm\Contacts\IndividualResource\RelationManagers;
use App\Filament\Resources\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\RelationManagers\MediaRelationManager;
use App\Models\Crm\Contacts\Individual;
use App\Services\Crm\Contacts\ContactService;
use App\Services\Crm\Contacts\IndividualService;
use App\Services\Crm\Contacts\LegalEntityService;
use App\Services\Crm\Contacts\RoleService;
use App\Services\Crm\SourceService;
use App\Services\System\UserService;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IndividualResource extends Resource
{
    protected static ?string $model = Individual::class;

    // protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Pessoa';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationParentItem = 'Contatos';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getGeneralInfosFormSection(),
                static::getAdditionalInfosFormSection(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o contato.'))
            ->schema([
                Forms\Components\TextInput::make('contact.name')
                    ->label(__('Nome'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make(__('Tipos de contato'))
                    ->schema([
                        Forms\Components\CheckboxList::make('contact.roles')
                            ->hiddenLabel()
                            ->options(
                                fn(RoleService $service): array =>
                                $service->getOptionsByActiveContactRoles()
                            )
                            ->columns(6)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ])
                    ->columns(6),
                Forms\Components\TextInput::make('contact.email')
                    ->label(__('Email'))
                    ->email()
                    // ->required()
                    ->rules([
                        function (IndividualService $service, ?Individual $record): Closure {
                            return function (
                                string $attribute,
                                string $state,
                                Closure $fail
                            ) use ($service, $record): void {
                                $service->validateEmail(
                                    record: $record,
                                    attribute: $attribute,
                                    state: $state,
                                    fail: $fail
                                );
                            };
                        },
                    ])
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('contact.additional_emails')
                    ->label(__('Email(s) adicional(is)'))
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label(__('Tipo de email'))
                            ->helperText(__('Nome identificador. Ex: Pessoal, Trabalho...'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->datalist([
                                'Pessoal',
                                'Trabalho',
                                'Outros'
                            ])
                            ->autocomplete(false),
                    ])
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['email'] ?? null
                    )
                    ->addActionLabel(__('Adicionar email'))
                    ->defaultItems(0)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Repeater::make('contact.phones')
                    ->label(__('Telefone(s) de contato'))
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label(__('Nº do telefone'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $input.length === 14 ? '(99) 9999-9999' : '(99) 99999-9999'
                                JS)
                            )
                            ->live(onBlur: true)
                            ->rules([
                                function (IndividualService $service, ?Individual $record): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ) use ($service, $record): void {
                                        $service->validatePhone(
                                            record: $record,
                                            attribute: $attribute,
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label(__('Tipo de contato'))
                            ->helperText(__('Nome identificador. Ex: Celular, Whatsapp, Casa, Trabalho...'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->datalist([
                                'Celular',
                                'Whatsapp',
                                'Casa',
                                'Trabalho',
                                'Outros'
                            ])
                            ->autocomplete(false),
                    ])
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['number'] ?? null
                    )
                    ->addActionLabel(__('Adicionar telefone'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn(Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Select::make('legal_entities')
                    ->label(__('Empresas relacionadas'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(
                        fn(LegalEntityService $service, string $search): array =>
                        $service->getLegalEntityOptionsBySearch(search: $search),
                    )
                    ->getOptionLabelsUsing(
                        fn(LegalEntityService $service, array $values): array =>
                        $service->getLegalEntityOptionsLabel(values: $values),
                    )
                    ->when(
                        auth()->user()->can('Cadastrar [CRM] Contatos'),
                        fn(Forms\Components\Select $component): Forms\Components\Select =>
                        $component->suffixAction(
                            fn(LegalEntityService $service): Forms\Components\Actions\Action =>
                            $service->getQuickCreateActionByContactLegalEntities(field: 'legal_entities', multiple: true),
                        ),
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('contact.source_id')
                    ->label(__('Origem da captação'))
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
                Forms\Components\Select::make('contact.user_id')
                    ->label(__('Captador'))
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
                    ->default(auth()->user()->id),
                Forms\Components\Select::make('contact.status')
                    ->label(__('Status'))
                    ->options(UserStatusEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAdditionalInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Complementares'))
            ->description(__('Forneça informações adicionais relevantes.'))
            ->schema([
                Forms\Components\TextInput::make('cpf')
                    ->label(__('CPF'))
                    ->mask('999.999.999-99')
                    ->rules([
                        function (IndividualService $service, ?Individual $record): Closure {
                            return function (
                                string $attribute,
                                string $state,
                                Closure $fail
                            ) use ($service, $record): void {
                                $service->validateCpf(
                                    record: $record,
                                    attribute: $attribute,
                                    state: $state,
                                    fail: $fail
                                );
                            };
                        },
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('rg')
                    ->label(__('RG'))
                    ->maxLength(255),
                Forms\Components\Select::make('gender')
                    ->label(__('Sexo'))
                    ->options(GenderEnum::class)
                    ->native(false),
                Forms\Components\DatePicker::make('birth_date')
                    ->label(__('Dt. nascimento'))
                    ->format('d/m/Y')
                    ->maxDate(now()),
                Forms\Components\Textarea::make('contact.complement')
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
                            ->prepend(Str::slug($get('contact.name'))),
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('500')
                    ->imageResizeTargetHeight('500')
                    ->imageResizeUpscale(false)
                    ->maxSize(5120),
            ])
            ->columns(2)
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort(column: 'contact.created_at', direction: 'desc')
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
                                        fn(Individual $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('[CRM] Editar Contatos')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(IndividualService $service, Tables\Actions\DeleteAction $action, Individual $record) =>
                            $service->preventIndividualDeleteIf(action: $action, individual: $record)
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            Tables\Columns\TextColumn::make('contact.name')
                ->label(__('Nome'))
                ->description(
                    fn(Individual $record): ?string =>
                    $record->cpf,
                )
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('contact.roles.name')
                ->label(__('Tipo(s)'))
                ->badge()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('contact.email')
                ->label(__('Email'))
                // ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('contact.display_main_phone')
                ->label(__('Telefone'))
                // ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('contact.source.name')
                ->label(__('Origem'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('contact.owner.name')
                ->label(__('Captador'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('contact.status')
                ->label(__('Status'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('contact.created_at')
                ->label(__('Cadastro'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('contact.updated_at')
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
            Tables\Filters\SelectFilter::make('roles')
                ->label(__('Tipo(s)'))
                ->options(
                    fn(ContactService $service): array =>
                    $service->getOptionsByContactRolesWhereHasContacts(contactableType: MorphMapByClass(static::$model)),
                )
                ->query(
                    fn(ContactService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByContactRoles(query: $query, data: $data)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('sources')
                ->label(__('Origem(s)'))
                ->options(
                    fn(ContactService $service): array =>
                    $service->getOptionsByContactSourcesWhereHasContacts(contactableType: MorphMapByClass(static::$model)),
                )
                ->query(
                    fn(ContactService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByContactSources(query: $query, data: $data)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('owners')
                ->label(__('Captador(es)'))
                ->options(
                    fn(ContactService $service): array =>
                    $service->getOptionsByContactOwnersWhereHasContacts(contactableType: MorphMapByClass(static::$model)),
                )
                ->query(
                    fn(ContactService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByContactOwners(query: $query, data: $data)
                )
                ->multiple(),
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
                    fn(ContactService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByContactCreatedAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('updated_at')
                ->label(__('Últ. atualização'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
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
                    fn(ContactService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByContactUpdatedAt(query: $query, data: $data)
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
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('avatar')
                                    ->label(__('Avatar'))
                                    ->hiddenLabel()
                                    ->collection('avatar')
                                    ->conversion('thumb')
                                    ->visible(
                                        fn(?array $state): bool =>
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
                                Infolists\Components\TextEntry::make('cpf')
                                    ->label(__('CPF'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('rg')
                                    ->label(__('RG'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('gender')
                                    ->label(__('Sexo'))
                                    ->visible(
                                        fn (?GenderEnum $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_birth_date')
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
                                Infolists\Components\Grid::make(['default' => 3])
                                    ->schema([
                                        Infolists\Components\TextEntry::make('contact.status')
                                            ->label(__('Status'))
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('contact.created_at')
                                            ->label(__('Cadastro'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('contact.updated_at')
                                            ->label(__('Últ. atualização'))
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Anexos'))
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('attachments')
                                    ->label('Arquivo(s)')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label(__('Nome')),
                                        Infolists\Components\TextEntry::make('mime_type')
                                            ->label(__('Mime')),
                                        Infolists\Components\TextEntry::make('size')
                                            ->label(__('Tamanho'))
                                            ->state(
                                                fn(Media $record): string =>
                                                AbbrNumberFormat($record->size),
                                            )
                                            ->hint(
                                                fn(Media $record): HtmlString =>
                                                new HtmlString('<a href="' . url('storage/' . $record->file_name) . '" target="_blank">Download</a>')
                                            )
                                            ->hintIcon('heroicon-s-arrow-down-tray')
                                            ->hintColor('primary'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                            ->visible(
                                fn(Individual $record): bool =>
                                $record->attachments->count() > 0
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
            AddressesRelationManager::class,
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIndividuals::route('/'),
            'create' => Pages\CreateIndividual::route('/create'),
            'edit'   => Pages\EditIndividual::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->with('contact');

        if ($user->hasAnyRole(['Superadministrador', 'Administrador'])) {
            return $query->whereHas('contact');
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            $teamUserIds = $user->teams()
                ->with('users:id')
                ->get()
                ->pluck('users.*.id')
                ->flatten()
                ->unique()
                ->toArray();

            return $query->whereHas('contact', function (Builder $query) use ($teamUserIds): Builder {
                return $query->whereIn('user_id', $teamUserIds);
            });
        }

        return $query->whereHas('contact', function (Builder $query) use ($user): Builder {
            return $query->where('user_id', $user->id);
        });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['cpf'];
    }
}
