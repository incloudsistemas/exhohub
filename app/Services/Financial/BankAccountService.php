<?php

namespace App\Services\Financial;

use App\Enums\DefaultStatusEnum;
use App\Models\Financial\BankAccount;
use App\Models\Financial\BankInstitution;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Illuminate\Support\Str;

class BankAccountService extends BaseService
{
    public function __construct(protected BankAccount $bankAccount, protected BankInstitution $bankInstitution)
    {
        //
    }

    public function getOptionsByBankAccount(): array
    {
        return $this->bankAccount->byStatuses(statuses: [1]) // 1 - Ativo
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByBankAccount(Builder $query): Builder
    {
        return $query->byStatuses(statuses: [1]); // 1 - Ativo
    }

    public function getOptionsByBankInstitution(): array
    {
        return $this->bankInstitution->byStatuses(statuses: [1]) // 1 - Ativo
            ->orderBy('id', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getQueryByBankInstitution(Builder $query): Builder
    {
        return $query->byStatuses(statuses: [1]) // 1 - Ativo
            ->orderBy('id', 'asc');
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = DefaultStatusEnum::getAssociativeArray();

        $matchingStatuses = [];
        foreach ($statuses as $index => $status) {
            if (stripos($status, $search) !== false) {
                $matchingStatuses[] = $index;
            }
        }

        if ($matchingStatuses) {
            return $query->whereIn('status', $matchingStatuses);
        }

        return $query;
    }

    public function tableSortByStatus(Builder $query, string $direction): Builder
    {
        $statuses = DefaultStatusEnum::getAssociativeArray();

        $caseParts = [];
        $bindings = [];

        foreach ($statuses as $key => $status) {
            $caseParts[] = "WHEN ? THEN ?";
            $bindings[] = $key;
            $bindings[] = $status;
        }

        $orderByCase = "CASE status " . implode(' ', $caseParts) . " END";

        return $query->orderByRaw("$orderByCase $direction", $bindings);
    }

    public function getQueryByAgenciesWhereHasBankAccounts(Builder $query): Builder
    {
        return $query->byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('bankAccounts', function (Builder $query): Builder {
                return $query->where('status', 1); // 1 - Ativo
            });
    }

    public function getQueryByBankInstitutionsWhereHasBankAccounts(Builder $query): Builder
    {
        return $query->byStatuses(statuses: [1]) // 1 - Ativo
            ->whereHas('bankAccounts', function (Builder $query): Builder {
                return $query->where('status', 1); // 1 - Ativo
            });
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventBankAccountDeleteIf($action, BankAccount $bankAccount): void
    {
        //
    }
}
