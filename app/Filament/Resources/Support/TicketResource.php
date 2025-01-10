<?php

namespace App\Filament\Resources\Support;

use App\Enums\Support\TicketPriorityEnum;
use App\Enums\Support\TicketStatusEnum;
use App\Filament\Resources\Support\TicketResource\Pages;
use App\Filament\Resources\Support\TicketResource\RelationManagers;
use App\Models\Support\Ticket;
use App\Models\Support\TicketComment;
use App\Models\System\User;
use App\Services\Support\DepartmentService;
use App\Services\Support\TicketCategoryService;
use App\Services\Support\TicketService;
use App\Services\System\UserService;
use Closure;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Chamado';

    protected static ?string $navigationGroup = 'Suporte';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        static::getGeneralInfosFormSection(),
                    ])
                    ->visibleOn('create')
                    ->columnSpanFull(),
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Quadro de Respostas/Comentários'))
                            ->schema(
                                static::getCommentsFormSection()
                            ),
                    ])
                    ->visibleOn('edit')
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o chamado.'))
            ->schema([
                Forms\Components\Select::make('department_id')
                    ->label(__('Departamento'))
                    ->relationship(
                        name: 'departments',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(DepartmentService $service, Builder $query): Builder =>
                        $service->getQueryByDepartments(query: $query)
                    )
                    // ->multiple()
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(
                        fn(callable $set) =>
                        $set('categories', null),
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('categories')
                    ->label(__('Categoria(s)'))
                    ->relationship(
                        name: 'categories',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (TicketCategoryService $service, Builder $query, callable $get): Builder {
                            $departmentId = $get('department_id') ? $get('department_id')[0] : null;
                            return $service->getQueryByTicketCategoriesDepartment(query: $query, department: $departmentId);
                        }
                    )
                    ->multiple()
                    // ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    // ->required()
                    ->native(false)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')
                    ->label(__('Título'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('body')
                    ->label(__('Descreva o seu problema'))
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'undo',
                    ])
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('pages')
                    ->fileAttachmentsVisibility('public')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('attachments[]')
                    ->label(__('Anexo(s)'))
                    ->helperText(__('Máx. 3 arqs. // 5 mb.'))
                    ->collection('attachments')
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('title'))),
                    )
                    ->multiple()
                    ->maxSize(5120)
                    ->maxFiles(3)
                    ->downloadable()
                    ->columnSpanFull(),
                Forms\Components\Select::make('applicant_users')
                    ->label(__('Solicitante(s) adicional(is)'))
                    ->helperText(__('Adicione usuários que você deseja que acompanhem este chamado. Eles poderão comentar e receber atualizações deste chamado.'))
                    ->relationship(
                        name: 'applicantUsers',
                        titleAttribute: 'name',
                    )
                    ->getSearchResultsUsing(
                        fn(UserService $service, string $search): array =>
                        $service->getUserOptionsBySearch(search: $search),
                    )
                    ->getOptionLabelUsing(
                        fn(UserService $service, int $value): string =>
                        $service->getUserOptionLabel(value: $value),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getCommentsFormSection(): array
    {
        return [
            Forms\Components\Section::make('')
                // ->description(__('...'))
                ->schema([
                    Forms\Components\Placeholder::make('ticket.title')
                        ->label(__('Título do chamado'))
                        ->hiddenLabel()
                        ->content(
                            fn(Ticket $record): HtmlString =>
                            new HtmlString("<strong>#{$record->id} - {$record->title}</strong>"),
                        )
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('ticket.body')
                        ->label(__('Descrição do problema'))
                        ->hiddenLabel()
                        ->content(
                            fn(Ticket $record): HtmlString =>
                            new HtmlString($record->body),
                        )
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('ticket_attachments')
                        ->label(__('Anexo(s)'))
                        ->schema([
                            Forms\Components\Placeholder::make('file_name')
                                ->label(__('Arquivo'))
                                ->hiddenLabel()
                                ->content(
                                    fn(?string $state): ?string =>
                                    $state,
                                ),
                            Forms\Components\Placeholder::make('download')
                                ->label(__('Download'))
                                ->hiddenLabel()
                                // ->content('')
                                ->hint(
                                    fn(?string $state): HtmlString =>
                                    new HtmlString('<a href="' . $state . '" target="_blank">Download</a>')
                                )
                                ->hintIcon('heroicon-s-arrow-down-tray')
                                ->hintColor('primary'),
                        ])
                        ->addable(false)
                        ->reorderable(false)
                        ->collapsible(false)
                        ->collapseAllAction(
                            fn(Forms\Components\Actions\Action $action) =>
                            $action->label(__('Minimizar todos'))
                        )
                        ->deletable(false)
                        ->deleteAction(
                            fn(Forms\Components\Actions\Action $action) =>
                            $action->requiresConfirmation()
                        )
                        ->hidden(
                            fn(array $state): bool =>
                            empty($state)
                        )
                        ->grid(2)
                        ->columns(2)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('ticket.user')
                        ->label(__('Autor'))
                        ->content(
                            fn(Ticket $record): string =>
                            $record->owner->name,
                        ),
                    Forms\Components\Placeholder::make('ticket.created_at')
                        ->label(__('Dt. chamado'))
                        ->content(
                            fn(Ticket $record): string =>
                            $record->created_at->format('d/m/Y H:i'),
                        ),
                ])
                ->columns(2),
            Forms\Components\Select::make('priority')
                ->label(__('Prioridade'))
                ->options(TicketPriorityEnum::class)
                ->native(false)
                ->visible(
                    fn(string $operation): bool =>
                    $operation === 'edit' &&
                        auth()->user()->hasAnyRole(['Superadministrador', 'Administrador', 'Suporte'])
                ),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options(TicketStatusEnum::class)
                ->selectablePlaceholder(false)
                ->required()
                ->native(false)
                ->live()
                ->visible(
                    fn(string $operation, ?string $state): bool =>
                    $operation === 'edit' &&
                        auth()->user()->hasAnyRole(['Superadministrador', 'Administrador', 'Suporte']) &&
                        (isset($state) && (int) $state !== 0)
                ),
            Forms\Components\Repeater::make('comments')
                ->hiddenLabel()
                ->relationship()
                ->schema([
                    Forms\Components\Placeholder::make('display_user')
                        ->label(__('Autor'))
                        ->content(
                            fn(?TicketComment $record): ?string =>
                            $record->owner?->name,
                        )
                        ->disabled()
                        ->visible(
                            fn(?TicketComment $record): int =>
                            isset($record)
                        ),
                    Forms\Components\Placeholder::make('display_created_at')
                        ->label(__('Data'))
                        ->content(
                            fn(?TicketComment $record): ?string =>
                            isset($record) ? $record->created_at->format('d/m/Y H:i') : null,
                        )
                        ->disabled()
                        ->visible(
                            fn(?TicketComment $record): int =>
                            isset($record)
                        ),
                    Forms\Components\RichEditor::make('body')
                        ->label(__('Comentário'))
                        ->hiddenLabel()
                        ->toolbarButtons([
                            'attachFiles',
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'h2',
                            'h3',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'undo',
                        ])
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('pages')
                        ->fileAttachmentsVisibility('public')
                        ->required()
                        ->visible(
                            fn(?TicketComment $record): int =>
                            !isset($record)
                        )
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('display_body')
                        ->hiddenLabel()
                        ->content(
                            fn(?TicketComment $record): ?HtmlString =>
                            isset($record) ? new HtmlString($record->body) : null,
                        )
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('attachments[]')
                        ->label(__('Anexo(s)'))
                        ->helperText(__('Máx. 3 arqs. // 5 mb.'))
                        ->collection('attachments')
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file, callable $get): string =>
                            (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                ->prepend(Str::slug($get('../../title'))),
                        )
                        ->multiple()
                        ->maxSize(5120)
                        ->maxFiles(3)
                        ->downloadable()
                        // ->panelLayout('grid')
                        ->visible(
                            fn(?TicketComment $record): int =>
                            !isset($record)
                        )
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('display_attachments')
                        ->label(__('Anexo(s)'))
                        ->schema([
                            Forms\Components\Placeholder::make('file_name')
                                ->label(__('Arquivo'))
                                ->hiddenLabel()
                                ->content(
                                    fn(?string $state): ?string =>
                                    $state,
                                ),
                            Forms\Components\Placeholder::make('download')
                                ->label(__('Download'))
                                ->hiddenLabel()
                                // ->content('')
                                ->hint(
                                    fn(?string $state): HtmlString =>
                                    new HtmlString('<a href="' . $state . '" target="_blank">Download</a>')
                                )
                                ->hintIcon('heroicon-s-arrow-down-tray')
                                ->hintColor('primary'),
                        ])
                        ->addable(false)
                        ->reorderable(false)
                        ->collapsible(false)
                        ->collapseAllAction(
                            fn(Forms\Components\Actions\Action $action) =>
                            $action->label(__('Minimizar todos'))
                        )
                        ->deletable(false)
                        ->deleteAction(
                            fn(Forms\Components\Actions\Action $action) =>
                            $action->requiresConfirmation()
                        )
                        ->visible(
                            fn(?TicketComment $record): int =>
                            isset($record)
                        )
                        ->hidden(
                            fn(array $state): bool =>
                            empty($state)
                        )
                        ->grid(2)
                        ->columns(2)
                        ->columnSpanFull(),
                    Forms\Components\Hidden::make('user_id')
                        ->label(__('Usuário'))
                        ->default(
                            fn(): int =>
                            auth()->user()->id,
                        )
                        ->required()
                        ->visible(
                            fn(?TicketComment $record): int =>
                            !isset($record)
                        ),
                ])
                // ->itemLabel(
                //     fn(array $state): ?string =>
                //     User::find($state['user_id'])?->name ?? null
                // )
                ->addActionLabel(__('Adicionar comentário'))
                ->defaultItems(1)
                ->reorderable(false)
                // ->collapsible()
                ->collapseAllAction(
                    fn(Forms\Components\Actions\Action $action) =>
                    $action->label(__('Minimizar todos'))
                )
                ->deletable(false)
                ->deleteAction(
                    fn(Forms\Components\Actions\Action $action) =>
                    $action->requiresConfirmation()
                )
                ->disabled(
                    function (Ticket $record): bool {
                        // 0 - Aguardando atendimento
                        if ((int) $record->status->value === 0 && !auth()->user()->hasAnyRole(['Superadministrador', 'Administrador', 'Suporte'])) {
                            return true;
                        }

                        // 2 - Finalizado
                        if ((int) $record->status->value === 2) {
                            return true;
                        }

                        return false;
                    }
                )
                ->minItems(1)
                ->columns(2)
                ->columnSpanFull(),
        ];
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
                                        fn(Ticket $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('Editar [Suporte] Chamados')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(TicketService $service, Tables\Actions\DeleteAction $action, Ticket $record) =>
                            $service->preventTicketDeleteIf(action: $action, ticket: $record)
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
            Tables\Columns\TextColumn::make('id')
                ->label(__('#ID'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('title')
                ->label(__('Título'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('departments.name')
                ->label(__('Departamento(s)'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('categories.name')
                ->label(__('Categoria(s)'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Solicitante'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
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
            Tables\Columns\TextColumn::make('responsibleUsers.name')
                ->label(__('Responsável'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(TicketService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(TicketService $service, Builder $query, string $direction): Builder =>
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
            Tables\Columns\TextColumn::make('finished_at')
                ->label(__('Encerrado'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('departments')
                ->label(__('Departamento(s)'))
                ->relationship(
                    name: 'departments',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(DepartmentService $service, Builder $query): Builder =>
                    $service->getQueryByDepartments(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('categories')
                ->label(__('Categoria(s)'))
                ->relationship(
                    name: 'categories',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(TicketCategoryService $service, Builder $query): Builder =>
                    $service->getQueryByTicketCategories(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('status')
                ->label(__('Status'))
                ->multiple()
                ->options(TicketStatusEnum::class),
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
                    fn(TicketService $service, Builder $query, array $data): Builder =>
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
                    fn(TicketService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                //
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
            'index'  => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit'   => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery();

        if ($user->hasAnyRole(['Superadministrador', 'Administrador'])) {
            return $query;
        }

        $query->where(function (Builder $query) use ($user): Builder {
            return $query->where('user_id', $user->id)
                ->orWhereHas('users', function (Builder $query) use ($user): Builder {
                    return $query->where('id', $user->id);
                    // ->wherePivot('role', 2); // 2 - Solicitante
                });
        });

        if ($user->hasAnyRole(['Suporte'])) {
            $departmentsIds = $user->departments()
                ->pluck('id')
                ->toArray();

            return $query->orWhereHas('departments', function (Builder $query) use ($departmentsIds): Builder {
                return $query->whereIn('id', $departmentsIds);
            });
        }

        return $query;
    }
}
