<?php

namespace App\Services\Crm\Contacts;

use App\Enums\ProfileInfos\UserStatusEnum;
use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Contacts\Individual;
use App\Models\Crm\Contacts\LegalEntity;
use App\Models\Crm\Contacts\Role;
use App\Models\Crm\Source;
use App\Models\System\User;
use App\Services\BaseService;
use App\Services\Crm\SourceService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Support;
use Illuminate\Database\Eloquent\Model;

class ContactService extends BaseService
{
    protected string $contactTable;

    public function __construct(protected Contact $contact)
    {
        $this->contactTable = $contact->getTable();
    }

    public function tableSearchByNameAndContactableCpfOrCnpj(Builder $query, string $search): Builder
    {
        return $query->whereHas('contactable', function (Builder $query) use ($search): Builder {
            return $query->when(
                $query->getModel()->getTable() === 'crm_contact_individuals',
                function ($query) use ($search): Builder {
                    return $query->where('cpf', 'like', "%{$search}%")
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"]);
                }
            )
                ->when(
                    $query->getModel()->getTable() === 'crm_contact_legal_entities',
                    function ($query) use ($search): Builder {
                        return $query->where('cnpj', 'like', "%{$search}%")
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"]);
                    }
                );
        })
            ->orWhere('name', 'like', "%{$search}%");
    }

    public function tableSearchByPhone(Builder $query, string $search): Builder
    {
        return $query->whereRaw("JSON_EXTRACT(phones, '$[0].number') LIKE ?", ["%$search%"]);
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = UserStatusEnum::getAssociativeArray();

        $matchingStatuses = [];
        foreach ($statuses as $index => $status) {
            if (stripos($status, $search) !== false) {
                $matchingStatuses[] = $index;
            }
        }

        if ($matchingStatuses) {
            return $query->whereIn('status', $matchingStatuses);
        }

        return $query;
    }

    public function tableSortByStatus(Builder $query, string $direction): Builder
    {
        $statuses = UserStatusEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($statuses as $key => $status) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $status;
        }

        $orderByCase = "CASE status " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
    }

    public function getQueryBySourcesWhereHasContacts(Builder $query): Builder
    {
        return $query->whereHas('contacts')
            ->orderBy('id', 'asc');
    }

    public function getQueryByOwnersWhereHasContacts(Builder $query): Builder
    {
        return $query->whereHas('contacts')
            ->orderBy('id', 'asc');
    }

    public function getOptionsByContactRolesWhereHasContacts(string $contactableType): array
    {
        return Role::whereHas('contacts', function (Builder $query) use ($contactableType): Builder {
            return $query->where('contactable_type', $contactableType);
        })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByContactRoles(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('contact', function (Builder $query) use ($data): Builder {
            return $query->whereHas('roles', function (Builder $query) use ($data): Builder {
                return $query->whereIn('role_id', $data['values']);
            });
        });
    }

    public function getOptionsByContactSourcesWhereHasContacts(string $contactableType): array
    {
        return Source::whereHas('contacts', function (Builder $query) use ($contactableType): Builder {
            return $query->where('contactable_type', $contactableType);
        })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByContactSources(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('contact', function (Builder $query) use ($data): Builder {
            return $query->whereIn('source_id', $data['values']);
        });
    }

    public function getOptionsByContactOwnersWhereHasContacts(string $contactableType): array
    {
        return User::whereHas('contacts', function (Builder $query) use ($contactableType): Builder {
            return $query->where('contactable_type', $contactableType);
        })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByContactOwners(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('contact', function (Builder $query) use ($data): Builder {
            return $query->whereIn('user_id', $data['values']);
        });
    }

    public function tableFilterByContactCreatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['created_from'],
                fn (Builder $query, $date): Builder =>
                $query->whereHas('contact', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('created_at', '>=', $date);
                }),
            )
            ->when(
                $data['created_until'],
                fn (Builder $query, $date): Builder =>
                $query->whereHas('contact', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('created_at', '<=', $date);
                }),
            );
    }

    public function tableFilterByContactUpdatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['updated_from'],
                fn (Builder $query, $date): Builder =>
                $query->whereHas('contact', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('updated_at', '>=', $date);
                }),
            )
            ->when(
                $data['updated_until'],
                fn (Builder $query, $date): Builder =>
                $query->whereHas('contact', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('updated_at', '<=', $date);
                }),
            );
    }

    public function getContactOptionsBySearch(?string $search): array
    {
        $query = $this->contact->with('contactable')
            ->byStatuses([1])
            ->whereHas('contactable', function (Builder $query) use ($search): Builder {
                return $query->when($query->getModel()->getTable() === 'crm_contact_individuals', function ($query) use ($search): Builder {
                    return $query->where('cpf', 'like', "%{$search}%")
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"]);
                })
                    ->when($query->getModel()->getTable() === 'crm_contact_legal_entities', function ($query) use ($search): Builder {
                        return $query->where('cnpj', 'like', "%{$search}%")
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"]);
                    })
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->limit(50);

        $user = auth()->user();

        if ($user->hasAnyRole(['Superadministrador', 'Administrador'])) {
            return $query->get()
                ->mapWithKeys(function ($item): array {
                    $cpfCnpj = $item->contactable->cpf ?? $item->contactable->cnpj;
                    $contactableName = !empty($cpfCnpj) ? $item->name . ' - ' . $cpfCnpj : $item->name;
                    return [$item->id => $contactableName];
                })
                ->toArray();
        }

        if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
            $teamUserIds = $user->teams()
                ->with('users:id')
                ->get()
                ->pluck('users.*.id')
                ->flatten()
                ->unique()
                ->toArray();

            return $query->whereIn('user_id', $teamUserIds)
                ->get()
                ->mapWithKeys(function ($item): array {
                    $cpfCnpj = $item->contactable->cpf ?? $item->contactable->cnpj;
                    $contactableName = !empty($cpfCnpj) ? $item->name . ' - ' . $cpfCnpj : $item->name;
                    return [$item->id => $contactableName];
                })
                ->toArray();
        }

        return $query->where('user_id', $user->id)
            ->get()
            ->mapWithKeys(function ($item): array {
                $cpfCnpj = $item->contactable->cpf ?? $item->contactable->cnpj;
                $contactableName = !empty($cpfCnpj) ? $item->name . ' - ' . $cpfCnpj : $item->name;
                return [$item->id => $contactableName];
            })
            ->toArray();
    }

    // Single
    public function getContactOptionLabel(int $value): string
    {
        $contact = $this->contact->find($value);

        $cpfCnpj = $contact->contactable->cpf ?? $contact->contactable->cnpj;

        return !empty($cpfCnpj) ? $contact->name . ' - ' . $cpfCnpj : $contact->name;
    }

    // Multiple
    public function getContactOptionsLabel(array $values): array
    {
        return $this->contact->whereIn('id', $values)
            ->get()
            ->mapWithKeys(function ($item): array {
                $cpfCnpj = $item->contactable->cpf ?? $item->contactable->cnpj;
                $contactableName = !empty($cpfCnpj) ? $item->name . ' - ' . $cpfCnpj : $item->name;
                return [$item->id => $contactableName];
            })
            ->toArray();
    }

    public function getQuickCreateActionByContacts(string $field, bool $multiple = false): Forms\Components\Actions\Action
    {
        $individualContactable  = MorphMapByClass(model: Individual::class);
        $legalEntityContactable = MorphMapByClass(model: LegalEntity::class);

        return Forms\Components\Actions\Action::make($field)
            ->label(__('Criar Contato'))
            ->icon('heroicon-o-plus')
            ->form([
                Forms\Components\Grid::make(['default' => 2])
                    ->schema([
                        Forms\Components\Radio::make('contactable_type')
                            ->label(__('Tipo de contato'))
                            ->options([
                                $individualContactable  => 'P. Física',
                                $legalEntityContactable => 'P. Jurídica',
                            ])
                            ->default($individualContactable)
                            ->in([$individualContactable, $legalEntityContactable])
                            ->inline()
                            ->inlineLabel(false)
                            ->live()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact.name')
                            ->label(__('Nome'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\Hidden::make('contact.phones.0.name')
                            ->default(null),
                        Forms\Components\TextInput::make('contact.email')
                            ->label(__('Email'))
                            ->email()
                            ->rules([
                                function (callable $get): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ) use ($get): void {
                                        $this->validateEmail(
                                            attribute: $attribute,
                                            contactableType: $get('contactable_type'),
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->maxLength(255),
                        Forms\Components\Fieldset::make(__('Tipos de contato'))
                            ->schema([
                                Forms\Components\CheckboxList::make('contact.roles')
                                    ->hiddenLabel()
                                    ->options(
                                        fn(RoleService $service): array =>
                                        $service->getOptionsByActiveContactRoles()
                                    )
                                    ->columns(6)
                                    ->gridDirection('row')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6),
                        Forms\Components\TextInput::make('contact.phones.0.number')
                            ->label(__('Nº do telefone'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $input.length === 14 ? '(99) 9999-9999' : '(99) 99999-9999'
                                JS)
                            )
                            ->live(onBlur: true)
                            ->rules([
                                function (callable $get): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ) use ($get): void {
                                        $this->validatePhone(
                                            attribute: $attribute,
                                            contactableType: $get('contactable_type'),
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cpf')
                            ->label(__('CPF'))
                            ->mask('999.999.999-99')
                            ->rules([
                                function (IndividualService $service): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ) use ($service): void {
                                        $service->validateCpf(
                                            record: null,
                                            attribute: $attribute,
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->maxLength(255)
                            ->visible(
                                fn(callable $get): bool =>
                                $get('contactable_type') === $individualContactable
                            ),
                        Forms\Components\TextInput::make('cnpj')
                            ->label(__('CNPJ'))
                            ->mask('99.999.999/9999-99')
                            ->rules([
                                function (LegalEntityService $service): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ) use ($service): void {
                                        $service->validateCnpj(
                                            record: null,
                                            attribute: $attribute,
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->maxLength(255)
                            ->visible(
                                fn(callable $get): bool =>
                                $get('contactable_type') === $legalEntityContactable
                            ),
                        // Forms\Components\Select::make('contact.source_id')
                        //     ->label(__('Origem da captação'))
                        //     ->options(
                        //         fn(SourceService $service): array =>
                        //         $service->getOptionsByActiveSources(),
                        //     )
                        //     ->searchable()
                        //     ->preload(),

                    ])
            ])
            ->action(
                function (array $data, string|array|null $state, callable $set) use (
                    $individualContactable,
                    $legalEntityContactable,
                    $field,
                    $multiple
                ): void {
                    if ($data['contactable_type'] === $individualContactable) {
                        $contactable = Individual::create($data);
                    } elseif ($data['contactable_type'] === $legalEntityContactable) {
                        $contactable = LegalEntity::create($data);
                    }

                    $data['contact']['user_id'] = auth()->user()->id;

                    $contact = $contactable->contact()
                        ->create($data['contact']);

                    $contact->roles()
                        ->sync($data['contact']['roles']);

                    if ($multiple) {
                        array_push($state, $contact->id);
                        $set($field, $state);
                    } else {
                        $set($field, $contact->id);
                    }
                }
            );
    }

    public function validateEmail(string $attribute, string $contactableType, string $state, Closure $fail): void
    {
        $userId = auth()->user()->id;

        $exists = $this->contact->where('email', $state)
            ->where('user_id', $userId)
            ->where('contactable_type', $contactableType)
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo email já está em uso.', ['attribute' => $attribute]));
        }
    }

    public function validatePhone(string $attribute, string $contactableType, string $state, Closure $fail): void
    {
        $userId = auth()->user()->id;

        $exists = $this->contact->whereRaw("JSON_EXTRACT(phones, '$[0].number') = ?", ["$state"])
            ->where('user_id', $userId)
            ->where('contactable_type', $contactableType)
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo telefone já está em uso.', ['attribute' => $attribute]));
        }
    }
}
