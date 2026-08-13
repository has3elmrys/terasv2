<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Str;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;
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

                Section::make('First Account')
                    ->description('Where your money sits right now. You can add more later.')
                    ->schema([
                        TextInput::make('bank.name')
                            ->label('Account name')
                            ->placeholder('BCA, Cash, GoPay…')
                            ->inlineLabel()
                            ->required(),

                        TextInput::make('bank.opening_balance')
                            ->label('Current balance')
                            ->inlineLabel()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        DatePicker::make('bank.opening_balance_date')
                            ->label('Balance as of')
                            ->inlineLabel()
                            ->default(now())
                            ->required(),
                    ]),
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
    $bank = $data['bank'] ?? [];
    unset($data['bank']);

    $data['owner_id'] = auth()->id();

    return DB::transaction(function () use ($data, $bank) {
        $team = Team::create($data);

        $team->users()->attach(auth()->user());

        if (filled($bank['name'] ?? null)) {
            Bank::create([
                ...$bank,
                'team_id' => $team->id,
                'user_id' => auth()->id(),
                'currency_id' => $team->base_currency_id,
            ]);
        }

        return $team;
    });
}
}
