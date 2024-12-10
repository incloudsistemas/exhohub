<?php

namespace App\Services\Crm;

use App\Enums\DefaultStatusEnum;
use App\Models\Crm\Source;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Illuminate\Support\Str;

class SourceService extends BaseService
{
    public function __construct(protected Source $source)
    {
        //
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = DefaultStatusEnum::getAssociativeArray();

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
        $statuses = DefaultStatusEnum::getAssociativeArray();

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

    public function getOptionsByActiveSources(): array
    {
        return $this->source->byStatuses(statuses: [1]) // 1 - Ativo
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getActiveSourceOptionsBySearch(?string $search): array
    {
        return $this->source->byStatuses(statuses: [1]) // 1 - Ativo
            ->where('name', 'like', "%{$search}%")
            ->pluck('name', 'id')
            ->toArray();
    }

    // Single
    public function getSourceOptionLabel(?int $value): string
    {
        return $this->source->find($value)?->name;
    }

    // Multiple
    public function getSourceOptionsLabel(array $values): array
    {
        return $this->source->whereIn('id', $values)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQuickCreateActionBySources(
        string $field,
        bool $multiple = false
    ): Forms\Components\Actions\Action {
        return Forms\Components\Actions\Action::make($field)
            ->label(__('Criar Origem do Contato'))
            ->icon('heroicon-o-plus')
            ->form([
                Forms\Components\Grid::make(['default' => 2])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Nome'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->live(debounce: 1000)
                            ->afterStateUpdated(
                                fn (callable $set, ?string $state): ?string =>
                                $set('slug', Str::slug($state))
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(Source::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                    ]),
            ])
            ->action(
                function (array $data, string|array|null $state, callable $set) use ($field, $multiple): void {
                    $source = $this->source->create($data);

                    if ($source) {
                        if ($multiple) {
                            array_push($state, $source->id);
                            $set($field, $state);
                        } else {
                            $set($field, $source->id);
                        }
                    }
                }
            );
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventSourceDeleteIf($action, Source $source): void
    {
        if ($source->contacts->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de origem'))
                ->warning()
                ->body(__('Esta origem possui contatos associados. Para excluir, você deve primeiro desvincular todos os contatos que estão associados a ele.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
