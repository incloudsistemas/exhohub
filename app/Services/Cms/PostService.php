<?php

namespace App\Services\Cms;

use App\Enums\Cms\PostStatusEnum;
use App\Models\Cms\Post;
use App\Models\Cms\PostCategory;
use App\Models\System\User;
use App\Services\BaseService;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PostService extends BaseService
{
    protected string $postTable;

    public function __construct(protected Post $post)
    {
        $this->postTable = $post->getTable();
    }

    public function validatePostSlugByPostableType(?Model $record, string $postableType, string $attribute, string $state, Closure $fail): void
    {
        $exists = $this->post->where('slug', $state)
            ->where('postable_type', $postableType)
            ->when($record, function ($query) use ($record): Builder {
                return $query->where('postable_id', '<>', $record->id);
            })
            ->first();

        if ($exists) {
            $fail(__('O valor informado para o campo slug já está em uso.', ['attribute' => $attribute]));
        }
    }

    public function getTablePostImage(Post $post): string
    {
        $image = $post->postable->getFirstMedia('image') ?? $post->postable->getFirstMedia('images');

        if ($image) {
            return '<div style="width: 45px; height: 45px; background-image: url(' . $image->getUrl('thumb') . ');
                background-size: cover; background-position: center center" class="rounded-full"></div>';
        }

        return '';
    }

    public function tableSearchByPostStatus(Builder $query, string $search): Builder
    {
        $statuses = PostStatusEnum::getAssociativeArray();

        $matchingStatuses = [];
        foreach ($statuses as $index => $status) {
            if (stripos($status, $search) !== false) {
                $matchingStatuses[] = $index;
            }
        }

        if ($matchingStatuses) {
            return $query->whereHas('cmsPost', function (Builder $query) use ($matchingStatuses): Builder {
                return $query->whereIn('status', $matchingStatuses);
            });
        }

        return $query;
    }

    public function tableSortByPostStatus(Builder $query, string $direction): Builder
    {
        $postableType = MorphMapByClass(model: $query->getModel()::class);
        $statuses = PostStatusEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($statuses as $key => $status) {
            $caseParts[] = "WHEN (SELECT status FROM {$this->postTable} WHERE {$this->postTable}.postable_type = '{$postableType}' AND {$this->postTable}.postable_id = {$postableType}.id) = ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $status;
        }

        $orderByCase = "CASE " . implode(' ', $caseParts) . " END";

        return $query->selectRaw("*, ({$orderByCase}) as display_status", $bindings)
            ->orderBy('display_status', $direction);
    }

    public function tableDefaultSort(Builder $query, string $orderDirection = 'desc', string $publishAtDirection = 'desc'): Builder
    {
        $postableType = MorphMapByClass(model: $query->getModel()::class);

        return $query->join("{$this->postTable} as pt", function ($join) use ($postableType) {
            return $join->on("{$postableType}.id", '=', "pt.postable_id")
                ->where("pt.postable_type", '=', $postableType);
        })
            ->orderBy("pt.order", $orderDirection)
            ->orderBy("pt.publish_at", $publishAtDirection);
    }

    public function getOptionsByPostCategoriesWhereHasPosts(string $postableType): array
    {
        return PostCategory::byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('cmsPosts', function (Builder $query) use ($postableType): Builder {
                return $query->where('postable_type', $postableType);
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByPostCategories(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('cmsPost', function (Builder $query) use ($data): Builder {
            return $query->whereHas('postCategories', function (Builder $query) use ($data): Builder {
                return $query->whereIn('id', $data['values']);
            });
        });
    }

    public function getOptionsByPostOwnersWhereHasPosts(string $postableType): array
    {
        return User::whereHas('cmsPosts', function (Builder $query) use ($postableType): Builder {
            return $query->where('postable_type', $postableType);
        })
            ->pluck('name', 'id')
            ->toArray();
    }

    public function tableFilterByPostOwners(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('cmsPost', function (Builder $query) use ($data): Builder {
            return $query->whereHas('owner', function (Builder $query) use ($data): Builder {
                return $query->whereIn('id', $data['values']);
            });
        });
    }

    public function tableFilterByPostStatuses(Builder $query, array $data): Builder
    {
        if (!$data['values'] || empty($data['values'])) {
            return $query;
        }

        return $query->whereHas('cmsPost', function (Builder $query) use ($data): Builder {
            return $query->whereIn('status', $data['values']);
        });
    }

    public function tableFilterByPostPublishAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['publish_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('cmsPost', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('publish_at', '>=', $date);
                }),
            )
            ->when(
                $data['publish_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('cmsPost', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('publish_at', '<=', $date);
                }),
            );
    }

    public function tableFilterByPostCreatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['created_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('cmsPost', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('created_at', '>=', $date);
                }),
            )
            ->when(
                $data['created_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('cmsPost', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('created_at', '<=', $date);
                }),
            );
    }

    public function tableFilterByPostUpdatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['updated_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('cmsPost', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('updated_at', '>=', $date);
                }),
            )
            ->when(
                $data['updated_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereHas('cmsPost', function (Builder $query) use ($date): Builder {
                    return $query->whereDate('updated_at', '<=', $date);
                }),
            );
    }
}
