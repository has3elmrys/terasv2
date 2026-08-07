<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->inlineLabel()
                    ->required(),
                TextInput::make('email')
                    ->inlineLabel()
                    ->label('Email address')
                    ->email()
                    ->required(),
                // Toggle::make('has_email_authentication')
                //     ->inlineLabel()
                //     ->required(),
                // Textarea::make('app_authentication_secret')
                //     ->inlineLabel()
                //     ->columnSpanFull(),
                TextInput::make('password')
                    ->inlineLabel()
                    ->revealable()
                    ->password()
                    ->required(),

                TextInput::make('phone_number')
                    ->inlineLabel()
                    ->tel(),
                // DateTimePicker::make('email_verified_at'),

            ]);
    }
}
