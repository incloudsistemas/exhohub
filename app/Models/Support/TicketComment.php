<?php

namespace App\Models\Support;

use App\Enums\DefaultStatusEnum;
use App\Models\System\User;
use App\Observers\Support\TicketCommentObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TicketComment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'title',
        'body',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TicketCommentObserver::class);
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

    public function getDisplayAttachmentsAttribute(): ?array
    {
        $attachments = $this->getMedia('attachments');

        $data['attachments'] = [];
        foreach ($attachments as $key => $attachment) {
            $data['attachments'][] = [
                'name'      => $attachment->name,
                'file_name' => $attachment->file_name,
                'mime'      => $attachment->mime_type,
                'size'      => AbbrNumberFormat($attachment->size),
                'download'  => url('storage/' . $attachment->id . '/' . $attachment->file_name)
            ];
        }

        return $data['attachments'];
    }
}
