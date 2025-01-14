<?php

namespace App\Filament\Resources\Financial;

use App\Filament\Resources\Financial\ReceivableTransactionResource\Pages;
use App\Filament\Resources\Financial\ReceivableTransactionResource\RelationManagers;
use App\Models\Financial\ReceivableTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReceivableTransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Conta a Receber';

    protected static ?string $pluralModelLabel = 'Contas a Receber';

    protected static ?string $navigationGroup = 'Financeiro';

    // protected static ?string $navigationParentItem = 'Agências';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Contas a Receber';

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

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
            'index' => Pages\ListReceivableTransactions::route('/'),
            'create' => Pages\CreateReceivableTransaction::route('/create'),
            'edit' => Pages\EditReceivableTransaction::route('/{record}/edit'),
        ];
    }
}
