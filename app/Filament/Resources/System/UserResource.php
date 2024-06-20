<?php

namespace App\Filament\Resources\System;

use App\Enums\ProfileInfos\EducationalLevel;
use App\Enums\ProfileInfos\Gender;
use App\Enums\ProfileInfos\MaritalStatus;
use App\Enums\ProfileInfos\UserStatus;
use App\Filament\Resources\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\RelationManagers\MediaRelationManager;
use App\Filament\Resources\System\UserResource\Pages;
use App\Filament\Resources\System\UserResource\RelationManagers;
use App\Models\System\User;
use App\Services\System\RoleService;
use App\Services\System\UserService;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getGeneralInfosFormSection(),
                static::getSystemAccessFormSection(),
                static::getAdditionalInfosFormSection(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o usuário.'))
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->confirmed()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn (callable $set, ?string $state): ?string =>
                        $set('email_confirmation', $state)
                    )
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('additional_emails')
                    ->label(__('Emails adicionais'))
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
                        fn (array $state): ?string =>
                        $state['email'] ?? null
                    )
                    ->addActionLabel(__('Adicionar email'))
                    ->defaultItems(0)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Repeater::make('phones')
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
                        fn (array $state): ?string =>
                        $state['number'] ?? null
                    )
                    ->addActionLabel(__('Adicionar telefone'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getSystemAccessFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Acesso ao Sistema'))
            ->description(__('Gerencie o nível de acesso do usuário.'))
            ->schema([
                Forms\Components\TextInput::make('email_confirmation')
                    ->label(__('Usuário'))
                    ->placeholder(__('Preencha o email'))
                    ->required()
                    // ->readOnly()
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Select::make('roles')
                    ->label(__('Nível de acesso'))
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (RoleService $service, Builder $query): Builder =>
                        $service->getQueryByAuthUserRoles(query: $query)
                    )
                    // ->multiple()
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('password')
                    ->label(__('Senha'))
                    ->password()
                    ->helperText(
                        fn (string $operation): string =>
                        $operation === 'create'
                            ? __('Senha com mín. de 8 digitos.')
                            : __('Preencha apenas se desejar alterar a senha. Min. de 8 dígitos.')
                    )
                    ->required(
                        fn (string $operation): bool =>
                        $operation === 'create'
                    )
                    ->confirmed()
                    ->minLength(8)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(__('Confirmar senha'))
                    ->password()
                    ->required(
                        fn (string $operation): bool =>
                        $operation === 'create'
                    )
                    ->maxLength(255)
                    ->dehydrated(false),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(UserStatus::getArray())
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->in(UserStatus::getIndexes())
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
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('rg')
                    ->label(__('RG'))
                    ->maxLength(255),
                Forms\Components\Select::make('gender')
                    ->label(__('Sexo'))
                    ->options(Gender::getArray())
                    ->in(Gender::getIndexes())
                    ->native(false),
                Forms\Components\DatePicker::make('birth_date')
                    ->label(__('Dt. nascimento'))
                    ->format('d/m/Y')
                    ->maxDate(now()),
                Forms\Components\Select::make('marital_status')
                    ->label(__('Estado civil'))
                    ->options(MaritalStatus::getArray())
                    ->searchable()
                    ->in(MaritalStatus::getIndexes())
                    ->native(false),
                Forms\Components\Select::make('educational_level')
                    ->label(__('Escolaridade'))
                    ->options(EducationalLevel::getArray())
                    ->searchable()
                    ->in(EducationalLevel::getIndexes())
                    ->native(false),
                Forms\Components\TextInput::make('nationality')
                    ->label(__('Nacionalidade'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('citizenship')
                    ->label(__('Naturalidade'))
                    ->maxLength(255),
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
                        fn (TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('name'))),
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
                                        fn (User $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->disabled(
                                        fn (): bool =>
                                        !auth()->user()->can('Editar Usuários')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn (UserService $service, Tables\Actions\DeleteAction $action, User $record) =>
                            $service->preventUserDeleteIf(action: $action, user: $record)
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
                ->label(__('Nome'))
                ->description(
                    fn (User $record): ?string =>
                    $record->cpf,
                )
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByNameAndCpf(query: $query, search: $search)
                )
                ->sortable(),
            Tables\Columns\TextColumn::make('roles.name')
                ->label(__('Nível de acesso'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('email')
                ->label(__('Email'))
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('display_main_phone')
                ->label(__('Telefone'))
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPhone(query: $query, search: $search)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('display_status')
                ->label(__('Status'))
                ->badge()
                ->color(
                    fn (User $record): ?string =>
                    UserStatus::getColor(status: $record->status),
                )
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn (UserService $service, Builder $query, string $direction): Builder =>
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
            Tables\Filters\SelectFilter::make('roles')
                ->label(__('Níveis de acessos'))
                ->relationship(
                    name: 'roles',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (RoleService $service, Builder $query): Builder =>
                    $service->getQueryByAuthUserRoles(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('status')
                ->label(__('Status'))
                ->multiple()
                ->options(UserStatus::getArray()),
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
                    fn (UserService $service, Builder $query, array $data): Builder =>
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
                    fn (UserService $service, Builder $query, array $data): Builder =>
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
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('image')
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
                                    ->label(__('Nível de acesso'))
                                    ->badge(),
                                Infolists\Components\TextEntry::make('email')
                                    ->label(__('Email')),
                                Infolists\Components\TextEntry::make('display_additional_emails')
                                    ->label(__('Emails adicionais'))
                                    ->visible(
                                        fn (array|string|null $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_main_phone_with_name')
                                    ->label(__('Telefone'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_additional_phones')
                                    ->label(__('Telefones adicionais'))
                                    ->visible(
                                        fn (array|string|null $state): bool =>
                                        !empty($state),
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
                                Infolists\Components\TextEntry::make('display_gender')
                                    ->label(__('Sexo'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_birth_date')
                                    ->label(__('Dt. nascimento'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_marital_status')
                                    ->label(__('Estado civil'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_educational_level')
                                    ->label(__('Escolaridade'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('nationality')
                                    ->label(__('Nacionalidade'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('citizenship')
                                    ->label(__('Naturalidade'))
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
                                Infolists\Components\RepeatableEntry::make('addresses')
                                    ->label('Endereço(s)')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('display_full_address')
                                                ->label(__('Endereço'))
                                                ->hiddenLabel()
                                                ->columnSpan(2),
                                        Infolists\Components\TextEntry::make('name')
                                            ->label(__('Tipo'))
                                            ->visible(
                                                fn (?string $state): bool =>
                                                !empty($state),
                                            ),
                                    ])
                                    ->visible(
                                        fn (array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) || (!is_array($state) && !empty($state)),
                                    )
                                    ->columns(3)
                                    ->columnSpanFull(),
                                Infolists\Components\Grid::make(['default' => 3])
                                    ->schema([
                                        Infolists\Components\TextEntry::make('display_status')
                                            ->label(__('Status'))
                                            ->badge()
                                            ->color(
                                                fn (User $record): string =>
                                                UserStatus::getColor(status: $record->status),
                                            ),
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
                                Infolists\Components\RepeatableEntry::make('attachments')
                                    ->label('Arquivo(s)')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label(__('Nome'))
                                            ->hiddenLabel(),
                                        Infolists\Components\TextEntry::make('mime_type')
                                                ->label(__('Mime')),
                                        Infolists\Components\TextEntry::make('size')
                                            ->label(__('Tamanho'))
                                            ->hiddenLabel()
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
                                    ->visible(
                                        fn (array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) || (!is_array($state) && !empty($state)),
                                    )
                                    ->columns(3)
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
            AddressesRelationManager::class,
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        return parent::getEloquentQuery()
            ->byAuthUserRoles(user: $user);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'cpf'];
    }
}
