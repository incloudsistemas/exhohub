<?php

namespace App\Services\Cms;

use App\Enums\DefaultStatusEnum;
use App\Models\Cms\PostCategory;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Illuminate\Support\Str;

class PostCategoryService extends BaseService
{
    public function __construct(protected PostCategory $postCategory)
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

    public function getOptionsByActivePostCategories(): array
    {
        return $this->postCategory->byStatuses(statuses: [1]) // 1 - Ativo
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByActivePostCategories(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function getQuickCreateActionByPostCategories(string $field, bool $multiple = false): Forms\Components\Actions\Action
    {
        return Forms\Components\Actions\Action::make($field)
            ->label(__('Criar Categoria'))
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
                                fn(callable $set, ?string $state): ?string =>
                                $set('slug', Str::slug($state))
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(PostCategory::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                    ]),
            ])
            ->action(
                function (array $data, string|array|null $state, callable $set) use ($field, $multiple): void {
                    $category = PostCategory::create($data);

                    if ($multiple) {
                        array_push($state, $category->id);
                        $set($field, $state);
                    } else {
                        $set($field, $category->id);
                    }
                }
            );
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventPostCategoryDeleteIf($action, PostCategory $postCategory): void
    {
        if ($postCategory->cmsPosts->count() > 0) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de categoria'))
                ->warning()
                ->body(__('Esta categoria possui postagens associadas. Para excluir, você deve primeiro desvincular todas as postagens que estão associados a ela.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
