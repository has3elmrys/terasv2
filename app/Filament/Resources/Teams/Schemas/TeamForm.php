<?php

namespace App\Filament\Resources\Teams\Schemas;

use Closure;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->inlineLabel()
                    ->live(onBlur: true)
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if (strtolower(trim($value)) === 'admin') {
                                $fail('Cannot use "admin" as a team name');
                            }
                        },
                    ])
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->required(),

                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->inlineLabel()
                    ->required(),

                Select::make('users')
                    ->inlineLabel()
                    ->multiple()
                    ->relationship('users', 'name')
                    ->preload(),
            ]);
    }
}
