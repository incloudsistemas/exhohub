<?php

namespace App\Models\Crm\Funnels;

use App\Models\Activities\FunnelUpdate;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Business\BusinessFunnelStage;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FunnelStage extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'crm_funnel_stages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'funnel_id',
        'name',
        'description',
        'business_probability',
        'order',
    ];

    public function business(): HasMany
    {
        return $this->hasMany(related: Business::class, foreignKey: 'funnel_stage_id');
    }

    public function businessFunnelStages(): HasMany
    {
        return $this->hasMany(related: BusinessFunnelStage::class, foreignKey: 'funnel_stage_id');
    }

    public function substages(): HasMany
    {
        return $this->hasMany(related: FunnelSubstage::class);
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(related: Funnel::class, foreignKey: 'funnel_id');
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
