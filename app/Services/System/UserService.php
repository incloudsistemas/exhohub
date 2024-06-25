<?php

namespace App\Services\System;

use App\Enums\ProfileInfos\UserStatusEnum;
use App\Models\System\User;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UserService extends BaseService
{
    public function __construct(protected User $user)
    {
        //
    }

    public function tableSearchByNameAndCpf(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): Builder {
            return $query->where('cpf', 'like', "%{$search}%")
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"])
                ->orWhere('name', 'like', "%{$search}%");
        });
    }

    public function tableSearchByPhone(Builder $query, string $search): Builder
    {
        return $query->whereRaw("JSON_EXTRACT(phones, '$[0].number') LIKE ?", ["%$search%"]);
    }

    public function tableSearchByStatus(Builder $query, string $search): Builder
    {
        $statuses = UserStatusEnum::getAssociativeArray();

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
        $statuses = UserStatusEnum::getAssociativeArray();

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

    public function getActiveUserOptionsBySearch(?string $search): array
    {
        return $this->user->byStatuses(statuses: [1,])
            ->where(function (Builder $query) use ($search): Builder {
                return $query->where('cpf', 'like', "%{$search}%")
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') LIKE ?", ["%{$search}%"])
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(function ($item): array {
                $name = !empty($item->cpf) ? $item->name . ' - ' . $item->cpf : $item->name;
                return [$item->id => $name];
            })
            ->toArray();
    }

    // Single
    public function getUserOptionLabel(?int $value): string
    {
        return $this->user->find($value)?->name;
    }

    // Multiple
    public function getUserOptionsLabel(array $values): array
    {
        return $this->user->whereIn('id', $values)
            ->get()
            ->mapWithKeys(function ($item): array {
                $name = !empty($item->cpf) ? $item->name . ' - ' . $item->cpf : $item->name;
                return [$item->id => $name];
            })
            ->toArray();
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventUserDeleteIf($action, User $user): void
    {
        if (auth()->user()->id === $user->id) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de usuário'))
                ->warning()
                ->body(__('Você não pode excluir seu próprio usuário do sistema por questões de segurança.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
