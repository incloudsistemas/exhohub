<?php

namespace App\Filament\Resources\Financial\BankAccountResource\Pages;

use App\Filament\Resources\Financial\BankAccountResource;
use App\Models\Financial\BankAccount;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBankAccounts extends ManageRecords
{
    protected static string $resource = BankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->before(
                    function (array $data) {
                        if (isset($data['is_main']) && $data['is_main']) {
                            BankAccount::where('is_main', 1)
                                ->update(['is_main' => 0]);
                        }
                    }
                ),
        ];
    }
}
