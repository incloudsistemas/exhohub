<?php

namespace App\Models\Support;

use App\Enums\DefaultStatusEnum;
use App\Models\System\User;
use App\Observers\Support\DepartmentObserver;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class Department extends Model
{
    use HasFactory, SoftDeletes, ClearsResponseCache;

    protected $table = 'support_departments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DefaultStatusEnum::class,
        ];
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Ticket::class,
            table: 'support_department_ticket',
            foreignPivotKey: 'department_id',
            relatedPivotKey: 'ticket_id'
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Category::class,
            table: 'support_department_ticket_category',
            foreignPivotKey: 'department_id',
            relatedPivotKey: 'category_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'support_department_user',
            foreignPivotKey: 'department_id',
            relatedPivotKey: 'user_id'
        );
    }

    /**
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(DepartmentObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */
}
