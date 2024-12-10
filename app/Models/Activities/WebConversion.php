<?php

namespace App\Models\Activities;

use App\Traits\Activities\Activityable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WebConversion extends Model
{
    use HasFactory, Activityable;

    protected $table = 'activity_web_conversions';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'conversionnable_type',
        'conversionnable_id',
        'data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function conversionnable(): MorphTo
    {
        return $this->morphTo();
    }
}
