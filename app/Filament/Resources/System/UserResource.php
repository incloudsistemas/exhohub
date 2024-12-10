<?php

namespace App\Filament\Resources\System;

use App\Enums\ProfileInfos\EducationalLevelEnum;
use App\Enums\ProfileInfos\GenderEnum;
use App\Enums\ProfileInfos\MaritalStatusEnum;
use App\Enums\ProfileInfos\UfEnum;
use App\Enums\ProfileInfos\UserStatusEnum;
use App\Filament\Resources\System\UserResource\Pages;
use App\Filament\Resources\System\UserResource\RelationManagers;
use App\Models\System\User;
use App\Models\System\UserCreciStage;
use App\Services\Polymorphics\AddressService;
use App\Services\Support\DepartmentService;
use App\Services\System\CreciControlStageService;
use App\Services\System\RoleService;
use App\Services\System\TeamService;
use App\Services\System\UserService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // protected static ?string $slug = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Infos. Gerais e Acesso'))
                            ->schema([
                                static::getGeneralInfosFormSection(),
                                static::getSystemAccessFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Infos. Complementares e Endereço'))
                            ->schema([
                                static::getAdditionalInfosFormSection(),
                                static::getAddressFormSection(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Documentação'))
                            ->schema([
                                static::getRequiredAttachmentsSection(),
                            ])
                            ->visibleOn('edit'),
                        Forms\Components\Tabs\Tab::make(__('Controle de CRECI'))
                            ->schema([
                                static::getCreciControlSection(),
                            ])
                            ->visibleOn('edit')
                            ->hidden(
                                fn (callable $get): bool =>
                                !in_array(6, $get('roles')) // 6 - Corretor/Realtor
                            ),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected static function getGeneralInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Gerais'))
            ->description(__('Visão geral e informações fundamentais sobre o usuário.'))
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->confirmed()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn (callable $set, ?string $state): ?string =>
                        $set('email_confirmation', $state)
                    )
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('additional_emails')
                    ->label(__('Email(s) adicional(is)'))
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label(__('Tipo de email'))
                            ->helperText(__('Nome identificador. Ex: Pessoal, Trabalho...'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->datalist([
                                'Pessoal',
                                'Trabalho',
                                'Outros'
                            ])
                            ->autocomplete(false),
                    ])
                    ->itemLabel(
                        fn (array $state): ?string =>
                        $state['email'] ?? null
                    )
                    ->addActionLabel(__('Adicionar email'))
                    ->defaultItems(0)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
                Forms\Components\Repeater::make('phones')
                    ->label(__('Telefone(s) de contato'))
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label(__('Nº do telefone'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $input.length === 14 ? '(99) 9999-9999' : '(99) 99999-9999'
                                JS)
                            )
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label(__('Tipo de contato'))
                            ->helperText(__('Nome identificador. Ex: Celular, Whatsapp, Casa, Trabalho...'))
                            ->minLength(2)
                            ->maxLength(255)
                            ->datalist([
                                'Celular',
                                'Whatsapp',
                                'Casa',
                                'Trabalho',
                                'Outros'
                            ])
                            ->autocomplete(false),
                    ])
                    ->itemLabel(
                        fn (array $state): ?string =>
                        $state['number'] ?? null
                    )
                    ->addActionLabel(__('Adicionar telefone'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->collapseAllAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->label(__('Minimizar todos'))
                    )
                    ->deleteAction(
                        fn (Forms\Components\Actions\Action $action) =>
                        $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->columns(2),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getSystemAccessFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Acesso ao Sistema'))
            ->description(__('Gerencie o nível de acesso do usuário.'))
            ->schema([
                Forms\Components\TextInput::make('email_confirmation')
                    ->label(__('Usuário'))
                    ->placeholder(__('Preencha o email'))
                    ->required()
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Select::make('roles')
                    ->label(__('Nível(is) de acesso(s)'))
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (RoleService $service, Builder $query): Builder =>
                        $service->getQueryByAuthUserRoles(query: $query)
                    )
                    ->multiple()
                    // ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->live()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('password')
                    ->label(__('Senha'))
                    ->password()
                    ->helperText(
                        fn (string $operation): string =>
                        $operation === 'create'
                            ? __('Senha com mín. de 8 digitos.')
                            : __('Preencha apenas se desejar alterar a senha. Min. de 8 dígitos.')
                    )
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required(
                        fn (string $operation): bool =>
                        $operation === 'create'
                    )
                    ->confirmed()
                    ->minLength(8)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(__('Confirmar senha'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required(
                        fn (string $operation): bool =>
                        $operation === 'create'
                    )
                    ->maxLength(255)
                    ->dehydrated(false),
                Forms\Components\Select::make('teams.director')
                    ->label(__('Equipe(s) do diretor'))
                    ->options(
                        fn (TeamService $service): array =>
                        $service->getOptionsByGroupedAgencies(),
                    )
                    ->multiple()
                    ->required()
                    ->native(false)
                    ->hidden(
                        fn (callable $get): bool =>
                        !in_array(4, $get('roles')) // 4 - Diretor/Director
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('teams.manager')
                    ->label(__('Equipe(s) do gerente'))
                    ->options(
                        fn (TeamService $service): array =>
                        $service->getOptionsByGroupedAgencies(),
                    )
                    ->multiple()
                    ->required()
                    ->native(false)
                    ->hidden(
                        fn (callable $get): bool =>
                        !in_array(5, $get('roles')) // 5 - Gerente/Manager
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('teams.realtor')
                    ->label(__('Equipe(s) do corretor'))
                    ->options(
                        fn (TeamService $service): array =>
                        $service->getOptionsByGroupedAgencies(),
                    )
                    ->multiple()
                    ->required()
                    ->native(false)
                    ->hidden(
                        fn (callable $get): bool =>
                        !in_array(6, $get('roles')) // 6 - Corretor/Realtor
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('departments')
                    ->label(__('Departamento(s)'))
                    ->relationship(
                        name: 'departments',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(DepartmentService $service, Builder $query): Builder =>
                        $service->getQueryByDepartments(query: $query)
                    )
                    ->multiple()
                    // ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->hidden(
                        fn (callable $get): bool =>
                        !in_array(8, $get('roles')) // 8 - Suporte/Support
                    )
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(UserStatusEnum::class)
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->native(false),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAdditionalInfosFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Infos. Complementares'))
            ->description(__('Forneça informações adicionais relevantes.'))
            ->schema([
                Forms\Components\TextInput::make('cpf')
                    ->label(__('CPF'))
                    ->mask('999.999.999-99')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('rg')
                    ->label(__('RG'))
                    ->maxLength(255),
                Forms\Components\Select::make('gender')
                    ->label(__('Sexo'))
                    ->options(GenderEnum::class)
                    ->native(false),
                Forms\Components\DatePicker::make('birth_date')
                    ->label(__('Dt. nascimento'))
                    ->format('d/m/Y')
                    ->maxDate(now()),
                Forms\Components\Select::make('marital_status')
                    ->label(__('Estado civil'))
                    ->options(MaritalStatusEnum::class)
                    ->searchable()
                    ->native(false),
                Forms\Components\Select::make('educational_level')
                    ->label(__('Escolaridade'))
                    ->options(EducationalLevelEnum::class)
                    ->searchable()
                    ->native(false),
                Forms\Components\TextInput::make('nationality')
                    ->label(__('Nacionalidade'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('citizenship')
                    ->label(__('Naturalidade'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('complement')
                    ->label(__('Sobre'))
                    ->rows(4)
                    ->minLength(2)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('Avatar'))
                    ->helperText(__('Tipos de arquivo permitidos: .png, .jpg, .jpeg, .gif. // Máx. 500x500px // 5 mb.'))
                    ->collection('avatar')
                    ->image()
                    // ->responsiveImages()
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file, callable $get): string =>
                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                            ->prepend(Str::slug($get('name'))),
                    )
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('500')
                    ->imageResizeTargetHeight('500')
                    ->imageResizeUpscale(false)
                    ->maxSize(5120),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getAddressFormSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Endereço'))
            ->description(__('Detalhes do endereço residencial do usuário.'))
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('address.zipcode')
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
                                        $set('address.uf', null);
                                        $set('address.city', null);
                                        $set('address.district', null);
                                        $set('address.address_line', null);
                                        $set('address.complement', null);
                                    } else {
                                        $set('address.uf', $address['uf']);
                                        $set('address.city', $address['localidade']);
                                        $set('address.district', $address['bairro']);
                                        $set('address.address_line', $address['logradouro']);
                                        $set('address.complement', $address['complemento']);
                                    }
                                }
                            )
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Select::make('address.uf')
                    ->label(__('Estado'))
                    ->options(UfEnum::class)
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->native(false),
                Forms\Components\TextInput::make('address.city')
                    ->label(__('Cidade'))
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.district')
                    ->label(__('Bairro'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.address_line')
                    ->label(__('Endereço'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.number')
                    ->label(__('Número'))
                    ->minLength(2)
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.complement')
                    ->label(__('Complemento'))
                    ->helperText(__('Apto / Bloco / Casa'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('address.reference')
                    ->label(__('Ponto de referência'))
                    ->maxLength(255),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getRequiredAttachmentsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Documentação Obrigatória'))
            ->description(__('Envio e verificação dos documentos necessários para o cadastro.'))
            ->schema([
                Forms\Components\Fieldset::make()
                    ->label(__('Certidão de nascimento'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.birth')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Certidão de nascimento')
                            ->collection('birth')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Certidão de nascimento')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Forms\Components\Fieldset::make()
                    ->label(__('Documento de identidade'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.identity')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Documento de identidade')
                            ->collection('identity')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Documento de identidade')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Forms\Components\Fieldset::make()
                    ->label(__('Comprovante de endereço'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.address')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Comprovante de endereço')
                            ->collection('address')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Comprovante de endereço')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Forms\Components\Fieldset::make()
                    ->label(__('Título reservista'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.reservist')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Título reservista')
                            ->collection('reservist')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Título reservista')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Forms\Components\Fieldset::make()
                    ->label(__('Certidão negativa civil'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.civil_negative')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Certidão negativa civil')
                            ->collection('civil_negative')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Certidão negativa civil')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Forms\Components\Fieldset::make()
                    ->label(__('Certidão negativa criminal'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.criminal_negative')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Certidão negativa criminal')
                            ->collection('criminal_negative')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Certidão negativa criminal')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Forms\Components\Fieldset::make()
                    ->label(__('Certificado de conclusão do ensino médio'))
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('attachments.high_school')
                            ->label(__('Anexar arquivo'))
                            ->helperText(__('Máx. 5 mb.'))
                            ->mediaName('Certificado de conclusão do ensino médio')
                            ->collection('high_school')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, callable $get): string =>
                                (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                    ->prepend(Str::slug('Certificado de conclusão do ensino médio')),
                            )
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(2)
            ->collapsible();
    }

    protected static function getCreciControlSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('Registro do CRECI do Usuário'))
            ->description(__('Informações e status relacionados ao registro do CRECI do usuário.'))
            ->schema([
                Forms\Components\Select::make('creci.creci_control_stage_id')
                    ->label(__('Etapa'))
                    ->options(
                        fn (CreciControlStageService $service): array =>
                        $service->getOptionsByActiveControlStages(),
                    )
                    ->selectablePlaceholder(false)
                    // ->required()
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function (?string $state, ?string $old, callable $set): void {
                            if ($old == $state) {
                                return;
                            }

                            $set('creci.member_since', date('Y-m-d'));
                            $set('creci.valid_thru', '');
                        }
                    )
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make()
                    ->label(__('Datas'))
                    ->schema([
                        Forms\Components\DatePicker::make('creci.member_since')
                            ->label(__('Membro desde'))
                            ->format('d/m/Y')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('creci.valid_thru')
                            ->label(__('Válido até'))
                            ->format('d/m/Y')
                            ->required()
                            ->minDate(
                                fn (callable $get): string =>
                                $get('creci.member_since'),
                            ),
                    ])
                    ->visible(
                        fn (callable $get): bool =>
                        $get('creci.creci_control_stage_id') != ''
                    ),
                Forms\Components\Fieldset::make('creci.attachments.stage_2')
                    ->label(__('Documentação'))
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_2.registration.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_2.registration.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_2.registration.file_name')
                                    ->label(__('Comprovante de matrícula'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_2.registration.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_2.payment.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_2.payment.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_2.payment.file_name')
                                    ->label(__('Comprovante de pagamento'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_2.payment.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                    ])
                    ->visible(
                        fn (callable $get): bool =>
                        $get('creci.creci_control_stage_id') == 2
                    ),
                Forms\Components\Fieldset::make('creci.attachments.stage_3')
                    ->label(__('Documentação'))
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_3.internship.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_3.internship.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_3.internship.file_name')
                                    ->label(__('Termo de compromisso de estágio'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_3.internship.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_3.frequency.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_3.frequency.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_3.frequency.file_name')
                                    ->label(__('Declaração de frequência'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_3.frequency.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_3.protocol.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_3.protocol.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_3.protocol.file_name')
                                    ->label(__('Nº de protocolo'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_3.protocol.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                    ])
                    ->visible(
                        fn (callable $get): bool =>
                        $get('creci.creci_control_stage_id') == 3
                    ),
                Forms\Components\Fieldset::make('creci.attachments.stage_4')
                    ->label(__('Documentação'))
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_4.protocol.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_4.protocol.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_4.protocol.file_name')
                                    ->label(__('Nº de protocolo de renovação'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_4.protocol.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                    ])
                    ->visible(
                        fn (callable $get): bool =>
                        $get('creci.creci_control_stage_id') == 4
                    ),
                Forms\Components\Fieldset::make('creci.attachments.stage_5')
                    ->label(__('Documentação'))
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_5.protocol.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_5.protocol.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_5.protocol.file_name')
                                    ->label(__('Nº de protocolo'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_5.protocol.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('creci.attachments.stage_5.creci.collection_name')
                                    ->label(__('Coleção'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\TextInput::make('creci.attachments.stage_5.creci.name')
                                    ->label(__('Nome'))
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->hidden(),
                                Forms\Components\FileUpload::make('creci.attachments.stage_5.creci.file_name')
                                    ->label(__('Carteira CRECI'))
                                    ->helperText(__('Máx. 5 mb.'))
                                    ->disk('public')
                                    ->directory('user-attachments')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file, callable $get): string =>
                                        (string) str('-' . md5(uniqid()) . '-' . time() . '.' . $file->extension())
                                            ->prepend(Str::slug($get('creci.attachments.stage_5.creci.name'))),
                                    )
                                    ->required()
                                    ->maxSize(5120)
                                    ->downloadable(),
                            ]),
                    ])
                    ->visible(
                        fn (callable $get): bool =>
                        $get('creci.creci_control_stage_id') == 5
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
                                        fn (User $record): string =>
                                        self::getUrl('edit', ['record' => $record]),
                                    )
                                    ->hidden(
                                        fn (): bool =>
                                        !auth()->user()->can('Editar Usuários')
                                    ),
                            ]),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make()
                        ->before(
                            fn (UserService $service, Tables\Actions\DeleteAction $action, User $record) =>
                            $service->preventUserDeleteIf(action: $action, user: $record)
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\SpatieMediaLibraryImageColumn::make('avatar')
                ->label('')
                ->collection('avatar')
                ->conversion('thumb')
                ->size(45)
                ->circular(),
            Tables\Columns\TextColumn::make('name')
                ->label(__('Nome'))
                ->description(
                    fn (User $record): ?string =>
                    $record->cpf,
                )
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByNameAndCpf(query: $query, search: $search)
                )
                ->sortable(),
            Tables\Columns\TextColumn::make('roles.name')
                ->label(__('Nível(is) de acesso(s)'))
                ->badge()
                ->searchable()
                // ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('email')
                ->label(__('Email'))
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('display_main_phone')
                ->label(__('Telefone'))
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByPhone(query: $query, search: $search)
                )
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('teams.name')
                ->label(__('Equipe(s)'))
                ->badge()
                ->searchable()
                // ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('display_active_creci_stage')
                ->label(__('Etapa CRECI'))
                ->badge()
                ->color(
                    function (User $record): string {
                        if ($record->active_creci_stage->valid_thru == date('Y-m-d')) {
                            return 'warning';
                        }

                        if ($record->active_creci_stage->valid_thru < date('Y-m-d')) {
                            return 'danger';
                        }

                        return 'success';
                    }
                )
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByCreciStage(query: $query, search: $search)
                )
                // ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->searchable(
                    query: fn (UserService $service, Builder $query, string $search): Builder =>
                    $service->tableSearchByStatus(query: $query, search: $search)
                )
                ->sortable(
                    query: fn (UserService $service, Builder $query, string $direction): Builder =>
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
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('roles')
                ->label(__('Nível(is) de acesso(s)'))
                ->relationship(
                    name: 'roles',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (RoleService $service, Builder $query): Builder =>
                    $service->getQueryByAuthUserRoles(query: $query)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('teams')
                ->label(__('Equipe(s)'))
                ->options(
                    fn (UserService $service): array =>
                    $service->getOptionsByTeamsByGroupedAgencies(),
                )
                ->query(
                    fn (UserService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByTeams(query: $query, data: $data)
                )
                ->multiple()
                ->preload(),
            Tables\Filters\SelectFilter::make('active_creci_stage')
                ->label(__('Etapa(s) CRECI'))
                ->options(
                    fn (UserService $service): array =>
                    $service->getOptionsByCreciStages(),
                )
                ->query(
                    fn (UserService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByCreciStages(query: $query, data: $data)
                )
                ->multiple(),
            Tables\Filters\SelectFilter::make('status')
                ->label(__('Status'))
                ->multiple()
                ->options(UserStatusEnum::class),
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
                    fn (UserService $service, Builder $query, array $data): Builder =>
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
                    fn (UserService $service, Builder $query, array $data): Builder =>
                    $service->tableFilterByUpdatedAt(query: $query, data: $data)
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
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('avatar')
                                    ->label(__('Avatar'))
                                    ->hiddenLabel()
                                    ->collection('avatar')
                                    ->conversion('thumb')
                                    ->circular()
                                    ->visible(
                                        fn (?array $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('Nome')),
                                Infolists\Components\TextEntry::make('roles.name')
                                    ->label(__('Nível(is) de acesso(s)'))
                                    ->badge(),
                                Infolists\Components\TextEntry::make('teams.name')
                                    ->label(__('Equipe(s)'))
                                    ->badge()
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('email')
                                    ->label(__('Email')),
                                Infolists\Components\TextEntry::make('display_additional_emails')
                                    ->label(__('Emails adicionais'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('display_main_phone_with_name')
                                    ->label(__('Telefone'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_additional_phones')
                                    ->label(__('Telefones adicionais'))
                                    ->visible(
                                        fn(array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    ),
                                Infolists\Components\TextEntry::make('cpf')
                                    ->label(__('CPF'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('rg')
                                    ->label(__('RG'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('gender')
                                    ->label(__('Sexo'))
                                    ->visible(
                                        fn (?GenderEnum $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('display_birth_date')
                                    ->label(__('Dt. nascimento'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('marital_status')
                                    ->label(__('Estado civil'))
                                    ->visible(
                                        fn (?MaritalStatusEnum $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('educational_level')
                                    ->label(__('Escolaridade'))
                                    ->visible(
                                        fn (?EducationalLevelEnum $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('nationality')
                                    ->label(__('Nacionalidade'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('citizenship')
                                    ->label(__('Naturalidade'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    ),
                                Infolists\Components\TextEntry::make('complement')
                                    ->label(__('Sobre'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    )
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('address.display_full_address')
                                    ->label(__('Endereço'))
                                    ->visible(
                                        fn (?string $state): bool =>
                                        !empty($state),
                                    )
                                    ->columnSpanFull(),
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
                            ]),
                        Infolists\Components\Tabs\Tab::make(__('Documentação'))
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('required_attachments')
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
                                                new HtmlString('<a href="' . url('storage/' . $record->id . '/' . $record->file_name) . '" target="_blank">Download</a>')
                                            )
                                            ->hintIcon('heroicon-s-arrow-down-tray')
                                            ->hintColor('primary'),
                                    ])
                                    ->visible(
                                        fn (array|string|null $state): bool =>
                                        (is_array($state) && !empty($state[0])) ||
                                        (!is_array($state) && !empty($state)),
                                    )
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                            ->visible(
                                fn (User $record): bool =>
                                $record->required_attachments->count() > 0
                            ),
                        Infolists\Components\Tabs\Tab::make(__('Controle de CRECI'))
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('userCreciStages')
                                    ->hiddenLabel()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('creciControlStage.name')
                                            ->label(__('Etapa'))
                                            ->badge()
                                            ->color(
                                                function (UserCreciStage $record): string {
                                                    if ($record->valid_thru == date('Y-m-d')) {
                                                        return 'warning';
                                                    }

                                                    if ($record->valid_thru < date('Y-m-d')) {
                                                        return 'danger';
                                                    }

                                                    return 'success';
                                                }
                                            ),
                                        Infolists\Components\TextEntry::make('member_since')
                                            ->label(__('Membro desde'))
                                            ->date('d/m/Y'),
                                        Infolists\Components\TextEntry::make('valid_thru')
                                            ->label(__('Válido até'))
                                            ->date('d/m/Y'),
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
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                            ->visible(
                                fn (User $record): bool =>
                                $record->hasRole('Corretor')
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
            RelationManagers\UserCreciStagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->byAuthUserRoles(user: $user);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'cpf'];
    }
}
