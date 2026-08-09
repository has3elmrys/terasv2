<?php

namespace App\Filament\Team\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                    ->required(),
                Select::make('icon')
                    ->label('Icon')
                    ->allowHtml()
                    ->options(collect([
                        'heroicon-o-banknotes' => 'Salary / Money',
                        'heroicon-o-cake' => 'Food',
                        'heroicon-o-truck' => 'Transport',
                        'heroicon-o-film' => 'Entertainment',
                        'heroicon-o-shopping-bag' => 'Shopping',
                        'heroicon-o-heart' => 'Health',
                        'heroicon-o-academic-cap' => 'Education',
                        'heroicon-o-bolt' => 'Utilities',
                        'heroicon-o-paper-airplane' => 'Travel',
                        'heroicon-o-arrow-path' => 'Subscription',
                    ])->mapWithKeys(fn ($label, $icon) => [
                        $icon => svg($icon, 'w-4 h-4 inline-block me-2')->toHtml().$label,
                    ]))
                    ->searchable(),
            ]);
    }
}
