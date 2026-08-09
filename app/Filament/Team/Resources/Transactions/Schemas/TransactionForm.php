<?php

namespace App\Filament\Team\Resources\Transactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bank_id')
                    ->relationship('bank', 'name', fn ($query) => $query->where('type', 'cash'))
                    ->required(),

                Select::make('category_id')
                    ->inlineLabel()
                    ->relationship('category', 'name'),

                Select::make('currency_id')
                    ->inlineLabel()
                    ->relationship('currency', 'code')
                    ->required(),

                Select::make('type')
                    ->inlineLabel()
                    ->options([
                    'income' => 'Income',
                    'expense' => 'Expense',
                    'transfer_out' => 'Transfer out',
                    'transfer_in' => 'Transfer in',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->inlineLabel()
                    ->required()
                    ->numeric(),

                TextInput::make('rate_to_base')
                    ->inlineLabel()
                    ->required()
                    ->numeric(),

                TextInput::make('base_amount')
                    ->inlineLabel()
                    ->required()
                    ->numeric(),

                DatePicker::make('date')
                    ->label('Transaction date')
                    ->inlineLabel()
                    ->required(),

                Textarea::make('note')
                    ->inlineLabel()
                    ->columnSpanFull(),

                FileUpload::make('image_path')
                    ->inlineLabel()
                    ->image(),


            ]);
    }
}
