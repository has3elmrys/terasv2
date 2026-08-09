<?php

namespace App\Filament\Team\Resources\Lenders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('team_id')
                //     ->required()
                //     ->numeric(),
                // TextInput::make('user_id')
                //     ->numeric(),
                TextInput::make('name')
                    ->inlineLabel()
                    ->required(),
                TextInput::make('phone')
                    ->inlineLabel()
                    ->tel(),
                TextInput::make('email')
                    ->inlineLabel()
                    ->label('Email address')
                    ->email(),
                Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }
}
