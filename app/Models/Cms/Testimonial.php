<?php

namespace App\Models\Cms;

use App\Enums\Cms\TestimonialRoleEnum;
use App\Observers\Cms\TestimonialObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\Cms\Postable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Testimonial extends Model implements HasMedia
{
    use Postable, ClearsResponseCache;

    protected $table = 'cms_testimonials';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
        'customer_name',
        'occupation',
        'company',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => TestimonialRoleEnum::class,
    ];

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TestimonialObserver::class);
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
