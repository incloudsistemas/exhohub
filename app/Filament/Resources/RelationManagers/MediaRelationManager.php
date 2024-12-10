<?php

namespace App\Filament\Resources\RelationManagers;

use App\Services\Polymorphics\MediaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Anexos';

    protected static ?string $modelLabel = 'Anexo';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('file_name')
                    ->label(__('Anexar arquivo(s)'))
                    ->helperText(__('Máx. 25 mb.'))
                    ->multiple(
                        fn (string $operation): bool =>
                        $operation === 'create'
                    )
                    ->disk('public')
                    ->directory('attachments')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('name'))),
                    )
                    ->required()
                    ->maxSize(25600)
                    ->downloadable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(
                fn (Builder $query): Builder =>
                $query->where('collection_name', 'attachments')
            )
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nome'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label(__('Mime'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size')
                    ->label(__('Tamanho'))
                    ->state(
                        fn (Media $record): string =>
                        AbbrNumberFormat($record->size),
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_column')
                    ->label(__('Ordem'))
                    ->sortable(),
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
            ->reorderable('order_column')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(
                        fn (MediaService $service, array $data): array =>
                        $service->mutateFormDataToCreate(ownerRecord: $this->ownerRecord, data: $data)
                    )
                    ->using(
                        fn (MediaService $service, string $model, array $data): Model =>
                        $service->createAction(data: $data, model: $model, ownerRecord: $this->ownerRecord),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make()
                            ->extraModalFooterActions([
                                Tables\Actions\Action::make('download')
                                    ->label(__('Download'))
                                    ->button()
                                    ->action(
                                        fn (Media $record): StreamedResponse =>
                                        Storage::disk('public')
                                            ->download($record->file_name)
                                    ),
                            ]),
                        Tables\Actions\EditAction::make()
                            ->mutateFormDataUsing(
                                fn (MediaService $service, Media $record, array $data): array =>
                                $service->mutateFormDataToEdit(media: $record, data: $data)
                            ),
                        Tables\Actions\Action::make('download')
                            ->icon('heroicon-s-arrow-down-tray')
                            ->action(
                                fn (Media $record): StreamedResponse =>
                                Storage::disk('public')
                                    ->download($record->file_name)
                            ),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->action(
                            fn (MediaService $service, Media $record): bool =>
                            $service->deleteAction(media: $record),
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\ImageEntry::make('file_name')
                    ->hiddenLabel()
                    ->hidden(
                        fn (Media $record): bool =>
                        !in_array(
                            Storage::disk('public')->mimeType($record->file_name),
                            ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml']
                        )
                    ),
                Infolists\Components\TextEntry::make('name')
                    ->label(__('Nome'))
                    ->helperText(
                        fn (Media $record): string =>
                        $record->file_name
                    ),
                Infolists\Components\TextEntry::make('mime_type')
                    ->label(__('Mime')),
                Infolists\Components\TextEntry::make('size')
                    ->label(__('Tamanho'))
                    ->state(
                        fn (Media $record): string =>
                        AbbrNumberFormat($record->size),
                    ),
                Infolists\Components\TextEntry::make('order_column')
                    ->label(__('Ordem')),
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

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // if ($ownerRecord->getTable() === 'cms_pages') {
        //     return !in_array('attachments', $ownerRecord->settings) ? false : true;
        // }

        return true;
    }
}
