<?php

namespace App\Models\Activities;

use App\Models\Crm\Business\Business;
use App\Models\Crm\Contacts\Contact;
use App\Models\System\User;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory, ClearsResponseCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activityable_type',
        'activityable_id',
        'user_id',
        'description',
        'custom'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'custom' => 'array',
        ];
    }

    public function business(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Business::class,
            table: 'activity_crm_business',
            foreignPivotKey: 'activity_id',
            relatedPivotKey: 'business_id'
        );
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Contact::class,
            table: 'activity_crm_contact',
            foreignPivotKey: 'activity_id',
            relatedPivotKey: 'contact_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'activity_user',
            foreignPivotKey: 'activity_id',
            relatedPivotKey: 'user_id'
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * EVENT LISTENERS.
     *
     */

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
