<?php

namespace App\Services\Cms;

use App\Enums\Cms\PostSliderRoleEnum;
use App\Enums\Cms\PostStatusEnum;
use App\Models\Cms\PostSlider;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PostSliderService extends BaseService
{
    public function __construct(protected PostSlider $postSlider)
    {
        //
    }

    public function tableSearchByRole(Builder $query, string $search): Builder
    {
        $roles = PostSliderRoleEnum::getAssociativeArray();

        $matchingRoles = [];
        foreach ($roles as $index => $role) {
            if (stripos($role, $search) !== false) {
                $matchingRoles[] = $index;
            }
        }

        if ($matchingRoles) {
            return $query->whereIn('role', $matchingRoles);
        }

        return $query;
    }

    public function tableSortByRole(Builder $query, string $direction): Builder
    {
        $roles = PostSliderRoleEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($roles as $key => $role) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $role;
        }

        $orderByCase = "CASE role " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = PostStatusEnum::getAssociativeArray();

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
        $statuses = PostStatusEnum::getAssociativeArray();

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

    public function tableFilterByPublishAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['publish_from'],
                fn(Builder $query, $date): Builder =>
                $query->whereDate('publish_at', '>=', $date),
            )
            ->when(
                $data['publish_until'],
                fn(Builder $query, $date): Builder =>
                $query->whereDate('publish_at', '<=', $date),
            );
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventPostSliderDeleteIf($action, PostSlider $postSlider): void
    {
        //
    }
}
