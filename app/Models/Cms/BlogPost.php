<?php

namespace App\Models\Cms;

use App\Enums\Cms\BlogPostRoleEnum;
use App\Observers\Cms\BlogPostObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\Cms\Postable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class BlogPost extends Model implements HasMedia
{
    use Postable, ClearsResponseCache;

    protected $table = 'cms_blog_posts';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
        'embed_videos',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role'         => BlogPostRoleEnum::class,
        'embed_videos' => 'array',
    ];

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(BlogPostObserver::class);
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

    public function getDisplayRoleAttribute(): string
    {
        return $this->role->getLabel();
    }

    public function getDisplayRoleSlugAttribute(): string
    {
        return $this->role->getSlug();
    }
}
