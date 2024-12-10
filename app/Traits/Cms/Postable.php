<?php

namespace App\Traits\Cms;

use App\Models\Cms\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait Postable
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public function cmsPost(): MorphOne
    {
        return $this->morphOne(related: Post::class, name: 'postable');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
    }

    /**
     * SCOPES.
     *
     */

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */

    public function getFeaturedImageAttribute(): ?Media
    {
        $featuredImage = $this->getFirstMedia('images');

        return $featuredImage ?? null;
    }

    public function getGalleryImagesAttribute(): ?Collection
    {
        $galleryImages = $this->getMedia('images');

        return $galleryImages ?? null;
    }

    public function getFeaturedVideoAttribute(): ?Media
    {
        $featuredVideo = $this->getFirstMedia('videos');

        return $featuredVideo ?? null;
    }

    public function getGalleryVideosAttribute(): ?Collection
    {
        $galleryVideos = $this->getMedia('videos');

        return $galleryVideos ?? null;
    }

    /**
     * WEBSITE EXCLUSIVE.
     *
     */

    protected function baseWebQuery(
        array $statuses = [1],
        bool $featured = false,
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        $postableTable = $this->getTable();
        $morphClass = MorphMapByClass(model: get_class($this));

        return $this->newQuery()
            ->with([
                'cmsPost',
                'cmsPost.owner:id,name,email',
                'media'
            ])
            ->join('cms_posts', function ($join) use ($postableTable, $morphClass) {
                return $join->on("{$postableTable}.id", '=', 'cms_posts.postable_id')
                    ->where('cms_posts.postable_type', '=', $morphClass);
            })
            ->select("{$postableTable}.*")
            ->whereIn('cms_posts.status', $statuses)
            ->where('cms_posts.publish_at', '<=', now())
            ->where(function ($query): Builder {
                return $query->where('cms_posts.expiration_at', '>', now())
                    ->orWhereNull('cms_posts.expiration_at');
            })
            ->orderBy($orderBy, $direction)
            ->orderBy('cms_posts.publish_at', $publishAtDirection);

        // Query debug
        // dd($query->toSql(), $query->getBindings());
    }

    public function getWeb(
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            featured: false,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        );
    }

    public function getWebFeatured(
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->baseWebQuery(
            statuses: $statuses,
            featured: true,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        );
    }

    public function getWebByRoles(
        array $roles,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereIn('role', $roles);
    }

    public function getWebFeaturedByRoles(
        array $roles,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWebFeatured(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereIn('role', $roles);
    }

    public function findWebBySlug(
        string $slug,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('slug', $slug);
    }

    public function searchWeb(
        string $keyword,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->where('title', 'like', '%' . $keyword . '%');
    }

    public function searchWebByRoles(
        string $keyword,
        array $roles,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->searchWeb(
            keyword: $keyword,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereIn('role', $roles);
    }

    public function getWebByCategory(
        string $categorySlug,
        array $statuses = [1,],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereHas('cmsPost.postCategories', function (Builder $query) use ($categorySlug): Builder {
                return $query->where('slug', $categorySlug);
            });
    }

    public function getWebByCategoryAndRoles(
        string $categorySlug,
        array $roles,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWebByCategory(
            categorySlug: $categorySlug,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereIn('role', $roles);
    }

    public function getWebByRelatedCategories(
        array $categoryIds,
        array $statuses = [1],
        ?int $idToAvoid = null,
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        $query = $this->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereHas('cmsPost.postCategories', function (Builder $query) use ($categoryIds): Builder {
                return $query->whereIn('id', $categoryIds);
            });

        if ($idToAvoid) {
            $query->where('id', '<>', $idToAvoid);
        }

        return $query;
    }

    public function getWebByRelatedCategoriesAndRoles(
        array $categoryIds,
        array $roles,
        array $statuses = [1],
        string $orderBy = 'cms_posts.order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        return $this->getWebByRelatedCategories(
            categoryIds: $categoryIds,
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereIn('role', $roles);
    }

    public function getWebSliders(
        array $statuses = [1],
        string $orderBy = 'order',
        string $direction = 'desc'
    ): Builder {
        return $this->sliders()
            ->whereIn('status', $statuses)
            ->where('publish_at', '<=', now())
            ->where(function (Builder $query): Builder {
                return $query->where('expiration_at', '>', now())
                    ->orWhereNull('expiration_at');
            })
            ->orderBy($orderBy, $direction)
            ->orderBy('publish_at', 'desc');
    }
}
