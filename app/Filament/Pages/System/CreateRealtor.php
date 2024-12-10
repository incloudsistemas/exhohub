<?php

namespace App\Filament\Pages\System;

use App\Filament\Resources\System\UserResource;
use App\Mail\System\NewUserAdmApprovalAlert;
use App\Models\System\Team;
use App\Models\System\User;
use App\Services\System\AgencyService;
use App\Services\System\TeamService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class CreateRealtor extends BaseRegister
{
    protected $user;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getAgencyFormComponent(),
                        $this->getTeamFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getAgencyFormComponent(): Forms\Components\Select
    {
        return Forms\Components\Select::make('agency_id')
            ->label(__('Agência'))
            ->options(
                fn (AgencyService $service): array =>
                $service->getOptionsByActiveAgencies(),
            )
            ->selectablePlaceholder(false)
            ->required()
            ->native(false)
            ->live(onBlur: true)
            ->afterStateUpdated(
                fn (callable $set): ?string =>
                $set('team_id', '')
            );
    }

    protected function getTeamFormComponent(): Forms\Components\Select
    {
        return Forms\Components\Select::make('team_id')
            ->label(__('Equipe'))
            ->options(
                fn (TeamService $service, callable $get): array =>
                $service->getOptionsByAgency(agency: $get('agency_id')),
            )
            ->selectablePlaceholder(false)
            ->required()
            ->native(false);
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $this->user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $this->user = $this->handleRegistration($data);

            $this->form->model($this->user)->saveRelationships();

            $this->callHook('afterRegister');

            return $this->user;
        });

        event(new Registered($this->user));

        $this->sendEmailVerificationNotification($this->user);

        Filament::auth()->login($this->user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['status'] = 2; // 2 - Pendente

        return $data;
    }

    protected function afterRegister(): void
    {
        $this->attachRole();
        $this->attachTeam();
        $this->sendEmailNotificationToAdmins();
    }

    protected function attachRole(): void
    {
        $this->user->roles()
            ->attach(6); // '6' - Corretor
    }

    protected function attachTeam(): void
    {
        $this->user->teams()
            ->attach($this->data['team_id'], ['role' => 3]); // '3' - Corretor

    }

    protected function sendEmailNotificationToAdmins(): void
    {
        $mailTo = User::bySuperAndAdmin()
            ->pluck('email')
            ->toArray();

        $team = Team::find($this->data['team_id']);
        $this->data['team_name'] = $team->name;
        $this->data['agency_name'] = $team->agency->name;

        $action = UserResource::getUrl('edit', ['record' => $this->user]);
        $this->data['action'] = $action;

        Mail::to($mailTo)
            ->send(new NewUserAdmApprovalAlert($this->data));
    }
}
