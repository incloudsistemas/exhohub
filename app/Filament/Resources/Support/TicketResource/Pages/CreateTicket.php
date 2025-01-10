<?php

namespace App\Filament\Resources\Support\TicketResource\Pages;

use App\Filament\Resources\Support\TicketResource;
use App\Mail\Support\NewTicketAlert;
use App\Models\Support\Department;
use App\Models\Support\TicketCategory;
use App\Models\System\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        $data['status'] = 0; // 0 - Aguardando atendimento.

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->attachApplicantUsers();
        $this->sendEmailNotification();
    }

    protected function attachApplicantUsers(): void
    {
        $this->record->users()
            ->attach($this->data['applicant_users'], ['role' => 2]); // 2 - Solicitante
    }

    protected function sendEmailNotification(): void
    {
        $data['action'] = TicketResource::getUrl('edit', ['record' => $this->record]);

        $userMails = User::whereHas('roles', function (Builder $query): Builder {
                return $query->where('name', 'Suporte'); // 8 - Suporte
            })
            ->whereHas('departments', function (Builder $query): Builder {
                return $query->where('id', $this->data['department_id']);
            })
            ->where('status', 1) // 1 - Ativo
            ->pluck('email')
            ->toArray();

        $applicantMails = $this->record->users()
            ->where('status', 1) // 1 - Ativo
            ->pluck('email')
            ->toArray();

        $mails = array_unique(array_merge($userMails, $applicantMails));

        if (!empty($mails)) {
            $data['id'] = $this->record->id;

            $data['title'] = $this->record->title;

            $data['user'] = auth()->user()->name;

            $data['department'] = Department::where('id', $this->data['department_id'])
                ->pluck('name')
                ->first();

            $categories = is_array($this->data['categories']) ? $this->data['categories'] : [];
            $data['categories'] = TicketCategory::whereIn('id', $categories)
                ->pluck('name')
                ->toArray();

            Mail::to($mails)
                ->send(new NewTicketAlert($data));
        }
    }
}
