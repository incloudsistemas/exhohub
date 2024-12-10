<?php

namespace App\Models\Crm\Contacts;

use App\Casts\DateCast;
use App\Enums\ProfileInfos\GenderEnum;
use App\Observers\Crm\Contacts\IndividualObserver;
use App\Traits\ClearsResponseCache;
use App\Traits\Crm\Contacts\Contactable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;

class Individual extends Model implements HasMedia
{
    use Contactable, ClearsResponseCache;

    protected $table = 'crm_contact_individuals';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cpf',
        'rg',
        'gender',
        'birth_date',
        'occupation',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender'     => GenderEnum::class,
            'birth_date' => DateCast::class
        ];
    }

    public function legalEntities(): BelongsToMany
    {
        return $this->belongsToMany(
            related: LegalEntity::class,
            table: 'crm_contact_individual_crm_contact_legal_entity',
            foreignPivotKey: 'individual_id',
            relatedPivotKey: 'legal_entity_id'
        );
    }

    /**
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(IndividualObserver::class);
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

    public function getDisplayBirthDateAttribute(): ?string
    {
        return isset($this->birth_date)
            ? ConvertEnToPtBrDate(date: $this->birth_date)
            : null;
    }
}
