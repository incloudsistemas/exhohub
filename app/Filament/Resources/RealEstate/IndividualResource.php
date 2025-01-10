<?php

namespace App\Filament\Resources\RealEstate;

use App\Enums\ProfileInfos\UfEnum;
use App\Enums\RealEstate\IndividualRoleEnum;
use App\Enums\RealEstate\PropertyStatusEnum;
use App\Enums\RealEstate\PropertyUsageEnum;
use App\Enums\RealEstate\RentPeriodEnum;
use App\Enums\RealEstate\RentWarrantiesEnum;
use App\Filament\Resources\RealEstate\IndividualResource\Pages;
use App\Filament\Resources\RealEstate\IndividualResource\RelationManagers;
use App\Filament\Resources\RelationManagers\MediaRelationManager;
use App\Models\RealEstate\Individual;
use App\Services\Crm\Contacts\ContactService;
use App\Services\Polymorphics\AddressService;
use App\Services\RealEstate\IndividualService;
use App\Services\RealEstate\PropertyService;
use App\Services\System\UserService;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class IndividualResource extends Resource
{
    protected static ?string $model = Individual::class;

    protected static ?string $modelLabel = 'Imóvel à Venda/Aluguel';

    protected static ?string $pluralModelLabel = 'Imóveis à Venda/Aluguel';

    protected static ?string $navigationGroup = 'Imóveis';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Imóveis à Venda/Aluguel';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Infos. Gerais e Endereço'))
                            ->schema([
                                static::getGeneralInfosFormSection(),
                                static::getAddressFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Infos. Detalhadas'))
                            ->schema([
                                static::getDetailedInfosFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Mídias'))
                            ->schema([
                                static::getMediaFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Características'))
                            ->schema([
                                static::getCharacteristicsFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Captação'))
                            ->schema([
                                static::getCaptureInfosFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Publicação'))
                            ->schema([
                                static::getChannelsFormSection(),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o imóvel.'))
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('property.usage')
                            ->label(__('O imóvel é'))
                            ->options(PropertyUsageEnum::class)
                            ->default(1)
                            ->selectablePlaceholder(false)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(
                                function (IndividualService $service, callable $set, callable $get): void {
                                    $set('property.type_id', null);
                                    $set('property.subtype_id', null);

                                    if (!$get('property.custom_title')) {
                                        $title = $service->getPropertyTitle(
                                            typeId: $get('property.type_id'),
                                            bedroom: $get('bedroom'),
                                            city: $get('property.address.city'),
                                            uf: $get('property.address.uf'),
                                            district: $get('property.address.district'),
                                            role: $get('role'),
                                        );

                                        $set('property.title', $title);
                                        // $set('property.slug', Str::slug($title));
                                    }

                                    if (!$get('property.custom_code')) {
                                        $set('property.code', null);
                                    }
                                }
                            ),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Select::make('property.type_id')
                    ->label(__('Tipo do imóvel'))
                    ->options(
                        fn(PropertyService $service, callable $get): array =>
                        $service->getOptionsByActivePropertyTypesUsage(usage: $get('property.usage')),
                    )
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(
                        function (
                            IndividualService $service,
                            PropertyService $propertyService,
                            ?string $state,
                            callable $set,
                            callable $get
                        ): void {
                            $set('property.subtype_id', null);

                            if (!$get('property.custom_title')) {
                                $title = $service->getPropertyTitle(
                                    typeId: $get('property.type_id'),
                                    bedroom: $get('bedroom'),
                                    city: $get('property.address.city'),
                                    uf: $get('property.address.uf'),
                                    district: $get('property.address.district'),
                                    role: $get('role'),
                                );

                                $set('property.title', $title);
                                // $set('property.slug', Str::slug($title));
                            }

                            if (!$get('property.custom_code')) {
                                $code = $propertyService->getPropertyCode(typeId: $state);
                                $set('property.code', $code);
                            }
                        }
                    ),
                Forms\Components\Select::make('property.subtype_id')
                    ->label('Subtipo do imóvel')
                    ->options(
                        fn(PropertyService $service, callable $get): array =>
                        $service->getOptionsByActivePropertySubtypesType(typeId: $get('property.type_id')),
                    )
                    ->searchable()
                    ->preload(),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('bedroom')
                            ->label(__('Quartos'))
                            ->options(array_combine(range(0, 50), range(0, 50)))
                            ->native(false)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function (IndividualService $service, callable $set, callable $get): void {
                                    if (!$get('property.custom_title')) {
                                        $title = $service->getPropertyTitle(
                                            typeId: $get('property.type_id'),
                                            bedroom: $get('bedroom'),
                                            city: $get('property.address.city'),
                                            uf: $get('property.address.uf'),
                                            district: $get('property.address.district'),
                                            role: $get('role'),
                                        );

                                        $set('property.title', $title);
                                        // $set('property.slug', Str::slug($title));
                                    }
                                }
                            ),
                        Forms\Components\Select::make('suite')
                            ->label(__('Suítes'))
                            ->options(array_combine(range(0, 50), range(0, 50)))
                            ->native(false),
                        Forms\Components\Select::make('bathroom')
                            ->label(__('Banheiros'))
                            ->options(array_combine(range(0, 50), range(0, 50)))
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('garage')
                            ->label(__('Vagas garagem'))
                            ->options(array_combine(range(0, 50), range(0, 50)))
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('useful_area')
                            ->label(__('Área útil'))
                            ->helperText(__('m²'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $money($input, ',')
                                JS)
                            )
                            ->placeholder('0,00')
                            ->required()
                            ->maxValue(42949672.95),
                        Forms\Components\TextInput::make('total_area')
                            ->label(__('Área total'))
                            ->helperText(__('m²'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $money($input, ',')
                                JS)
                            )
                            ->placeholder('0,00')
                            ->maxValue(42949672.95),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAddressFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Endereço'))
            ->description(__('Detalhe a localização exata do imóvel, incluindo endereço, pontos de referência e acessibilidade.'))
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('property.address.zipcode')
                            ->label(__('CEP'))
                            ->mask('99999-999')
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function (
                                    AddressService $addressService,
                                    IndividualService $individualService,
                                    ?string $state,
                                    ?string $old,
                                    callable $set,
                                    callable $get
                                ): void {
                                    if ($old == $state) {
                                        return;
                                    }

                                    $address = $addressService->getAddressByZipcodeViaCep(zipcode: $state);

                                    if (isset($address['error'])) {
                                        $set('property.address.uf', null);
                                        $set('property.address.city', null);
                                        $set('property.address.district', null);
                                        $set('property.address.address_line', null);
                                        $set('property.address.complement', null);
                                    } else {
                                        $set('property.address.uf', $address['uf']);
                                        $set('property.address.city', $address['localidade']);
                                        $set('property.address.district', $address['bairro']);
                                        $set('property.address.address_line', $address['logradouro']);
                                        $set('property.address.complement', $address['complemento']);
                                    }

                                    if (!$get('property.custom_title')) {
                                        $title = $individualService->getPropertyTitle(
                                            typeId: $get('property.type_id'),
                                            bedroom: $get('bedroom'),
                                            city: $get('property.address.city'),
                                            uf: $get('property.address.uf'),
                                            district: $get('property.address.district'),
                                            role: $get('role'),
                                        );

                                        $set('property.title', $title);
                                        // $set('property.slug', Str::slug($title));
                                    }
                                }
                            )
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Select::make('property.address.uf')
                    ->label(__('Estado'))
                    ->options(UfEnum::class)
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('property.address.city')
                    ->label(__('Cidade'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('property.address.district')
                    ->label(__('Bairro'))
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function (IndividualService $service, callable $set, callable $get): void {
                            if (!$get('property.custom_title')) {
                                $title = $service->getPropertyTitle(
                                    typeId: $get('property.type_id'),
                                    bedroom: $get('bedroom'),
                                    city: $get('property.address.city'),
                                    uf: $get('property.address.uf'),
                                    district: $get('property.address.district'),
                                    role: $get('role'),
                                );

                                $set('property.title', $title);
                                // $set('property.slug', Str::slug($title));
                            }
                        }
                    ),
                Forms\Components\TextInput::make('property.address.address_line')
                    ->label(__('Endereço'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('property.address.number')
                    ->label(__('Número'))
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('property.address.complement')
                    ->label(__('Complemento'))
                    ->helperText(__('Apto / Bloco / Casa'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('property.address.reference')
                    ->label(__('Ponto de referência'))
                    ->maxLength(255),
                Forms\Components\Select::make('property.show_address')
                    ->label('Mostrar o endereço')
                    ->options([
                        0 => 'Não mostrar',
                        1 => 'Completo',
                        2 => 'Somente bairro, cidade e uf',
                        3 => 'Somente rua, cidade e uf',
                        4 => 'Somente cidade e uf',
                    ])
                    ->default(1)
                    ->required()
                    ->in([0, 1, 2, 3, 4])
                    ->native(false)
                    ->selectablePlaceholder(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getDetailedInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Detalhadas'))
            ->description(__('Código, título, valores, descrição e demais informações do anúncio do imóvel.'))
            ->schema([
                Forms\Components\Select::make('role')
                    ->label(__('Tipo de negociação'))
                    ->options(IndividualRoleEnum::class)
                    ->selectablePlaceholder(false)
                    ->default(1)
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(
                        function (IndividualService $service, callable $set, callable $get): void {
                            if (!$get('property.custom_title')) {
                                $title = $service->getPropertyTitle(
                                    typeId: $get('property.type_id'),
                                    bedroom: $get('bedroom'),
                                    city: $get('property.address.city'),
                                    uf: $get('property.address.uf'),
                                    district: $get('property.address.district'),
                                    role: $get('role'),
                                );

                                $set('property.title', $title);
                                // $set('property.slug', Str::slug($title));
                            }
                        }
                    ),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('property.code')
                            ->label(__('Código'))
                            ->required()
                            ->rules([
                                function (PropertyService $service, ?Individual $record): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ) use ($service, $record): void {
                                        $service->validateCodeRule(
                                            record: $record,
                                            attribute: $attribute,
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->minLength(2)
                            ->maxLength(255)
                            ->disabled(
                                fn(callable $get): bool =>
                                !$get('property.custom_code')
                            )
                            ->dehydrated()
                            ->columnSpan(2),
                        Forms\Components\Toggle::make('property.custom_code')
                            ->label(__('Customizar'))
                            ->default(false)
                            ->inline(false)
                            ->live(),
                    ])
                    ->columns(3),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('property.title')
                            ->label(__('Título do anúncio'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->disabled(
                                fn(callable $get): bool =>
                                !$get('property.custom_title')
                            )
                            ->dehydrated()
                            ->live(debounce: 1000)
                            // ->afterStateUpdated(
                            //     fn(callable $set, ?string $state): ?string =>
                            //     $set('property.slug', Str::slug($state))
                            // )
                            ->columnSpan(5),
                        Forms\Components\Toggle::make('property.custom_title')
                            ->label(__('Customizar'))
                            ->default(false)
                            ->inline(false)
                            ->live(),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),
                // Forms\Components\TextInput::make('property.slug')
                //     ->label(__('Slug'))
                //     ->required()
                //     ->rules([
                //         function (PropertyService $service, ?Individual $record): Closure {
                //             return function (
                //                 string $attribute,
                //                 string $state,
                //                 Closure $fail
                //             ) use ($service, $record): void {
                //                 $service->validateSlugRule(
                //                     record: $record,
                //                     attribute: $attribute,
                //                     state: $state,
                //                     fail: $fail
                //                 );
                //             };
                //         },
                //     ])
                //     ->maxLength(255)
                //     ->columnSpanFull(),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('sale_price')
                            ->label(__('Preço da venda'))
                            ->prefix('R$')
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $money($input, ',')
                                JS)
                            )
                            ->placeholder('0,00')
                            ->maxValue(42949672.95)
                            ->required(),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array($get('role'), [1, 3])
                    )
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('rent_price')
                    ->label(__('Preço do aluguel'))
                    ->prefix('R$')
                    ->mask(
                        Support\RawJs::make(<<<'JS'
                            $money($input, ',')
                        JS)
                    )
                    ->placeholder('0,00')
                    ->maxValue(42949672.95)
                    ->required()
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array($get('role'), [2, 3])
                    ),
                Forms\Components\Select::make('rent_period')
                    ->label(__('Pagamento do aluguel'))
                    ->options(RentPeriodEnum::class)
                    ->default(3)
                    ->selectablePlaceholder(false)
                    ->required(
                        fn(callable $get): bool =>
                        !empty($get('rent_price'))
                    )
                    ->native(false)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array($get('role'), [2, 3])
                    ),
                Forms\Components\Select::make('rent_warranties')
                    ->label(__('Modalidade do aluguel (garantias)'))
                    ->multiple()
                    ->options(RentWarrantiesEnum::class)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->hidden(
                        fn(callable $get): bool =>
                        !in_array($get('role'), [2, 3])
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('property.condo_price')
                    ->label(__('Condomínio /mês'))
                    ->prefix('R$')
                    ->mask(
                        Support\RawJs::make(<<<'JS'
                            $money($input, ',')
                        JS)
                    )
                    ->placeholder('0,00')
                    ->maxValue(42949672.95),
                Forms\Components\TextInput::make('property.tax_price')
                    ->label(__('IPTU /ano'))
                    ->prefix('R$')
                    ->mask(
                        Support\RawJs::make(<<<'JS'
                            $money($input, ',')
                        JS)
                    )
                    ->placeholder('0,00')
                    ->maxValue(42949672.95),
                Forms\Components\RichEditor::make('property.body')
                    ->label(__('Descrição'))
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
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('property.tags')
                    ->label(__('Tag(s)'))
                    ->helperText(__('As tags são usadas para filtragem e busca. Uma postagem pode ter até 120 tags.'))
                    ->nestedRecursiveRules([
                        // 'min:1',
                        'max:120',
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getMediaFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Mídias'))
            ->description(__('Adicione e gerencie as imagens, vídeos e demais mídias do imóvel.'))
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
                            ->prepend($get('property.slug')),
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->imageResizeUpscale(false)
                    ->required()
                    ->maxSize(5120)
                    ->maxFiles(50)
                    ->downloadable()
                    ->panelLayout('grid')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('property.embed_videos')
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
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\TextInput::make('property.url')
                    ->label(__('URL do tour virtual'))
                    ->url()
                    ->helperText('https://...')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getCharacteristicsFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Características'))
            ->description(__('Destaque atributos e qualidades distintas do imóvel.'))
            ->schema([
                Forms\Components\Fieldset::make('Diferenciais')
                    ->schema([
                        // 1 - Diferenciais
                        Forms\Components\CheckboxList::make('property.characteristics.differences')
                            ->label('')
                            ->options(
                                fn(PropertyService $service): array =>
                                $service->getOptionsByActiveCharacteristicsRoles(roles: [1]),
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(4)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Lazer')
                    ->schema([
                        // 2 - Lazer
                        Forms\Components\CheckboxList::make('property.characteristics.leisure')
                            ->label('')
                            ->options(
                                fn(PropertyService $service): array =>
                                $service->getOptionsByActiveCharacteristicsRoles(roles: [2]),
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(4)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Segurança')
                    ->schema([
                        // 3 - Segurança
                        Forms\Components\CheckboxList::make('property.characteristics.security')
                            ->label('')
                            ->options(
                                fn(PropertyService $service): array =>
                                $service->getOptionsByActiveCharacteristicsRoles(roles: [3]),
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(4)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Infraestrutura')
                    ->schema([
                        // 4 - Infraestrutura
                        Forms\Components\CheckboxList::make('property.characteristics.infrastructure')
                            ->label('')
                            ->options(
                                fn(PropertyService $service): array =>
                                $service->getOptionsByActiveCharacteristicsRoles(roles: [4]),
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(4)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Características do condomínio')
                    ->schema([
                        Forms\Components\TextInput::make('property.floors')
                            ->numeric()
                            ->label(__('Nº de andares')),
                        Forms\Components\TextInput::make('property.units_per_floor')
                            ->numeric()
                            ->label(__('Nº de unidades por andar')),
                        Forms\Components\TextInput::make('property.towers')
                            ->numeric()
                            ->label(__('Nº de torres')),
                        Forms\Components\TextInput::make('property.construct_year')
                            ->numeric()
                            ->label(__('Ano de construção'))
                            ->placeholder('0000')
                            ->minLength(4)
                            ->maxLength(4),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getCaptureInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Captação'))
            ->description(__('Informe os detalhes dos proprietários e do captador do imóvel.'))
            ->schema([
                Forms\Components\Select::make('property.contact_owners')
                    ->label(__('Proprietário(s) do imóvel'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(
                        fn(ContactService $service, string $search): array =>
                        $service->getContactOptionsBySearch(search: $search),
                    )
                    ->getOptionLabelsUsing(
                        fn(ContactService $service, array $values): array =>
                        $service->getContactOptionsLabel(values: $values),
                    )
                    ->when(
                        auth()->user()->can('Cadastrar [CRM] Contatos'),
                        fn(Forms\Components\Select $component): Forms\Components\Select =>
                        $component->suffixAction(
                            fn(ContactService $service): Forms\Components\Actions\Action =>
                            $service->getQuickCreateActionByContacts(field: 'property.contact_owners', multiple: true),
                        ),
                    )
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('property.user_id')
                    ->label(__('Captador'))
                    ->getSearchResultsUsing(
                        fn(UserService $service, string $search): array =>
                        $service->getUserOptionsBySearch(search: $search),
                    )
                    ->getOptionLabelUsing(
                        fn(UserService $service, int $value): ?string =>
                        $service->getUserOptionLabel(value: $value),
                    )
                    ->searchable()
                    ->preload()
                    ->default(auth()->user()->id)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('property.owner_notes')
                    ->label(__('Dados privativos da captação'))
                    ->helperText(__('Estas informações não são publicadas no portal.'))
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
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getChannelsFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Publicação'))
            ->description(__('Defina canais e métodos para promover a publicação do anúncio do imóvel.'))
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Toggle::make('property.publish_on.canal_pro')
                            ->label(__('Canal Pro'))
                            ->helperText(__('Olx, Zap e VivaReal'))
                            ->default(false)
                            ->inline(true)
                            ->live(),
                        Forms\Components\Toggle::make('property.publish_on.portal_exho')
                            ->label(__('Portal Exho'))
                            ->default(true)
                            ->inline(true)
                            ->live(),
                        Forms\Components\Toggle::make('property.publish_on.portal_web')
                            ->label(__('Portal Web'))
                            ->default(true)
                            ->inline(true)
                            ->live(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make(__('Canal Pro (Olx, Zap e VivaReal)'))
                    ->schema([
                        Forms\Components\Select::make('property.publish_on_data.canal_pro.publication_type')
                            ->label(__('Tipo de publicação (destaques)'))
                            ->options([
                                'STANDARD'      => 'Padrão',
                                'PREMIUM'       => 'Destaque',
                                'SUPER_PREMIUM' => 'Super destaque',
                                'PREMIERE_1'    => 'Destaque Premium*',
                                'PREMIERE_2'    => 'Destaque Especial*',
                            ])
                            ->default('PREMIUM')
                            ->required()
                            ->in(['STANDARD', 'PREMIUM', 'SUPER_PREMIUM', 'PREMIERE_1', 'PREMIERE_2'])
                            ->native(false),
                        Forms\Components\Placeholder::make('')
                            ->content(__('Certifique-se que as ofertas estejam sendo enviadas conforme o plano (destaques, super destaques ou premieres) respeitando a grade contratada.'))
                            ->helperText(new HtmlString('*Destaques Especial/Premium estão disponíveis apenas para os contratos Zap+.'))
                            ->columnSpanFull(),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !$get('property.publish_on.canal_pro')
                    ),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Fieldset::make(__('Otimização para motores de busca (SEO)'))
                            ->schema([
                                Forms\Components\Placeholder::make('')
                                    ->content(__('Crie metatags específicas para esta página. Por padrão elas já são preenchidas automaticamente usando o título da pág. e descrição simplificada.'))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('property.cms_post.meta_title')
                                    ->label(__('Título SEO'))
                                    ->helperText('55 - 60 caracteres')
                                    ->minLength(2)
                                    ->maxLength(60)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('property.cms_post.meta_description')
                                    ->label(__('Descrição SEO'))
                                    ->rows(4)
                                    ->helperText('152 - 155 caracteres')
                                    ->minLength(2)
                                    ->maxLength(155)
                                    ->columnSpanFull(),
                                // Forms\Components\TagsInput::make('property.cms_post.meta_keywords')
                                //     ->label(__('Palavras chave'))
                                //     ->columnSpanFull(),
                            ]),
                        Forms\Components\Grid::make(['default' => 3])
                            ->schema([
                                Forms\Components\TextInput::make('property.order')
                                    ->numeric()
                                    ->label(__('Ordem'))
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(100),
                                Forms\Components\Toggle::make('property.featured')
                                    ->label(__('Destaque?'))
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Toggle::make('property.comment')
                                    ->label(__('Comentário?'))
                                    ->default(false)
                                    ->inline(false),
                            ]),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !$get('property.publish_on.portal_web')
                    )
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make(__('Datas da postagem'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('property.publish_at')
                            ->label(__('Dt. publicação'))
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('property.expiration_at')
                            ->label(__('Dt. expiração'))
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->minDate(
                                fn(callable $get): string =>
                                $get('property.publish_at')
                            ),
                    ])
                    ->hidden(
                        fn(callable $get): bool =>
                        !$get('property.publish_on.canal_pro') &&
                        !$get('property.publish_on.portal_exho') &&
                        !$get('property.publish_on.portal_web')
                    ),
                Forms\Components\Select::make('property.status')
                    ->label(__('Status'))
                    ->options(PropertyStatusEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false)
                    ->visible(
                        fn(string $operation): bool =>
                        $operation === 'edit' && auth()->user()->hasAnyRole(['Superadministrador', 'Administrador']),
                    ),
            ])
            ->columns(2)
            ->collapsible();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns(static::getTableColumns())
            ->defaultSort(column: 'property.created_at', direction: 'desc')
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
                                        fn(Individual $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn(): bool =>
                                        !auth()->user()->can('Editar [IMB] Imóveis à Venda e/ou Aluguel')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\Action::make('download-images')
                            ->label(__('Download imagens'))
                            ->icon('heroicon-c-arrow-down-tray')
                            ->action(
                                fn(PropertyService $service, Individual $record) =>
                                $service->downloadImages(property: $record->property)
                            ),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn(IndividualService $service, Tables\Actions\DeleteAction $action, Individual $record) =>
                            $service->preventIndividualDeleteIf(action: $action, individual: $record)
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
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                ->label('')
                ->collection('images')
                ->conversion('thumb')
                ->size(100)
                ->limit(1)
                ->circular(),
            Tables\Columns\TextColumn::make('property.title')
                ->label(__('Imóvel'))
                ->description(
                    fn(Individual $record): string =>
                    $record->property->code . ' | ' .
                    $record->property->display_usage . ' | ' .
                    $record->property->type->name,
                    position: 'above'
                )
                ->formatStateUsing(
                    fn(PropertyService $service, Individual $record): string =>
                    $service->getTableDisplayProperty(property: $record->property)
                )
                ->searchable(
                    query: fn(PropertyService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPropertyTitleCodeAndUsage(query: $query, search: $search)
                )
                ->sortable()
                ->html(),
            Tables\Columns\TextColumn::make('property.type.name')
                ->label(__('Tipo'))
                ->description(
                    fn(Individual $record): ?string =>
                    $record->property->subtype->name ?? null
                )
                ->searchable(
                    query: fn(PropertyService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPropertyTypeAndSubtype(query: $query, search: $search)
                )
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('role')
                ->label(__('Negociação'))
                ->searchable(
                    query: fn(IndividualService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByRole(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(IndividualService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByRole(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('property.address.district')
                ->label(__('Localização'))
                ->description(
                    fn(Individual $record): string =>
                    $record->property->address->city . ' - ' . $record->property->address->uf->name
                )
                ->searchable(
                    query: fn(PropertyService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPropertyAddress(query: $query, search: $search)
                )
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('display_sale_price')
                ->label(__('Venda (R$)'))
                ->sortable(
                    query: fn(IndividualService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortBySalePrice(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('display_rent_price')
                ->label(__('Aluguel (R$)'))
                ->state(
                    function (Individual $record): ?string {
                        if (!$record->display_rent_price) {
                            return null;
                        }

                        return "
                            {$record->display_rent_price}
                            <span class=text-xs>/{$record->display_rent_period}</span>
                        ";
                    }
                )
                ->sortable(
                    query: fn(IndividualService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByRentPrice(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: true)
                ->html(),
            Tables\Columns\TextColumn::make('property.owner.name')
                ->label(__('Captador'))
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('property.status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn(PropertyService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPropertyStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn(PropertyService $service, Builder $query, string $direction): Builder =>
                    $service->tableSortByPropertyStatus(query: $query, direction: $direction)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('property.created_at')
                ->label(__('Cadastro'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('property.updated_at')
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
            Tables\Filters\SelectFilter::make('types')
                ->label(__('Tipo(s) do(s) imóvel(is)'))
                ->options(
                    fn(PropertyService $service): array =>
                    $service->getOptionsByPropertyTypesWhereHasProperties(
                        propertableType: MorphMapByClass(static::$model)
                    ),
                )
                ->query(
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyTypes(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('roles')
                ->label(__('Tipo(s) de negociação(ões)'))
                ->options(
                    function (): array {
                        $roles = IndividualRoleEnum::getAssociativeArray();
                        unset($roles[3]);

                        return $roles;
                    }
                )
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByRoles(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('districts')
                ->label(__('Bairro(s)'))
                ->options(
                    fn(PropertyService $service): array =>
                    $service->getOptionsByPropertyDistrictsWhereHasProperties(
                        propertableType: MorphMapByClass(static::$model)
                    ),
                )
                ->query(
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyDistricts(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\Filter::make('sale_price')
                ->label(__('Preço de venda'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\TextInput::make('min_sale_price')
                                ->label(__('Preço de venda (min)'))
                                ->prefix('R$')
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('max_sale_price')) && $state > $get('max_sale_price')) {
                                            $set('max_sale_price', $state);
                                        }
                                    }
                                ),
                            Forms\Components\TextInput::make('max_sale_price')
                                ->label(__('Preço de venda (máx)'))
                                ->prefix('R$')
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('min_sale_price')) && $state < $get('min_sale_price')) {
                                            $set('min_sale_price', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterBySalePrice(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('rent_price')
                ->label(__('Preço do aluguel'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\TextInput::make('min_rent_price')
                                ->label(__('Preço do aluguel (min)'))
                                ->prefix('R$')
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('max_rent_price')) && $state > $get('max_rent_price')) {
                                            $set('max_rent_price', $state);
                                        }
                                    }
                                ),
                            Forms\Components\TextInput::make('max_rent_price')
                                ->label(__('Preço do aluguel (máx)'))
                                ->prefix('R$')
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('min_rent_price')) && $state < $get('min_rent_price')) {
                                            $set('min_rent_price', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByRentPrice(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('condo_price')
                ->label(__('Preço do condomínio'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\TextInput::make('min_condo_price')
                                ->label(__('Condomínio (min)'))
                                // ->numeric()
                                ->prefix('R$')
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('max_condo_price')) && $state > $get('max_condo_price')) {
                                            $set('max_condo_price', $state);
                                        }
                                    }
                                ),
                            Forms\Components\TextInput::make('max_condo_price')
                                ->label(__('Condomínio (máx)'))
                                // ->numeric()
                                ->prefix('R$')
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('min_condo_price')) && $state < $get('min_condo_price')) {
                                            $set('min_condo_price', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyCondoPrice(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('rooms')
                ->label(__('Cômodos'))
                ->form([
                    Forms\Components\Grid::make(['default' => 4])
                        ->schema([
                            Forms\Components\Select::make('bedroom')
                                ->label(__('Quarto(s)'))
                                ->options([
                                    1 => '1',
                                    2 => '2',
                                    3 => '3',
                                    4 => '+4',
                                ])
                                ->native(false),
                            Forms\Components\Select::make('suite')
                                ->label(__('Suíte(s)'))
                                ->options([
                                    1 => '1',
                                    2 => '2',
                                    3 => '3',
                                    4 => '+4',
                                ])
                                ->native(false),
                            Forms\Components\Select::make('bathroom')
                                ->label(__('Banheiro(s)'))
                                ->options([
                                    1 => '1',
                                    2 => '2',
                                    3 => '3',
                                    4 => '+4',
                                ])
                                ->native(false),
                            Forms\Components\Select::make('garage')
                                ->label(__('Vaga(s)'))
                                ->options([
                                    1 => '1',
                                    2 => '2',
                                    3 => '3',
                                    4 => '+4',
                                ])
                                ->native(false),
                        ]),
                ])
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByRooms(query: $query, data: $data)
                )
                ->columnSpan(2),
            Tables\Filters\Filter::make('useful_area')
                ->label(__('Área útil'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\TextInput::make('min_useful_area')
                                ->label(__('Área útil (min)'))
                                // ->numeric()
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('max_useful_area')) && $state > $get('max_useful_area')) {
                                            $set('max_useful_area', $state);
                                        }
                                    }
                                ),
                            Forms\Components\TextInput::make('max_useful_area')
                                ->label(__('Área útil (máx)'))
                                // ->numeric()
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('min_useful_area')) && $state < $get('min_useful_area')) {
                                            $set('min_useful_area', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUsefulArea(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('total_area')
                ->label(__('Área total'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
                        ->schema([
                            Forms\Components\TextInput::make('min_total_area')
                                ->label(__('Área total (min)'))
                                // ->numeric()
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('max_total_area')) && $state > $get('max_total_area')) {
                                            $set('max_total_area', $state);
                                        }
                                    }
                                ),
                            Forms\Components\TextInput::make('max_total_area')
                                ->label(__('Área total (máx)'))
                                // ->numeric()
                                ->mask(
                                    Support\RawJs::make(<<<'JS'
                                        $money($input, ',')
                                    JS)
                                )
                                ->placeholder('0,00')
                                ->maxValue(42949672.95)
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (callable $set, callable $get, ?string $state): void {
                                        if (!empty($get('min_total_area')) && $state < $get('min_total_area')) {
                                            $set('min_total_area', $state);
                                        }
                                    }
                                ),
                        ]),
                ])
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByTotalArea(query: $query, data: $data)
                ),
            Tables\Filters\SelectFilter::make('contact_owners')
                ->label(__('Proprietário(s)'))
                ->options(
                    fn(IndividualService $service): array =>
                    $service->getOptionsByPropertyContactOwnersWhereHasProperties(),
                )
                ->query(
                    fn(IndividualService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyContactOwners(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('owners')
                ->label(__('Captador(es)'))
                ->options(
                    fn(PropertyService $service): array =>
                    $service->getOptionsByPropertyOwnersWhereHasProperties(
                        propertableType: MorphMapByClass(static::$model)
                    ),
                )
                ->query(
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyOwners(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('statuses')
                ->label(__('Status'))
                ->options(PropertyStatusEnum::class)
                ->query(
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyStatuses(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\Filter::make('created_at')
                ->label(__('Cadastro'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
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
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyCreatedAt(query: $query, data: $data)
                ),
            Tables\Filters\Filter::make('updated_at')
                ->label(__('Últ. atualização'))
                ->form([
                    Forms\Components\Grid::make(['default' => 2])
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
                    fn(PropertyService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByPropertyUpdatedAt(query: $query, data: $data)
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
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('images')
                                    ->label(__('Avatar'))
                                    ->hiddenLabel()
                                    ->collection('images')
                                    ->conversion('thumb')
                                    ->circular()
                                    ->limit(1)
                                    ->visible(
                                        fn(?array $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('property.title')
                                    ->label(__('Imóvel'))
                                    ->helperText(
                                        fn(Individual $record): string =>
                                        $record->property->code . ' / ' . $record->property->display_usage
                                    )
                                    ->url(
                                        fn(Individual $record): string =>
                                        route('web.real-estate.properties.show', [
                                            'slug' => $record->property->slug,
                                            'code' => $record->property->code,
                                        ])
                                    )
                                    ->openUrlInNewTab(),
                                Infolists\Components\TextEntry::make('property.type.name')
                                    ->label(__('Tipo do imóvel'))
                                    ->helperText(
                                        fn(Individual $record): ?string =>
                                        $record->property->subtype?->name,
                                    ),
                                Infolists\Components\TextEntry::make('role')
                                    ->label(__('Negociação')),
                                Infolists\Components\TextEntry::make('property.address.display_full_address')
                                    ->label(__('Localização')),
                                Infolists\Components\TextEntry::make('display_sale_price')
                                    ->label(__('Preço da venda (R$)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_rent_price')
                                    ->label(__('Preço do aluguel (R$)'))
                                    ->state(
                                        function (Individual $record): ?string {
                                            if (!$record->display_rent_price) {
                                                return null;
                                            }

                                            return "
                                                {$record->display_rent_price}
                                                <span class=text-xs>/{$record->display_rent_period}</span>
                                            ";
                                        }
                                    )
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    )
                                    ->html(),
                                Infolists\Components\TextEntry::make('property.display_condo_price')
                                    ->label(__('Condomínio /mês (R$)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('property.display_tax_price')
                                    ->label(__('IPTU /ano (R$)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('bedroom')
                                    ->label(__('Quarto(s)'))
                                    ->helperText(
                                        fn(Individual $record): ?string =>
                                        $record->suite ? $record->suite . ' suíte(s)' : null,
                                    )
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('bathroom')
                                    ->label(__('Banheiro(s)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('garage')
                                    ->label(__('Vaga(s)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_useful_area')
                                    ->label(__('Área útil (m²)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_total_area')
                                    ->label(__('Área total (m²)'))
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\Fieldset::make('Publicação nos portais')
                                    ->schema([
                                        Infolists\Components\IconEntry::make('property.publish_on.canal_pro')
                                            ->label(__('Canal Pro'))
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
                                        Infolists\Components\IconEntry::make('property.publish_on.portal_exho')
                                            ->label(__('Portal Exho'))
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
                                        Infolists\Components\IconEntry::make('property.publish_on.portal_web')
                                            ->label(__('Portal Web'))
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
                                                Infolists\Components\TextEntry::make('property.meta_title')
                                                    ->label(__('Título SEO'))
                                                    ->visible(
                                                        fn(array|string|null $state): bool =>
                                                        (is_array($state) && !empty($state[0])) ||
                                                        (!is_array($state) && !empty($state)),
                                                    ),
                                                Infolists\Components\TextEntry::make('property.meta_description')
                                                    ->label(__('Descrição SEO'))
                                                    ->visible(
                                                        fn(array|string|null $state): bool =>
                                                        (is_array($state) && !empty($state[0])) ||
                                                        (!is_array($state) && !empty($state)),
                                                    ),
                                                Infolists\Components\TextEntry::make('property.order')
                                                    ->label(__('Ordem')),
                                                Infolists\Components\IconEntry::make('property.featured')
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
                                                Infolists\Components\IconEntry::make('property.comment')
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
                                                    )

                                            ])
                                            ->visible(
                                                fn(Individual $record): bool =>
                                                $record->property->publish_on['portal_web']
                                            ),
                                        Infolists\Components\TextEntry::make('property.status')
                                            ->label(__('Status'))
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('property.publish_at')
                                            ->label(__('Dt. publicação'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('property.expiration_at')
                                            ->label(__('Dt. expiração'))
                                            ->dateTime('d/m/Y H:i')
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                                Infolists\Components\Grid::make(['default' => 3])
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.created_at')
                                            ->label(__('Cadastro'))
                                            ->dateTime('d/m/Y H:i'),
                                        Infolists\Components\TextEntry::make('property.updated_at')
                                            ->label(__('Últ. atualização'))
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Descrição e Características'))
                            ->schema([
                                Infolists\Components\TextEntry::make('property.body')
                                    ->label(__('Descrição'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->html()
                                    ->columnSpanFull(),
                                Infolists\Components\Fieldset::make('Diferenciais')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.differences_characteristics')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->separator(',')
                                            ->icon('heroicon-o-check-badge')
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(
                                        fn(Individual $record): bool =>
                                        !empty($record->property->differences_characteristics),
                                    )
                                    ->columnSpanFull(),
                                Infolists\Components\Fieldset::make('Lazer e Esportes')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.leisure_characteristics')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->separator(',')
                                            ->icon('heroicon-o-check-badge')
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(
                                        fn(Individual $record): bool =>
                                        !empty($record->property->leisure_characteristics),
                                    )
                                    ->columnSpanFull(),
                                Infolists\Components\Fieldset::make('Segurança')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.security_characteristics')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->separator(',')
                                            ->icon('heroicon-o-check-badge')
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(
                                        fn(Individual $record): bool =>
                                        !empty($record->property->security_characteristics),
                                    )
                                    ->columnSpanFull(),
                                Infolists\Components\Fieldset::make('Comodidades e serviços')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.infrastructure_characteristics')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->separator(',')
                                            ->icon('heroicon-o-check-badge')
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(
                                        fn(Individual $record): bool =>
                                        !empty($record->property->infrastructure_characteristics),
                                    )
                                    ->columnSpanFull(),
                                Infolists\Components\Fieldset::make('Características do condomínio')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.floors')
                                            ->label(__('Nº de andares'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                        Infolists\Components\TextEntry::make('property.units_per_floor')
                                            ->label(__('Unidades por andar'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                        Infolists\Components\TextEntry::make('property.towers')
                                            ->label(__('Nº de torres'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                        Infolists\Components\TextEntry::make('property.construct_year')
                                            ->label(__('Ano de construção'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                    ])
                                    ->hidden(
                                        fn(Individual $record): bool =>
                                        empty($record->property->floors) &&
                                        empty($record->property->units_per_floor) &&
                                        empty($record->property->towers) &&
                                        empty($record->property->construct_year),
                                    )
                                    ->columns(4)
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('property.tags')
                                    ->label(__('Tag(s)'))
                                    ->badge()
                                    ->separator(',')
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->columnSpanFull(),
                            ]),
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
                                Infolists\Components\RepeatableEntry::make('property.embed_videos')
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
                                Infolists\Components\TextEntry::make('property.url')
                                    ->label(__('Tour virtual'))
                                    ->url(
                                        fn(Individual $record): string =>
                                        $record->property->url
                                    )
                                    ->openUrlInNewTab()
                                    ->visible(
                                        fn(?string $state): bool =>
                                        !empty($state),
                                    ),
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Dados da Captação e Privativos'))
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('property.contacts')
                                    ->label('Proprietário(s)')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label(__('Nome')),
                                        Infolists\Components\TextEntry::make('email')
                                            ->label(__('Email'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                        Infolists\Components\TextEntry::make('display_main_phone')
                                            ->label(__('Telefone'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                    ])
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->hidden(
                                        function (Individual $record): bool {
                                            $user = auth()->user();

                                            if ($user->id === $record->property->user_id) {
                                                return false;
                                            }

                                            if (
                                                $record->property->owner->hasRole('Captador') ||
                                                $user->hasRole('Superadministrador') ||
                                                $user->hasRole('Administrador')
                                            ) {
                                                return false;
                                            }

                                            return true;
                                        }
                                    )
                                    ->columns(3)
                                    ->columnSpanFull(),
                                Infolists\Components\Fieldset::make('Captador')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('property.owner.name')
                                            ->label(__('Nome')),
                                        Infolists\Components\TextEntry::make('property.owner.email')
                                            ->label(__('Email'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                        Infolists\Components\TextEntry::make('property.owner.display_main_phone')
                                            ->label(__('Telefone'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            ),
                                        Infolists\Components\TextEntry::make('property.owner_notes')
                                            ->label(__('Dados privativos da captação'))
                                            ->visible(
                                                fn(array|string|null $state): bool =>
                                                (is_array($state) && !empty($state[0])) ||
                                                (!is_array($state) && !empty($state)),
                                            )
                                            ->hidden(
                                                function (Individual $record): bool {
                                                    $user = auth()->user();

                                                    if ($user->id === $record->property->user_id) {
                                                        return false;
                                                    }

                                                    if (
                                                        $user->hasRole('Superadministrador') ||
                                                        $user->hasRole('Administrador')
                                                    ) {
                                                        return false;
                                                    }

                                                    return true;
                                                }
                                            )
                                            ->html()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIndividuals::route('/'),
            'create' => Pages\CreateIndividual::route('/create'),
            'edit'   => Pages\EditIndividual::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'media',
                'property',
                'property.address',
                'property.type',
                'property.subtype',
                'property.characteristics',
                'property.owner',
                'property.contacts',
            ])
            ->whereHas('property');
    }
}
