<?php

namespace App\Filament\Team\Resources\Banks\Schemas;

use App\Models\Bank;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;

class BankForm
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

                TextInput::make('logo')
                ->inlineLabel(),

                Select::make('currency_id')
                    ->inlineLabel()
                    ->label('Currency')
                    ->relationship('currency', 'code'),

                    // Radio::make('type')
                    // ->label('Account Type')
                    // ->inlineLabel()
                    // ->options(['cash' => 'Cash / Bank Account', 'receivable' => 'Receivable (others owe me)', 'payable' => 'Payable (I Owe others)'])
                    // ->default('cash')
                    // ->required(),

                TextInput::make('opening_balance')
                    ->inlineLabel()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->numeric()
                    ->default(0.0),



                DatePicker::make('opening_balance_date')
                ->inlineLabel(),
            ]);
    }
}
