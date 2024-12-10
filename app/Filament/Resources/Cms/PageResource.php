<?php

namespace App\Filament\Resources\Cms;

use App\Enums\Cms\PostStatusEnum;
use App\Filament\Resources\Cms\PageResource\Pages;
use App\Filament\Resources\Cms\PageResource\RelationManagers;
use App\Models\Cms\Page;
use App\Services\Cms\PageService;
use App\Services\Cms\PostCategoryService;
use App\Services\Cms\PostService;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Página';

    protected static ?string $navigationGroup = 'CMS & Marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-window';

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
                        Forms\Components\Tabs\Tab::make(__('Mídias'))
                            ->schema([
                                static::getMediaFormSection(),
                            ])
                            ->hidden(
                                fn(callable $get): bool =>
                                empty(array_intersect(['images', 'videos', 'embed_videos'], $get('settings') ?? []))
                            ),
                        Forms\Components\Tabs\Tab::make(__('Infos. Complementares'))
                            ->schema([
                                static::getAdditionalInfosFormSection(),
                            ])
                            ->hidden(
                                fn(callable $get): bool =>
                                empty(array_intersect([
                                    'tags',
                                    'seo',
                                    'user_id',
                                    'order',
                                    'featured',
                                    'comment',
                                    'publish_at',
                                    'status'
                                ], $get('settings') ?? []))
                            ),
                        Forms\Components\Tabs\Tab::make(__('Configs. da página'))
                            ->schema([
                                static::getConfigPageFormSection(),
                            ])
                            ->hidden(
                                fn(): bool =>
                                !auth()->user()->can('Cadastrar [CMS] Páginas')
                            ),
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
                Forms\Components\Select::make('page_id')
                    ->label(__('Página parental'))
                    ->options(
                        fn(PageService $service, ?Page $record): array =>
                        $service->getOptionsByMainPages(page: $record),
                    )
                    ->default($_GET['main-page'] ?? null)
                    ->searchable()
                    ->preload()
                    ->disabled(
                        fn(): bool =>
                        isset($_GET['main-page']) || !auth()->user()->can('Cadastrar [CMS] Páginas')
                    )
                    ->dehydrated()
                    ->hidden(
                        fn(?Page $record, ?string $state): bool =>
                        ($state && !auth()->user()->can('Cadastrar [CMS] Páginas')) ||
                        ($record && $record->subpages->count() > 0)
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cms_post.title')
                    ->label(__('Título'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->live(debounce: 1000)
                    ->afterStateUpdated(
                        fn(callable $set, ?string $state): ?string =>
                        auth()->user()->can('Cadastrar [CMS] Páginas')
                            ? $set('cms_post.slug', Str::slug($state))
                            : null
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cms_post.slug')
                    ->label(__('Slug'))
                    ->required()
                    ->rules([
                        function (PostService $service, ?Page $record): Closure {
                            return function (
                                string $attribute,
                                string $state,
                                Closure $fail
                            ) use ($service, $record): void {
                                $postableType = MorphMapByClass(model: static::$model);

                                $service->validatePostSlugByPostableType(
                                    record: $record,
                                    postableType: $postableType,
                                    attribute: $attribute,
                                    state: $state,
                                    fail: $fail
                                );
                            };
                        },
                    ])
                    ->maxLength(255)
                    ->disabled(
                        fn(): bool =>
                        !auth()->user()->can('Cadastrar [CMS] Páginas')
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('cms_post.categories')
                    ->label(__('Categoria(s)'))
                    ->options(
                        fn(PostCategoryService $service): array =>
                        $service->getOptionsByActivePostCategories(),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->when(
                        auth()->user()->can('Cadastrar [CMS] Categorias'),
                        fn(Forms\Components\Select $component): Forms\Components\Select =>
                        $component->suffixAction(
                            fn(PostCategoryService $service): Forms\Components\Actions\Action =>
                            $service->getQuickCreateActionByPostCategories(field: 'cms_post.categories', multiple: true),
                        ),
                    )
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('categories', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cms_post.subtitle')
                    ->label(__('Subtítulo'))
                    ->minLength(2)
                    ->maxLength(255)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('subtitle', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('cms_post.excerpt')
                    ->label(__('Resumo/Chamada'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('excerpt', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('cms_post.body')
                    ->label(__('Conteúdo'))
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
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('body', $get('settings'))
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
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('cta', $get('settings'))
                    )
                    ->columns(4),
                Forms\Components\TextInput::make('cms_post.url')
                    ->label(__('URL'))
                    ->url()
                    // ->prefix('https://')
                    ->helperText('https://...')
                    ->maxLength(255)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('url', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cms_post.embed_video')
                    ->label(__('Vídeo destaque no Youtube'))
                    ->prefix('.../watch?v=')
                    ->helperText(new HtmlString('https://youtube.com/watch?v=<span class="font-bold">kJQP7kiw5Fk</span>'))
                    ->maxLength(255)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('embed_video', $get('settings'))
                    ),
                Forms\Components\SpatieMediaLibraryFileUpload::make('video')
                    ->label(__('Vídeo destaque'))
                    ->helperText(__('Tipo de arquivo permitido: .mp4. // Máx. 25 mb.'))
                    ->collection('video')
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend($get('cms_post.slug')),
                    )
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(25600)
                    ->downloadable()
                    ->live()
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('video', $get('settings'))
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
                            ->prepend($get('cms_post.slug')),
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->imageResizeUpscale(false)
                    ->maxSize(5120)
                    ->downloadable()
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('image', $get('settings'))
                    ),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getMediaFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(
            function (callable $get): string {
                $hasImages = in_array('images', $get('settings'));
                $hasVideos = in_array(['videos', 'embed_videos'], $get('settings'));

                return ($hasImages && $hasVideos)
                    ? __('Galeria de Imagens e Vídeos')
                    : ($hasImages
                        ? __('Galeria de Imagens')
                        : __('Galeria de Vídeos')
                    );
            }
        )
            ->description(
                function (callable $get): string {
                    $hasImages = in_array('images', $get('settings'));
                    $hasVideos = in_array('videos', $get('settings'));

                    return ($hasImages && $hasVideos)
                        ? __('Adicione e gerencie as imagens e vídeos da postagem.')
                        : ($hasImages
                            ? __('Adicione e gerencie as imagens da postagem.')
                            : __('Adicione e gerencie os vídeos da postagem.')
                        );
                }
            )
            ->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('Upload das imagens'))
                    ->helperText(__('Tipos de arquivo permitidos: .png, .jpg, .jpeg, .gif. // Máx. 1920x1080px // 50 arqs. // 5 mb.'))
                    ->collection('images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend($get('cms_post.slug')),
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->imageResizeUpscale(false)
                    ->maxSize(5120)
                    ->maxFiles(50)
                    ->downloadable()
                    ->panelLayout('grid')
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('images', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('videos')
                    ->label(__('Upload dos vídeos'))
                    ->helperText(__('Tipo de arquivo permitido: .mp4. // 10 arqs. // Máx. 25 mb.'))
                    ->collection('videos')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend($get('cms_post.slug')),
                    )
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(25600)
                    ->maxFiles(10)
                    ->downloadable()
                    ->panelLayout('grid')
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('videos', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('embed_videos')
                    ->label(__('Vídeos destaque no Youtube'))
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label(__('Código Youtube'))
                            ->prefix('.../watch?v=')
                            ->helperText(new HtmlString('https://youtube.com/watch?v=<span class="font-bold">kJQP7kiw5Fk</span>'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title')
                            ->label(__('Título do vídeo'))
                            ->minLength(2)
                            ->maxLength(255),
                    ])
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['title'] ?? null
                    )
                    ->addActionLabel(__('Adicionar novo'))
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
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('embed_videos', $get('settings'))
                    )
                    ->columnSpanFull()
                    ->columns(2),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAdditionalInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Complementares'))
            ->description(__('Forneça informações adicionais relevantes sobre a postagem.'))
            ->schema([
                Forms\Components\TagsInput::make('cms_post.tags')
                    ->label(__('Tag(s)'))
                    ->helperText(__('As tags são usadas para filtragem e busca. Uma postagem pode ter até 120 tags.'))
                    ->nestedRecursiveRules([
                        // 'min:1',
                        'max:120',
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('tags', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make(__('Otimização para motores de busca (SEO)'))
                    ->schema([
                        Forms\Components\Placeholder::make('')
                            ->content(__('Crie metatags específicas para esta página. Por padrão elas já são preenchidas automaticamente usando o título da pág. e descrição simplificada.'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cms_post.meta_title')
                            ->label(__('Título SEO'))
                            ->helperText(__('55 - 60 caracteres'))
                            ->minLength(2)
                            ->maxLength(60)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('cms_post.meta_description')
                            ->label(__('Descrição SEO'))
                            ->rows(4)
                            ->helperText(__('152 - 155 caracteres'))
                            ->minLength(2)
                            ->maxLength(155)
                            ->columnSpanFull(),
                        // Forms\Components\TagsInput::make('cms_post.meta_keywords')
                        //     ->label(__('Palavras chave'))
                        //     // ->separator(',')
                        //     ->columnSpanFull(),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('seo', $get('settings'))
                    ),
                Forms\Components\Select::make('cms_post.user_id')
                    ->label(__('Autor'))
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
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('user_id', $get('settings'))
                    )
                    ->columnSpanFull(),
                Forms\Components\Grid::make(['default' => 3])
                    ->schema([
                        Forms\Components\TextInput::make('cms_post.order')
                            ->numeric()
                            ->label(__('Ordem'))
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(100)
                            ->hidden(
                                fn(callable $get): bool =>
                                !in_array('order', $get('settings'))
                            ),
                        Forms\Components\Toggle::make('cms_post.featured')
                            ->label(__('Destaque?'))
                            ->default(true)
                            ->inline(false)
                            ->hidden(
                                fn(callable $get): bool =>
                                !in_array('featured', $get('settings'))
                            ),
                        Forms\Components\Toggle::make('cms_post.comment')
                            ->label(__('Comentário?'))
                            ->default(true)
                            ->inline(false)
                            ->hidden(
                                fn(callable $get): bool =>
                                !in_array('comment', $get('settings'))
                            ),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        empty(array_intersect([
                            'order',
                            'featured',
                            'comment'
                        ], $get('settings') ?? []))
                    ),
                Forms\Components\Fieldset::make(__('Datas da postagem'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('cms_post.publish_at')
                            ->label(__('Dt. publicação'))
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('cms_post.expiration_at')
                            ->label(__('Dt. expiração'))
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->minDate(
                                fn(callable $get): string =>
                                $get('cms_post.publish_at')
                            )
                            ->hidden(
                                fn(callable $get): bool =>
                                !in_array('expiration_at', $get('settings'))
                            ),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('publish_at', $get('settings'))
                    ),
                Forms\Components\Select::make('cms_post.status')
                    ->label(__('Status'))
                    ->options(PostStatusEnum::class)
                    ->default(1)
                    ->required()
                    ->native(false)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array('status', $get('settings'))
                    ),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getConfigPageFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Configs. da página'))
            ->description(__('Personalize a página com os campos desejados.'))
            ->schema([
                Forms\Components\CheckboxList::make('settings')
                    ->hiddenLabel()
                    ->options([
                        'categories'    => 'Categorias',
                        'subtitle'      => 'Subtítulo',
                        'excerpt'       => 'Resumo',
                        'body'          => 'Conteúdo',
                        'cta'           => 'CTA',
                        'url'           => 'Url',
                        'embed_video'   => 'Youtube vídeo',
                        'video'         => 'Vídeo',
                        'image'         => 'Imagem',
                        'images'        => 'Galeria de imagens',
                        'videos'        => 'Galeria de vídeos',
                        'embed_videos'  => 'Galeria de vídeos Youtube',
                        'tags'          => 'Tags',
                        'seo'           => 'SEO',
                        'user_id'       => 'Autor',
                        'order'         => 'Ordem',
                        'featured'      => 'Destaque',
                        'comment'       => 'Comentário',
                        'publish_at'    => 'Data de publicação',
                        'expiration_at' => 'Data de expiração',
                        'status'        => 'Status',
                        'sliders'       => 'Sliders',
                        'tabs'          => 'Abas',
                        'accordions'    => 'Acordeões',
                        'attachments'   => 'Anexos',
                    ])
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(4)
                    ->gridDirection('row')
                    ->live(),
            ])
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort(
                fn (PostService $service, Builder $query): Builder =>
                $service->tableDefaultSort(query: $query, publishAtDirection: 'asc')
            )
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
                                        fn(Page $record): string =>
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
            Tables\Columns\TextColumn::make('cmsPost.title')
                ->label(__('Título'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('cmsPost.postCategories.name')
                ->label(__('Categoria(s)'))
                ->badge()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('cmsPost.order')
                ->label(__('Ordem'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('cmsPost.owner.name')
                ->label(__('Autor'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('cmsPost.status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(PostService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPostStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(PostService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByPostStatus(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('cmsPost.publish_at')
                ->label(__('Publicação'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('cmsPost.created_at')
                ->label(__('Cadastro'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('cmsPost.updated_at')
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
            Tables\Filters\SelectFilter::make('cmsPost.postCategories')
                ->label(__('Categoria(s)'))
                ->options(
                    fn(PostService $service): array =>
                    $service->getOptionsByPostCategoriesWhereHasPosts(postableType: MorphMapByClass(model: static::$model))
                )
                ->query(
                    fn(PostService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPostCategories(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('owners')
                ->label(__('Autor(es)'))
                ->options(
                    fn(PostService $service): array =>
                    $service->getOptionsByPostOwnersWhereHasPosts(postableType: MorphMapByClass(model: static::$model)),
                )
                ->query(
                    fn(PostService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPostOwners(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('cmsPost.status')
                ->label(__('Status'))
                ->multiple()
                ->options(PostStatusEnum::class)
                ->query(
                    fn(PostService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPostStatuses(query: $query, data: $data)
                ),
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
                    fn(PostService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPostPublishAt(query: $query, data: $data)
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
                    fn(PostService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPostCreatedAt(query: $query, data: $data)
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
                    fn(PostService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPostUpdatedAt(query: $query, data: $data)
                ),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Label')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make(__('Dados Gerais'))
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
                                Infolists\Components\TextEntry::make('cmsPost.title')
                                    ->label(__('Título')),
                                Infolists\Components\TextEntry::make('cmsPost.postCategories.name')
                                    ->label(__('Categoria(s)'))
                                    ->badge()
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('cmsPost.subtitle')
                                    ->label(__('Subtítulo'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('cmsPost.excerpt')
                                    ->label(__('Resumo/Chamada'))
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
                                    ->visible(
                                        fn(Page $record): bool =>
                                        in_array('cta', $record->settings),
                                    )
                                    ->columns(3)
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('cmsPost.url')
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
                                Infolists\Components\TextEntry::make('cmsPost.meta_title')
                                    ->label(__('Título SEO'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('cmsPost.meta_description')
                                    ->label(__('Descrição SEO'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('cmsPost.owner.name')
                                    ->label(__('Autor'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('cmsPost.order')
                                    ->label(__('Ordem')),
                                Infolists\Components\IconEntry::make('cmsPost.featured')
                                    ->label(__('Destaque?'))
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
                                Infolists\Components\IconEntry::make('cmsPost.comment')
                                    ->label(__('Comentário?'))
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
                                        Infolists\Components\TextEntry::make('cmsPost.status')
                                            ->label(__('Status'))
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('cmsPost.publish_at')
                                            ->label(__('Dt. publicação'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('cmsPost.expiration_at')
                                            ->label(__('Dt. expiração'))
                                            ->dateTime('d/m/Y H:i')
                                            ->visible(
                                                fn(?string $state): bool =>
                                                !empty($state),
                                            ),
                                        Infolists\Components\TextEntry::make('cmsPost.created_at')
                                            ->label(__('Cadastro'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('cmsPost.updated_at')
                                            ->label(__('Últ. atualização'))
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Conteúdo e Tags'))
                            ->schema([
                                Infolists\Components\TextEntry::make('cmsPost.body')
                                    ->label(__('Conteúdo'))
                                    ->hiddenLabel()
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->html()
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('cmsPost.tags')
                                    ->label(__('Tag(s)'))
                                    ->badge()
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->columnSpanFull(),
                            ])
                            ->visible(
                                fn(Page $record): bool =>
                                !empty($record->cmsPost->body) || !empty($record->cmsPost->tags)
                            ),
                        Infolists\Components\Tabs\Tab::make(__('Mídias'))
                            ->schema([
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('images')
                                    ->label(__('Galeria de imagens'))
                                    ->collection('images')
                                    ->conversion('thumb')
                                    ->visible(
                                        fn(?array $state): bool =>
                                        !empty($state),
                                    )
                                    ->columnSpanFull(),
                                // Infolists\Components\SpatieMediaLibraryImageEntry::make('video')
                                //     ->label(__('Vídeo destaque'))
                                //     ->collection('video')
                                //     ->visible(
                                //         fn(?array $state): bool =>
                                //         !empty($state),
                                //     )
                                //     ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('cmsPost.embed_video')
                                    ->label(__('Vídeo destaque no Youtube'))
                                    ->state(
                                        function (Page $record): ?string {
                                            if (!$record->cmsPost->embed_video) {
                                                return null;
                                            }

                                            return "https://www.youtube.com/watch?v={$record->cmsPost->embed_video}";
                                        }
                                    )
                                    ->url(
                                        fn(Page $record): string =>
                                        "https://www.youtube.com/watch?v={$record->cmsPost->embed_video}",
                                    )
                                    ->openUrlInNewTab()
                                    ->visible(
                                        fn (array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                // Infolists\Components\SpatieMediaLibraryImageEntry::make('videos')
                                //     ->label(__('Galeria de vídeos'))
                                //     ->collection('videos')
                                //     ->visible(
                                //         fn(?array $state): bool =>
                                //         !empty($state),
                                //     )
                                //     ->columnSpanFull(),
                                Infolists\Components\RepeatableEntry::make('embed_videos')
                                    ->label('Vídeos destaque no Youtube')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label(__('Título'))
                                            ->visible(
                                                fn(?string $state): bool =>
                                                !empty($state),
                                            ),
                                        Infolists\Components\TextEntry::make('code')
                                            ->label(__('Código Youtube'))
                                            ->url(
                                                fn(?string $state): string =>
                                                "https://youtube.com/watch?v={$state}"
                                            )
                                            ->openUrlInNewTab()
                                            ->visible(
                                                fn(?string $state): bool =>
                                                !empty($state),
                                            ),
                                    ])
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0]['code'])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->visible(
                                fn(Page $record): bool =>
                                in_array('images', $record->settings) ||
                                in_array('video', $record->settings) ||
                                in_array('embed_video', $record->settings) ||
                                in_array('embed_videos', $record->settings),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('cmsPost')
            ->whereHas('cmsPost');
    }
}
