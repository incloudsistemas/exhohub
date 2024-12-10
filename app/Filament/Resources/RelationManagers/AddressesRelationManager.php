<?php

namespace App\Filament\Resources\RelationManagers;

use App\Enums\ProfileInfos\UfEnum;
use App\Models\Polymorphics\Address;
use App\Services\Polymorphics\AddressService;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Endereços';

    protected static ?string $modelLabel = 'Endereço';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Tipo de endereço'))
                    ->helperText(__('Nome identificador. Ex: Casa, Trabalho...'))
                    ->maxLength(255)
                    ->datalist([
                        'Casa',
                        'Trabalho',
                        'Outros'
                    ])
                    ->autocomplete(false),
                Forms\Components\TextInput::make('zipcode')
                    ->label(__('CEP'))
                    ->mask('99999-999')
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function (AddressService $service, ?string $state, ?string $old, callable $set): void {
                            if ($old == $state) {
                                return;
                            }

                            $address = $service->getAddressByZipcodeViaCep(zipcode: $state);

                            if (isset($address['error'])) {
                                $set('uf', null);
                                $set('city', null);
                                $set('district', null);
                                $set('address_line', null);
                                $set('complement', null);
                            } else {
                                $set('uf', $address['uf']);
                                $set('city', $address['localidade']);
                                $set('district', $address['bairro']);
                                $set('address_line', $address['logradouro']);
                                $set('complement', $address['complemento']);
                            }
                        }
                    )
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('uf')
                    ->label(__('Estado'))
                    ->options(UfEnum::class)
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('city')
                    ->label(__('Cidade'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('district')
                    ->label(__('Bairro'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line')
                    ->label(__('Endereço'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('number')
                    ->label(__('Número'))
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('complement')
                    ->label(__('Complemento'))
                    ->helperText(__('Apto / Bloco / Casa'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('reference')
                    ->label(__('Ponto de referência'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_main')
                    ->label(__('Utilizar como endereço principal'))
                    ->default(
                        fn (): bool =>
                        $this->ownerRecord->addresses->count() === 0
                    )
                    ->accepted(
                        fn (): bool =>
                        $this->ownerRecord->addresses->count() === 0
                    )
                    ->disabled(
                        fn (?Address $record): bool =>
                        isset($record) && $record->is_main === true
                    )
                    ->dehydrated(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(
                fn (Address $record): string =>
                $record->display_full_address
            )
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Tipo'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('display_short_address')
                    ->label(__('Endereço')),
                Tables\Columns\TextColumn::make('zipcode')
                    ->label(__('CEP'))
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('Cidade/Uf'))
                    ->formatStateUsing(
                        fn (Address $record): string =>
                        "{$record->city}-{$record->uf->name}"
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('is_main')
                    ->label(__('Principal'))
                    ->icon(
                        fn (bool $state): string =>
                        match ($state) {
                            false => 'heroicon-m-minus-small',
                            true  => 'heroicon-o-check-circle',
                        }
                    )
                    ->color(
                        fn (bool $state): string =>
                        match ($state) {
                            true    => 'success',
                            default => 'gray',
                        }
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
            // ->reorderable('order')
            ->defaultSort(
                fn (Builder $query): Builder =>
                $query->orderBy('is_main', 'desc')
                    ->orderBy('created_at', 'desc')
            )
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before($this->setUniqueMainAddressCallback()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make()
                            ->before($this->setUniqueMainAddressCallback()),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn (
                                AddressService $service,
                                Tables\Actions\DeleteAction $action,
                                Address $record,
                                RelationManager $livewire
                            ) =>
                            $service->preventAddressDeleteIf(action: $action, address: $record, livewire: $livewire),
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
                    ->before($this->setUniqueMainAddressCallback()),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Tipo de endereço'))
                    ->badge()
                    ->visible(
                        fn (?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('zipcode')
                    ->label(__('CEP')),
                Infolists\Components\TextEntry::make('city')
                    ->label(__('Cidade/Uf'))
                    ->formatStateUsing(
                        fn (Address $record): string =>
                        "{$record->city}-{$record->uf->name}"
                    ),
                Infolists\Components\TextEntry::make('district')
                    ->label(__('Bairro'))
                    ->visible(
                        fn (?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('address_line')
                    ->label(__('Endereço'))
                    ->visible(
                        fn (?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('number')
                    ->label(__('Número'))
                    ->visible(
                        fn (?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('complement')
                    ->label(__('Complemento'))
                    ->visible(
                        fn (?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('reference')
                    ->label(__('Ponto de referência'))
                    ->visible(
                        fn (?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\IconEntry::make('is_main')
                    ->label(__('Principal'))
                    ->icon(
                        fn (bool $state): string =>
                        match ($state) {
                            false => 'heroicon-m-minus-small',
                            true  => 'heroicon-o-check-circle',
                        }
                    )
                    ->color(
                        fn (bool $state): string =>
                        match ($state) {
                            true    => 'success',
                            default => 'gray',
                        }
                    ),
                Infolists\Components\Grid::make(['default' => 3])
                    ->schema([
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

    private function setUniqueMainAddressCallback(): Closure
    {
        return function (AddressService $service, array $data, ?Address $record, RelationManager $livewire): void {
            $service->setUniqueMainAddress(data: $data, address: $record, livewire: $livewire);
        };
    }
}
