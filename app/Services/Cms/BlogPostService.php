<?php

namespace App\Services\Cms;

use App\Enums\Cms\BlogPostRoleEnum;
use App\Models\Cms\BlogPost;
use App\Models\Cms\Post;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class BlogPostService extends BaseService
{
    public function __construct(protected Post $post, protected BlogPost $blogPost)
    {
        //
    }

    public function tableSearchByRole(Builder $query, string $search): Builder
    {
        $roles = BlogPostRoleEnum::getAssociativeArray();

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
        $roles = BlogPostRoleEnum::getAssociativeArray();

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

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventBlogPostDeleteIf($action, BlogPost $blogPost): void
    {
        //
    }
}
