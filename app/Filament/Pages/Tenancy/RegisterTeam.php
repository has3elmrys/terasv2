<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Vault';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                     ->inlineLabel()
                    ->live(onBlur: true)
                    ->unique()
                    ->validationMessages([
                        'unique' => 'Team name already exists. Please choose a different name.',
                    ])
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
                    ->hidden()
                    ->dehydrated()
                    ->inlineLabel()
                    ->required(),

                Select::make('base_currency_id')
                    ->helperText('Choose your base currency')
                    ->label('Base Currency')
                    ->relationship('baseCurrency', 'code')
                    ->inlineLabel()
                    ->required(),
            ]);
    }

    // protected function handleRegistration(array $data): Team
    // {
    //     $team = Team::create($data);

    //     $team->users()->attach(auth()->user());

    //     return $team;
    // }

    public static function canView(): bool
{
    if (auth()->user()?->teams()->exists()) {
        return false;
    }

    return parent::canView();
}

protected function handleRegistration(array $data): Team
    {
        $data['owner_id'] = auth()->id();

        $team = Team::create($data);

        $team->users()->attach(auth()->user());

        return $team;
    }
}
