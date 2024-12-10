<?php

namespace App\Filament\Resources\System\UserResource\RelationManagers;

use App\Models\System\UserCreciStage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserCreciStagesRelationManager extends RelationManager
{
    protected static string $relationship = 'userCreciStages';

    protected static ?string $title = 'Linha do Tempo: Controle de CRECI';

    protected static ?string $modelLabel = 'Endereço';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(
                fn (UserCreciStage $record): string =>
                $record->creciControlStage->name
            )
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('creciControlStage.name')
                    ->label(__('Etapa')),
                Tables\Columns\TextColumn::make('member_since')
                    ->label(__('Membro desde'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('valid_thru')
                    ->label(__('Válido até'))
                    ->dateTime('d/m/Y')
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
            ])
            ->defaultSort(column: 'created_at', direction: 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        // Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('creciControlStage.name')
                    ->label(__('Etapa')),
                Infolists\Components\TextEntry::make('member_since')
                    ->label(__('Membro desde'))
                    ->dateTime('d/m/Y'),
                Infolists\Components\TextEntry::make('valid_thru')
                    ->label(__('Válido até'))
                    ->dateTime('d/m/Y'),
                Infolists\Components\RepeatableEntry::make('media')
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
                Infolists\Components\TextEntry::make('created_at')
                    ->label(__('Cadastro'))
                    ->dateTime('d/m/Y H:i'),
                Infolists\Components\TextEntry::make('updated_at')
                    ->label(__('Últ. atualização'))
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(3);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $userRoles = $ownerRecord->roles->pluck('id')
            ->toArray();

        // 6 - Corretor/Realtor
        if (!in_array(6, $userRoles)) {
            return false;
        }

        return true;
    }
}
