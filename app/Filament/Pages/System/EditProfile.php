<?php

namespace App\Filament\Pages\System;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected static ?string $slug = 'my-profile';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Nome'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                $this->getPasswordFormComponent()
                    ->helperText(__('Preencha apenas se desejar alterar a senha. Min. de 8 dígitos.'))
                    ->minLength(8)
                    ->maxLength(255),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     $data['email_confirmation'] = $data['email'];
    //     return $data;
    // }
}
