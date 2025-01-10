<?php

namespace App\Filament\Resources\Support\TicketResource\Pages;

use App\Filament\Resources\Support\TicketResource;
use App\Mail\Support\TicketAnswerAlert;
use App\Mail\Support\TicketClosedAlert;
use App\Models\Support\Department;
use App\Models\Support\Ticket;
use App\Models\Support\TicketCategory;
use App\Services\Support\TicketService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support;
use Illuminate\Support\Facades\Mail;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected ?int $currentStatus = null;

    protected int $hasComments = 0;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(TicketService $service, Actions\DeleteAction $action, Ticket $record) =>
                    $service->preventTicketDeleteIf(action: $action, ticket: $record)
                ),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('Salvar Comentário'))
                ->submit('save')
                ->keyBindings(['mod+s']),
            Actions\Action::make('cancel')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
                ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Support\Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
                ->color('gray'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $attachments = $this->record->getMedia('attachments');

        $data['ticket_attachments'] = [];
        foreach ($attachments as $key => $attachment) {
            $data['ticket_attachments'][] = [
                'name'      => $attachment->name,
                'file_name' => $attachment->file_name,
                'mime'      => $attachment->mime_type,
                'size'      => AbbrNumberFormat($attachment->size),
                'download'  => url('storage/' . $attachment->id . '/' . $attachment->file_name)
            ];
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->currentStatus = (int) $this->record->status->value;

        $this->hasComments = $this->record->comments()
            ->count();
    }

    protected function afterSave(): void
    {
        $this->updateStatus();
        $this->addResponsible();
        $this->sendEmailNotification();
    }

    protected function updateStatus(): void
    {
        // Status 1 - Ativo, 2 - Finalizado
        if ($this->hasComments === 0 && !in_array($this->currentStatus, [1, 2])) {
            $newComments = $this->data['comments'] ?? [];

            if (!empty($newComments)) {
                $this->record->update([
                    'status'    => 1, // 1 - Ativo
                    'opened_at' => now(),
                ]);
            }
        }
    }

    protected function addResponsible(): void
    {
        if ($this->hasComments === 0 ) {
            $newComments = $this->data['comments'] ?? [];

            if (!empty($newComments)) {
                $firstComment = reset($newComments) ?: null;

                if ($firstComment && isset($firstComment['user_id'])) {
                    $this->record->users()
                        ->attach($firstComment['user_id'], ['role' => 1]); // 1 - Responsável
                }
            }
        }
    }

    protected function sendEmailNotification(): void
    {
        $data['action'] = TicketResource::getUrl('edit', ['record' => $this->record]);

        $authMail = auth()->user()->email;

        $mainApplicantMail = $this->record->owner->email;

        $userMails = $this->record->users()
            ->where('status', 1) // 1 - Ativo
            ->pluck('email')
            ->toArray();

        $mails = array_unique(array_merge([$authMail], [$mainApplicantMail], $userMails));

        $data['id'] = $this->data['id'];

        $data['title'] = $this->data['title'];

        $data['user'] = auth()->user()->name;

        $data['department'] = Department::where('id', $this->data['department_id'])
            ->pluck('name')
            ->first();

        $categories = is_array($this->data['categories']) ? $this->data['categories'] : [];
        $data['categories'] = TicketCategory::whereIn('id', $categories)
            ->pluck('name')
            ->toArray();

        $newComments = count($this->data['comments'] ?? []);

        if ($newComments !== $this->hasComments) {
            $mails = array_diff($mails, [$authMail]);

            if (!empty($mails)) {
                Mail::to($mails)
                    ->send(new TicketAnswerAlert($data));
            }
        }

        $status = (int) $this->record->status->value;

        if ($this->currentStatus !== $status && $status === 2) {
            $this->record->update([
                'closed_at' => now(),
            ]);

            if (!empty($mails)) {
                $data['created_at'] = ConvertEnToPtBrDateTime(date: $this->record->created_at);
                $data['opened_at'] = ConvertEnToPtBrDateTime(date: $this->record->opened_at);
                $data['closed_at'] = ConvertEnToPtBrDateTime(date: $this->record->closed_at);

                Mail::to($mails)
                    ->send(new TicketClosedAlert($data));
            }
        }
    }
}
