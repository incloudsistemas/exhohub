<?php

namespace App\Services;

use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseService
{
    protected function getErrorException(\Throwable $e): array
    {
        $message = match (get_class($e)) {
            ValidatorException::class => $e->getMessageBag(),
            default => $e->getMessage(),
        };

        return [
            'success' => false,
            'message' => $message,
        ];
    }

    public function tableFilterByCreatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['created_from'],
                fn (Builder $query, $date): Builder =>
                $query->whereDate('created_at', '>=', $date),
            )
            ->when(
                $data['created_until'],
                fn (Builder $query, $date): Builder =>
                $query->whereDate('created_at', '<=', $date),
            );
    }

    public function tableFilterByUpdatedAt(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['updated_from'],
                fn (Builder $query, $date): Builder =>
                $query->whereDate('updated_at', '>=', $date),
            )
            ->when(
                $data['updated_until'],
                fn (Builder $query, $date): Builder =>
                $query->whereDate('updated_at', '<=', $date),
            );
    }
}
