<?php

namespace App\Models\Cms;

use App\Casts\DateTimeCast;
use App\Enums\Cms\PostStatusEnum;
use App\Models\System\User;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    protected $table = 'cms_posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'postable_type',
        'postable_id',
        'user_id',
        'title',
        'slug',
        'subtitle',
        'excerpt',
        'body',
        'url',
        'embed_video',
        'tags',
        'order',
        'featured',
        'comment',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'publish_at',
        'expiration_at',
        'status',
        'custom',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags'          => 'array',
        'featured'      => 'boolean',
        'comment'       => 'boolean',
        'meta_keywords' => 'array',
        'publish_at'    => DateTimeCast::class,
        'expiration_at' => DateTimeCast::class,
        'status'        => PostStatusEnum::class,
        'custom'        => 'array',
    ];

    public function postCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            related: PostCategory::class,
            table: 'cms_post_cms_post_category',
            foreignPivotKey: 'post_id',
            relatedPivotKey: 'category_id'
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function postable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 345, 230)
            ->nonQueued();
    }

    /**
     * EVENT LISTENERS.
     *
     */

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
