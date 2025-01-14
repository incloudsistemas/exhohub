<?php

namespace App\Models\Financial;

use App\Casts\DateCast;
use App\Casts\FloatCast;
use App\Enums\Financial\TransactionPaymentMethodEnum;
use App\Enums\Financial\TransactionRepeatFrequencyEnum;
use App\Enums\Financial\TransactionRepeatPaymentEnum;
use App\Models\Crm\Contacts\Contact;
use App\Models\System\User;
use App\Observers\Financial\TransactionObserver;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Transaction extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, ClearsResponseCache;

    protected $table = 'financial_transactions';

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'contact_id',
        'role',
        'name',
        'payment_method',
        'repeat_payment',
        'repeat_frequency',
        'repeat_occurrence',
        'idx_transaction',
        'price',
        'interest',
        'fine',
        'discount',
        'taxes',
        'final_price',
        'description',
        'due_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_method'   => TransactionPaymentMethodEnum::class,
            'repeat_payment'   => TransactionRepeatPaymentEnum::class,
            'repeat_frequency' => TransactionRepeatFrequencyEnum::class,
            'price'            => FloatCast::class,
            'interest'         => FloatCast::class,
            'fine'             => FloatCast::class,
            'discount'         => FloatCast::class,
            'taxes'            => FloatCast::class,
            'final_price'      => FloatCast::class,
            'due_at'           => DateCast::class,
            'paid_at'          => DateCast::class,
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Category::class,
            table: 'financial_category_financial_transaction',
            foreignPivotKey: 'transaction_id',
            relatedPivotKey: 'category_id'
        );
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(related: BankAccount::class, foreignKey: 'bank_account_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
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
        self::observe(TransactionObserver::class);
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
