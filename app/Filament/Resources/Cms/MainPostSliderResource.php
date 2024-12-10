<?php

namespace App\Filament\Resources\Cms;

use App\Enums\Cms\PostSliderRoleEnum;
use App\Enums\Cms\PostStatusEnum;
use App\Filament\Resources\Cms\MainPostSliderResource\Pages;
use App\Filament\Resources\Cms\MainPostSliderResource\RelationManagers;
use App\Models\Cms\PostSlider;
use App\Services\Cms\PostSliderService;
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

class MainPostSliderResource extends Resource
{
    protected static ?string $model = PostSlider::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Slider';

    protected static ?string $pluralModelLabel = 'Sliders';

    protected static ?string $navigationGroup = 'CMS & Marketing';

    protected static ?string $navigationParentItem = 'Páginas';

    protected static ?int $navigationSort = 98;

    protected static ?string $navigationLabel = 'Sliders';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Infos. Gerais'))
                            ->schema([
                                static::getGeneralInfosFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Infos. Complementares'))
                            ->schema([
                                static::getAdditionalInfosFormSection(),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre a postagem.'))
            ->schema([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label(__('Tipo do slider'))
                            ->options(PostSliderRoleEnum::class)
                            ->default(1)
                            ->disabled(
                                fn(string $operation): bool =>
                                $operation === 'edit'
                            )
                            ->required()
                            ->native(false)
                            ->live()
                            ->columnSpan(3),
                        Forms\Components\Toggle::make('settings.hide_text')
                            ->label(__('Ocultar texto?'))
                            ->inline(false)
                            ->live(),
                    ]),
                Forms\Components\TextInput::make('title')
                    ->label(__('Título'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('subtitle')
                    ->label(__('Subtítulo'))
                    ->minLength(2)
                    ->maxLength(255)
                    ->hidden(
                        fn(callable $get): bool =>
                        $get('settings.hide_text') === true
                    )
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('body')
                    ->label(__('Conteúdo'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->hidden(
                        fn(callable $get): bool =>
                        $get('settings.hide_text') === true
                    )
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make(__('Chamada para ação (CTA)'))
                    ->schema([
                        Forms\Components\TextInput::make('cta.url')
                            ->label(__('URL'))
                            ->url()
                            ->helperText('https://...')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('cta.call')
                            ->label(__('Chamada'))
                            ->helperText(__('Ex: Saiba mais!')),
                        Forms\Components\Select::make('cta.target')
                            ->label(__('Alvo'))
                            ->options([
                                '_self'  => 'Mesma janela',
                                '_blank' => 'Nova janela',
                            ])
                            ->default('_self')
                            ->selectablePlaceholder(false)
                            ->native(false),
                    ])
                    ->columns(4),
                Forms\Components\TextInput::make('embed_video')
                    ->label(__('Vídeo destaque no Youtube'))
                    ->prefix('.../watch?v=')
                    ->helperText(new HtmlString('https://youtube.com/watch?v=<span class="font-bold">kJQP7kiw5Fk</span>'))
                    ->required(
                        fn(callable $get): bool =>
                        (int) $get('role') === 3
                    )
                    ->maxLength(255)
                    ->hidden(
                        fn(callable $get): bool =>
                        (int) $get('role') !== 3
                    ),
                Forms\Components\SpatieMediaLibraryFileUpload::make('video')
                    ->label(__('Vídeo destaque'))
                    ->helperText(__('Tipo de arquivo permitido: .mp4. // Máx. 25 mb.'))
                    ->collection('video')
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('title'))),
                    )
                    ->required(
                        fn(callable $get): bool =>
                        (int) $get('role') === 2
                    )
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(25600)
                    ->downloadable()
                    ->live()
                    ->hidden(
                        fn(callable $get): bool =>
                        (int) $get('role') !== 2
                    ),
                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('Imagem destaque'))
                    ->helperText(__('Tipos de arquivo permitidos: .png, .jpg, .jpeg, .gif. // Máx. 1920x1080px // 5 mb.'))
                    ->collection('image')
                    ->image()
                    ->responsiveImages()
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('title'))),
                    )
                    ->required(
                        fn(callable $get): bool =>
                        (int) $get('role') === 1
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->imageResizeUpscale(false)
                    ->maxSize(5120)
                    ->downloadable(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAdditionalInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Complementares'))
            ->description(__('Forneça informações adicionais relevantes sobre a postagem.'))
            ->schema([
                Forms\Components\Fieldset::make(__('Configs. de estilo'))
                    ->schema([
                        Forms\Components\Select::make('settings.style')
                            ->label(__('Contraste'))
                            ->options([
                                'dark'  => 'Escuro',
                                'light' => 'Claro',
                                'none'  => 'Nenhum'
                            ])
                            ->default('dark')
                            ->selectablePlaceholder(false)
                            ->native(false),
                        Forms\Components\Select::make('settings.image_indent')
                            ->label(__('Identação da imagem'))
                            ->options([
                                'left'   => 'Esquerda',
                                'right'  => 'Direita',
                                'center' => 'Centro',
                                'top'    => 'Topo',
                            ])
                            ->default('center')
                            ->selectablePlaceholder(false)
                            ->native(false),
                        Forms\Components\Select::make('settings.text_indent')
                            ->label(__('Identação do texto'))
                            ->options([
                                'left'   => 'Esquerda',
                                'right'  => 'Direita',
                                'center' => 'Centro'
                            ])
                            ->default('left')
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->hidden(
                                fn(callable $get): bool =>
                                $get('settings.hide_text') === true
                            ),
                        Forms\Components\ColorPicker::make('settings.text_color')
                            ->label(__('Cor do texto (hexadecimal)'))
                            ->hidden(
                                fn(callable $get): bool =>
                                $get('settings.hide_text') === true
                            ),
                    ])
                    ->columns(3),
                Forms\Components\Fieldset::make(__('Datas da postagem'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('publish_at')
                            ->label(__('Dt. publicação'))
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('expiration_at')
                            ->label(__('Dt. expiração'))
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->minDate(
                                fn(callable $get): string =>
                                $get('publish_at')
                            ),
                    ]),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(PostStatusEnum::class)
                    ->default(1)
                    ->required()
                    ->native(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
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
                                        fn(PostSlider $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('Editar [CMS] Páginas')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(PageService $service, Tables\Actions\DeleteAction $action, Page $record) =>
                            $service->preventPageDeleteIf(action: $action, page: $record)
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

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                ->label('')
                ->collection('image')
                ->conversion('thumb')
                ->size(45)
                ->limit(1)
                ->circular(),
            Tables\Columns\TextColumn::make('title')
                ->label(__('Título'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('role')
                ->label(__('Tipo'))
                ->searchable(
                    query: fn(PostSliderService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByRole(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(PostSliderService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByRole(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('order')
                ->label(__('Ordem'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(PostSliderService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(PostSliderService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByStatus(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('publish_at')
                ->label(__('Publicação'))
                ->dateTime('d/m/Y H:i')
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

    public static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('role')
                ->label(__('Tipo'))
                ->options(PostSliderRoleEnum::class)
                ->multiple(),
            Tables\Filters\SelectFilter::make('status')
                ->label(__('Status'))
                ->options(PostStatusEnum::class)
                ->multiple(),
            Tables\Filters\Filter::make('cmsPost.publish_at')
                ->label(__('Publicação'))
                ->form([
                    Forms\Components\Grid::make([
                        'default' => 1,
                        'md'      => 2,
                    ])
                        ->schema([
                            Forms\Components\DatePicker::make('publish_from')
                                ->label(__('Publicação de'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('publish_until')) && $state > $get('publish_until')) {
                                            $set('publish_until', $state);
                                        }
                                    }
                                ),
                            Forms\Components\DatePicker::make('publish_until')
                                ->label(__('Publicação até'))
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $get, callable $set, ?string $state): void {
                                        if (!empty($get('publish_from')) && $state < $get('publish_from')) {
                                            $set('publish_from', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(PostSliderService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPublishAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('cmsPost.created_at')
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
                    fn(PostSliderService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByCreatedAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('cmsPost.updated_at')
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
                    fn(PostSliderService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\SpatieMediaLibraryImageEntry::make('image')
                    ->label(__('Avatar'))
                    ->hiddenLabel()
                    ->collection('image')
                    ->conversion('thumb')
                    ->circular()
                    ->visible(
                        fn(?array $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('title')
                    ->label(__('Título')),
                Infolists\Components\TextEntry::make('role')
                    ->label(__('Tipo da postagem'))
                    ->badge(),
                Infolists\Components\TextEntry::make('subtitle')
                    ->label(__('Subtítulo'))
                    ->visible(
                        fn(?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\TextEntry::make('body')
                    ->label(__('Conteúdo'))
                    ->visible(
                        fn(?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\Fieldset::make('Chamada para ação (CTA)')
                    ->schema([
                        Infolists\Components\TextEntry::make('cta.url')
                            ->label(__('URL'))
                            ->url(
                                fn(string $state): string =>
                                $state,
                            )
                            ->openUrlInNewTab()
                            ->visible(
                                fn(?string $state): bool =>
                                !empty($state),
                            ),
                        Infolists\Components\TextEntry::make('cta.call')
                            ->label(__('Chamada'))
                            ->visible(
                                fn(?string $state): bool =>
                                !empty($state),
                            ),
                        Infolists\Components\TextEntry::make('cta.target')
                            ->label(__('Alvo'))
                            ->visible(
                                fn(?string $state): bool =>
                                !empty($state),
                            ),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('order')
                    ->label(__('Ordem'))
                    ->visible(
                        fn(?string $state): bool =>
                        !empty($state),
                    ),
                Infolists\Components\Grid::make(['default' => 3])
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge(),
                        Infolists\Components\TextEntry::make('publish_at')
                            ->label(__('Dt. publicação'))
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('expiration_at')
                            ->label(__('Dt. expiração'))
                            ->dateTime('d/m/Y H:i')
                            ->visible(
                                fn(?string $state): bool =>
                                !empty($state),
                            ),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMainPostSliders::route('/'),
            'create' => Pages\CreateMainPostSlider::route('/create'),
            'edit'   => Pages\EditMainPostSlider::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('slideable_type', 'cms_pages')
            ->where('slideable_id', 1); // 1 - Index
    }
}
