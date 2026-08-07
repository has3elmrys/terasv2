<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput as ComponentsTextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class CustomLogin extends Login
{
    protected function getEmailFormComponent(): Component
    {
        // Accept either email or username
        return ComponentsTextInput::make('login')
            ->label('Email or Username')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['login'];

        // Check if input is an email
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return [
            $field => $login,
            'password' => $data['password'],
        ];
    }

    protected function getPasswordFormComponent(): Component
{
    return parent::getPasswordFormComponent()
        // ->revealable(false) // Example: disable password reveal
        ->extraInputAttributes(['class' => 'custom-password-field']);
}

protected function getRememberFormComponent(): Component
{
    return parent::getRememberFormComponent()
        ->label('Keep me logged in'); // Custom label
}

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
              'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
