<?php

namespace App\Services\Crm\Contacts;

use App\Models\Crm\Contacts\Contact;
use App\Models\Crm\Contacts\Individual;
use App\Services\BaseService;
use App\Services\Crm\SourceService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Support;

class IndividualService extends BaseService
{
    public function __construct(protected Contact $contact, protected Individual $individual)
    {
        //
    }

    public function getIndividualOptionsBySearch(?string $search, $return = 'individualId'): array
    {
        return $this->individual->whereHas('contact', function (Builder $query) use ($search): Builder {
            $query->where('status', 1);

            if (!empty($search)) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            }

            $user = auth()->user();

            if ($user->hasAnyRole(['Superadministrador', 'Administrador'])) {
                return $query;
            }

            if ($user->hasAnyRole(['Diretor', 'Gerente'])) {
                $teamUserIds = $user->teams()
                    ->with('users:id')
                    ->get()
                    ->pluck('users.*.id')
                    ->flatten()
                    ->unique()
                    ->toArray();

                return $query->whereIn('user_id', $teamUserIds);
            }

            return $query->where('user_id', $user->id);
        })
            ->when(!empty($search), function ($query) use ($search) {
                $query->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"]);
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(function ($item) use ($return): array {
                $name = !empty($item->cpf) ? $item->contact->name . ' - ' . $item->cpf : $item->contact->name;

                if ($return === 'contactId') {
                    return [$item->contact->id => $name];
                }

                return [$item->id => $name];
            })
            ->toArray();
    }

    // Single
    public function getIndividualOptionLabel(?string $value): string
    {
        return $this->individual->find($value)?->contact->name;
    }

    // Multiple
    public function getIndividualOptionsLabel(array $values, $return = 'individualId'): array
    {
        return $this->individual->whereIn('id', $values)
            ->get()
            ->mapWithKeys(function ($item) use ($return): array {
                $name = !empty($item->cpf) ? $item->contact->name . ' - ' . $item->cpf : $item->contact->name;

                if ($return === 'contactId') {
                    return [$item->contact->id => $name];
                }

                return [$item->id => $name];
            })
            ->toArray();
    }

    public function getQuickCreateActionByContactIndividuals(
        string $field,
        bool $multiple = false,
        $return = 'individualId'
    ): Forms\Components\Actions\Action {
        return Forms\Components\Actions\Action::make($field)
            ->label(__('Criar Pessoa'))
            ->icon('heroicon-o-plus')
            ->form([
                Forms\Components\Grid::make(['default' => 2])
                    ->schema([
                        Forms\Components\TextInput::make('contact.name')
                            ->label(__('Nome'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\Hidden::make('contact.emails.0.name')
                            ->default(null),
                        Forms\Components\TextInput::make('contact.emails.0.email')
                            ->label(__('Email'))
                            ->email()
                            ->rules([
                                function (): Closure {
                                    return function (string $attribute, string $state, Closure $fail): void {
                                        $this->validateEmail(
                                            record: null,
                                            attribute: $attribute,
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
                        Forms\Components\Hidden::make('contact.phones.0.name')
                            ->default(null),
                        Forms\Components\TextInput::make('contact.phones.0.number')
                            ->label(__('Nº do telefone'))
                            ->mask(
                                Support\RawJs::make(<<<'JS'
                                    $input.length === 14 ? '(99) 9999-9999' : '(99) 99999-9999'
                                JS)
                            )
                            ->rules([
                                function (): Closure {
                                    return function (string $attribute, string $state, Closure $fail): void {
                                        $this->validatePhone(
                                            record: null,
                                            attribute: $attribute,
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
                                function (): Closure {
                                    return function (
                                        string $attribute,
                                        string $state,
                                        Closure $fail
                                    ): void {
                                        $this->validateCpf(
                                            record: null,
                                            attribute: $attribute,
                                            state: $state,
                                            fail: $fail
                                        );
                                    };
                                },
                            ])
                            ->maxLength(255),
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
                function (array $data, string|array|null $state, callable $set) use ($field, $multiple, $return): void {
                    $individual = $this->individual->create($data);

                    $data['contact']['user_id'] = auth()->user()->id;

                    $contact = $individual->contact()
                        ->create($data['contact']);

                    $contact->roles()
                        ->sync($data['contact']['roles']);

                    if ($return == 'contactId') {
                        $id = $contact->id;
                    } else {
                        $id = $individual->id;
                    }

                    if ($multiple) {
                        array_push($state, $id);
                        $set($field, $state);
                    } else {
                        $set($field, $id);
                    }
                }
            );
    }

    public function validateEmail(?Individual $record, string $attribute, string $state, Closure $fail): void
    {
        $userId = auth()->user()->id;

        if ($record) {
            $userId = $record->contact->user_id;
        }

        $contactableType = MorphMapByClass(model: get_class($this->individual));

        $exists = $this->contact->where('email', $state)
            ->where('user_id', $userId)
            ->where('contactable_type', $contactableType)
            ->when($record, function ($query) use ($record): Builder {
                return $query->where('contactable_id', '<>', $record->id);
            })
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo email já está em uso.', ['attribute' => $attribute]));
        }
    }

    public function validatePhone(?Individual $record, string $attribute, string $state, Closure $fail): void
    {
        $userId = auth()->user()->id;

        if ($record) {
            $userId = $record->contact->user_id;
        }

        $contactableType = MorphMapByClass(model: get_class($this->individual));

        $exists = $this->contact->whereRaw("JSON_EXTRACT(phones, '$[0].number') = ?", ["$state"])
            ->where('user_id', $userId)
            ->where('contactable_type', $contactableType)
            ->when($record, function ($query) use ($record): Builder {
                return $query->where('contactable_id', '<>', $record->id);
            })
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo telefone já está em uso.', ['attribute' => $attribute]));
        }
    }

    public function validateCpf(?Individual $record, string $attribute, string $state, Closure $fail): void
    {
        $userId = auth()->user()->id;

        if ($record) {
            $userId = $record->contact->user_id;
        }

        $exists = $this->individual->where('cpf', $state)
            ->whereHas('contact', function (Builder $query) use ($userId): Builder {
                return $query->where('user_id', $userId);
            })
            ->when($record, function ($query) use ($record): Builder {
                return $query->where('id', '<>', $record->id);
            })
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo cpf já está em uso.', ['attribute' => $attribute]));
        }
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventIndividualDeleteIf($action, Individual $individual): void
    {
        //
    }
}
