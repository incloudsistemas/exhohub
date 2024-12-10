<?php

namespace App\Models\Cms;

use App\Enums\DefaultStatusEnum;
use App\Observers\Cms\PostCategoryObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostCategory extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'cms_post_categories';

    protected $fillable = [
        'name',
        'slug',
        'order',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => DefaultStatusEnum::class,
    ];

    public function cmsPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Post::class,
            table: 'cms_post_cms_post_category',
            foreignPivotKey: 'category_id',
            relatedPivotKey: 'post_id'
        );
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(PostCategoryObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */
}
