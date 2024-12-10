<?php

namespace App\Models\Cms;

use App\Observers\Cms\PageObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\Cms\Postable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class Page extends Model implements HasMedia
{
    use Postable, ClearsResponseCache;

    protected $table = 'cms_pages';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_id',
        'cta',
        'embed_videos',
        'settings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cta'          => 'array',
        'embed_videos' => 'array',
        'settings'     => 'array',
    ];

    public function mainPage(): BelongsTo
    {
        return $this->belongsTo(related: Self::class, foreignKey: 'page_id');
    }

    public function subpages(): HasMany
    {
        return $this->hasMany(related: Self::class, foreignKey: 'page_id');
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(PageObserver::class);
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
}
