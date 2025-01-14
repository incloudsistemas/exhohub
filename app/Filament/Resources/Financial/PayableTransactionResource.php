<?php

namespace App\Filament\Resources\Financial;

use App\Filament\Resources\Financial\PayableTransactionResource\Pages;
use App\Filament\Resources\Financial\PayableTransactionResource\RelationManagers;
use App\Models\Financial\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayableTransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Conta a Pagar';

    protected static ?string $pluralModelLabel = 'Contas a Pagar';

    protected static ?string $navigationGroup = 'Financeiro';

    // protected static ?string $navigationParentItem = 'Agências';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Contas a Pagar';

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';

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
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPayableTransactions::route('/'),
            'create' => Pages\CreatePayableTransaction::route('/create'),
            'edit' => Pages\EditPayableTransaction::route('/{record}/edit'),
        ];
    }
}
