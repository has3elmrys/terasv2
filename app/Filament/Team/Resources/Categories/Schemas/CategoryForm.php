<?php

namespace App\Filament\Team\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->inlineLabel()
                    ->required(),
                Select::make('type')
                    ->inlineLabel()
                    ->options(['income' => 'Income', 'expense' => 'Expense'])
                    ->live()
                    ->required(),
                TextInput::make('budget_amount_limit')
                    ->label('Monthly Budget Limit')
                    ->inlineLabel()
                    ->numeric()
                    ->helperText('Optional. Leave blank for no limit.')
                    ->visible(fn (Get $get) => $get('type') === 'expense'),
                // ToggleButtons::make('icon')
                //     ->label('Icon')
                //     ->options([
                //         'heroicon-o-banknotes' => 'Banknotes',
                //         'heroicon-o-cake' => 'Cake',
                //         'heroicon-o-truck' => 'Truck',
                //         'heroicon-o-film' => 'Film',
                //         'heroicon-o-shopping-bag' => 'Shopping Bag',
                //         'heroicon-o-heart' => 'Heart',
                //         'heroicon-o-academic-cap' => 'Academic Cap',
                //         'heroicon-o-bolt' => 'Bolt',
                //         'heroicon-o-paper-airplane' => 'Paper Airplane',
                //         'heroicon-o-arrow-path' => 'Arrow Path',
                //     ])
                //     ->icons([
                //         'heroicon-o-banknotes' => 'heroicon-o-banknotes',
                //         'heroicon-o-cake' => 'heroicon-o-cake',
                //         'heroicon-o-truck' => 'heroicon-o-truck',
                //         'heroicon-o-film' => 'heroicon-o-film',
                //         'heroicon-o-shopping-bag' => 'heroicon-o-shopping-bag',
                //         'heroicon-o-heart' => 'heroicon-o-heart',
                //         'heroicon-o-academic-cap' => 'heroicon-o-academic-cap',
                //         'heroicon-o-bolt' => 'heroicon-o-bolt',
                //         'heroicon-o-paper-airplane' => 'heroicon-o-paper-airplane',
                //         'heroicon-o-arrow-path' => 'heroicon-o-arrow-path',
                //     ])
                //     ->hiddenButtonLabels()
                //     ->grouped(),
            ]);
    }
}
