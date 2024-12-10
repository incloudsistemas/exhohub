<?php

namespace App\Models\Cms;

use App\Observers\Cms\PartnerObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\Cms\Postable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Partner extends Model implements HasMedia
{
    use Postable, ClearsResponseCache;

    protected $table = 'cms_partners';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_name',
    ];

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(PartnerObserver::class);
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
