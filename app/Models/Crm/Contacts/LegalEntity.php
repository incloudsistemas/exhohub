<?php

namespace App\Models\Crm\Contacts;

use App\Observers\Crm\Contacts\LegalEntityObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\Crm\Contacts\Contactable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;

class LegalEntity extends Model implements HasMedia
{
    use Contactable, ClearsResponseCache;

    protected $table = 'crm_contact_legal_entities';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trade_name',
        'cnpj',
        'municipal_registration',
        'state_registration',
        'url',
        'sector',
        'num_employees',
        'anual_income',
    ];

    public function individuals(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Individual::class,
            table: 'crm_contact_individual_crm_contact_legal_entity',
            foreignPivotKey: 'legal_entity_id',
            relatedPivotKey: 'individual_id'
        );
    }

    public function contact(): MorphOne
    {
        return $this->morphOne(related: Contact::class, name: 'contactable');
    }

    /**
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(LegalEntityObserver::class);
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
