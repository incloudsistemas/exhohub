<?php

namespace App\Filament\Resources\Financial;

use App\Enums\DefaultStatusEnum;
use App\Enums\Financial\BankAccountRoleEnum;
use App\Enums\Financial\BankAccountTypeEnum;
use App\Filament\Resources\Financial\BankAccountResource\Pages;
use App\Filament\Resources\Financial\BankAccountResource\RelationManagers;
use App\Models\Financial\BankAccount;
use App\Services\Financial\BankAccountService;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Services\System\AgencyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Conta Bancária';

    protected static ?string $pluralModelLabel = 'Contas Bancárias';

    protected static ?string $navigationGroup = 'Financeiro';

    // protected static ?string $navigationParentItem = 'Agências';

    protected static ?int $navigationSort = 98;

    protected static ?string $navigationLabel = 'Contas Bancárias';

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bank_institution_id')
                    ->label(__('Instituição bancária'))
                    ->relationship(
                        name: 'bankInstitution',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(BankAccountService $service, Builder $query): Builder =>
                        $service->getQueryByBankInstitution(query: $query)
                    )
                    ->searchable()
                    ->selectablePlaceholder(false)
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('role')
                    ->label(__('Tipo de conta'))
                    ->options(BankAccountRoleEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('type_person')
                    ->label(__('Modalidade'))
                    ->options(BankAccountTypeEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('agency_id')
                    ->label(__('Agência imobiliária'))
                    ->relationship(
                        name: 'agency',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(AgencyService $service, Builder $query): Builder =>
                        $service->getQueryByActiveAgencies(query: $query)
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome da conta'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('balance')
                    ->label(__('Saldo inicial'))
                    // ->numeric()
                    ->prefix('R$')
                    ->mask(
                        Support\RawJs::make(<<<'JS'
                            $money($input, ',')
                        JS)
                    )
                    ->placeholder('0,00')
                    ->required()
                    ->maxValue(42949672.95),
                Forms\Components\DatePicker::make('balance_date')
                    ->label(__('Início dos lançamentos'))
                    ->helperText(__('Valor existente na sua conta antes do primeiro lançamento em nossa aplicação.'))
                    ->displayFormat('d/m/Y')
                    ->seconds(false)
                    ->default(now())
                    ->maxDate(now())
                    ->required(),
                Forms\Components\Toggle::make('is_main')
                    ->label(__('Utilizar como conta principal'))
                    ->helperText('Com esta opção selecionada, esta conta bancária vai vir pré-preenchida por padrão quando você criar receitas e despesas. Você pode alterar isso a qualquer momento.')
                    ->inline(false)
                    ->default(
                        fn(): bool =>
                        BankAccount::all()
                            ->count() === 0
                    )
                    ->accepted(
                        fn(): bool =>
                        BankAccount::all()
                            ->count() === 0
                    )
                    ->disabled(
                        fn(?BankAccount $record): bool =>
                        $record && $record->is_main === true
                    )
                    ->dehydrated(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(DefaultStatusEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->required()
                    ->disabled(
                        fn(?BankAccount $record): bool =>
                        $record && $record->is_main === true
                    )
                    ->dehydrated()
                    ->hidden(
                        fn(string $operation): bool =>
                        $operation === 'create'
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort('id', 'desc')
            ->filters(static::getTableFilters(), layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make()
                            ->before(
                                function (array $data): void {
                                    if (isset($data['is_main']) && $data['is_main']) {
                                        BankAccount::where('is_main', 1)
                                            ->update(['is_main' => 0]);
                                    }
                                }
                            ),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(BankAccountService $service, Tables\Actions\DeleteAction $action, BankAccount $record) =>
                            $service->preventBankAccountDeleteIf(action: $action, bankAccount: $record)
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
                Tables\Actions\CreateAction::make()
                    ->before(
                        function (array $data): void {
                            if (isset($data['is_main']) && $data['is_main']) {
                                BankAccount::where('is_main', 1)
                                    ->update(['is_main' => 0]);
                            }
                        }
                    ),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Nome da conta'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('agency.name')
                ->label(__('Ag. imobiliária'))
                ->badge()
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('bankInstitution.name')
                ->label(__('Banco'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('role')
                ->label(__('Tipo de conta'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('type_person')
                ->label(__('Modalidade'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(BankAccountService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(BankAccountService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByStatus(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\IconColumn::make('is_main')
                ->label(__('Principal'))
                ->icon(
                    fn(bool $state): string =>
                    match ($state) {
                        false => 'heroicon-m-minus-small',
                        true  => 'heroicon-o-check-circle',
                    }
                )
                ->color(
                    fn(bool $state): string =>
                    match ($state) {
                        true    => 'success',
                        default => 'gray',
                    }
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('balance_date')
                ->label(__('Data do saldo'))
                ->date('d/m/Y')
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
            Tables\Filters\SelectFilter::make('agencies')
                ->label(__('Agência(s) imobiliária(s)'))
                ->relationship(
                    name: 'agency',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (BankAccountService $service, Builder $query): Builder =>
                    $service->getQueryByAgenciesWhereHasBankAccounts(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('bankInstitutions')
                ->label(__('Banco(s)'))
                ->relationship(
                    name: 'bankInstitution',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (BankAccountService $service, Builder $query): Builder =>
                    $service->getQueryByBankInstitutionsWhereHasBankAccounts(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('role')
                ->label(__('Tipo de conta'))
                ->multiple()
                ->options(BankAccountRoleEnum::class),
            Tables\Filters\SelectFilter::make('type_person')
                ->label(__('Modalidade'))
                ->multiple()
                ->options(BankAccountRoleEnum::class),
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
                    fn(BankAccountService $service, Builder $query, array $data): Builder =>
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
                    fn(BankAccountService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Nome da conta')),
                Infolists\Components\TextEntry::make('agency.name')
                    ->label(__('Ag. imobiliária'))
                    ->badge()
                    ->visible(
                        fn(?array $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('bankInstitution.name')
                    ->label(__('Banco')),
                Infolists\Components\TextEntry::make('role')
                    ->label(__('Tipo de conta')),
                Infolists\Components\TextEntry::make('type_person')
                    ->label(__('Modalidade')),
                Infolists\Components\TextEntry::make('display_balance')
                    ->label(__('Saldo inicial (R$)')),
                Infolists\Components\TextEntry::make('balance_date')
                    ->label(__('Data do saldo'))
                    ->dateTime('d/m/Y'),
                Infolists\Components\IconEntry::make('is_main')
                    ->label(__('Principal'))
                    ->icon(
                        fn(bool $state): string =>
                        match ($state) {
                            false => 'heroicon-m-minus-small',
                            true  => 'heroicon-o-check-circle',
                        }
                    )
                    ->color(
                        fn(bool $state): string =>
                        match ($state) {
                            true    => 'success',
                            default => 'gray',
                        }
                    ),
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
            'index' => Pages\ManageBankAccounts::route('/'),
        ];
    }
}
