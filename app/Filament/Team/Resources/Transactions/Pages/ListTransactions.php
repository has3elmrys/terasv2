<?php

namespace App\Filament\Team\Resources\Transactions\Pages;

use App\Filament\Team\Resources\Transactions\TransactionResource;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

     public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'income')),
            'expense' => Tab::make('Expenses')
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'expense')),
            'transfer' => Tab::make('Transfer')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('type', ['transfer_in', 'transfer_out'])),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

Action::make('exchange')
    ->label('Currency Exchange')
    ->form([
        Select::make('from_bank_id')
            ->label('From Bank')
            ->options(fn () => Bank::where('team_id', Filament::getTenant()->id)->where('type', 'cash')->get()->mapWithKeys(fn ($b) => [$b->id => "{$b->name} ({$b->currency->code})"]))
            ->live()
            ->required()
            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcExchangeOut($get, $set)),

        Select::make('to_bank_id')
            ->label('To Bank')
            ->options(fn (Get $get) => Bank::where('team_id', Filament::getTenant()->id)->where('type', 'cash')->where('id', '!=', $get('from_bank_id'))->get()->mapWithKeys(fn ($b) => [$b->id => "{$b->name} ({$b->currency->code})"]))
            ->live()
            ->required()
            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcExchangeOut($get, $set)),

        TextInput::make('amount_in')
            ->label('Amount received')
            ->numeric()
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcExchangeOut($get, $set)),

        Radio::make('rate_mode')
            ->label('Rate')
            ->options(['auto' => 'Pakai rate otomatis', 'manual' => 'Isi rate manual'])
            ->default('auto')
            ->live()
            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcExchangeOut($get, $set)),

        TextInput::make('manual_rate')
            ->label('1 unit "To Bank" = how many units of "From Bank"')
            ->numeric()
            ->visible(fn (Get $get) => $get('rate_mode') === 'manual')
            ->live(onBlur: true)
            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcExchangeOut($get, $set)),

        TextInput::make('amount_out')
            ->label('Deducted from Source Bank (auto)')
            ->numeric()
            ->disabled()
            ->dehydrated()
            ->required()
            ->helperText('Calculated automatically from Amount Received × rate. Switch to manual to correct it.'),
    ])
    // Action check valas
    ->action(function (array $data, Action $action) {
        $fromBank = Bank::findOrFail($data['from_bank_id']);
        $toBank = Bank::findOrFail($data['to_bank_id']);

        if (! $fromBank->hasSufficientBalance($data['amount_out'])) {
            Notification::make()
                ->danger()
                ->title('Insufficient balance')
                ->body("{$fromBank->name} balance is ".number_format((float) $fromBank->balance(), 2).", not enough for {$data['amount_out']}.")
                ->send();

            $action->halt();
        }

        $baseCurrency = Filament::getTenant()->baseCurrency;
        $transferGroupId = (string) Str::uuid();

        $rateOutToBase = bcdiv($fromBank->currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);
        $rateInToBase = bcdiv($toBank->currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);

        Transaction::create([
            'team_id' => Filament::getTenant()->id,
            'bank_id' => $fromBank->id,
            'currency_id' => $fromBank->currency_id,
            'type' => 'transfer_out',
            'transfer_group_id' => $transferGroupId,
            'amount' => $data['amount_out'],
            'rate_to_base' => $rateOutToBase,
            'base_amount' => bcmul($data['amount_out'], $rateOutToBase, 4),
            'date' => today(),
        ]);

        Transaction::create([
            'team_id' => Filament::getTenant()->id,
            'bank_id' => $toBank->id,
            'currency_id' => $toBank->currency_id,
            'type' => 'transfer_in',
            'transfer_group_id' => $transferGroupId,
            'amount' => $data['amount_in'],
            'rate_to_base' => $rateInToBase,
            'base_amount' => bcmul($data['amount_in'], $rateInToBase, 4),
            'date' => today(),
        ]);
    }),

            Action::make('quickAdd')
                ->label('Quick Add')
                ->form([
                    TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->autofocus(),

                    ToggleButtons::make('category_id')
                        ->label('Category')
                        ->options(fn () => Category::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                        ->icons(fn () => Category::where('team_id', Filament::getTenant()->id)->pluck('icon', 'id')->toArray())
                        ->required(),

                    Select::make('bank_id')
                        ->label('Bank')
                        ->relationship('bank', 'name', fn ($query) => $query->where('type', 'cash'))
                        ->default(fn () => Bank::where('team_id', Filament::getTenant()->id)->where('type', 'cash')->orderBy('id')->value('id'))
                        ->required(),
                ])
                // Action check saldo bank
                ->action(function (array $data, Action $action) {
                    $bank = Bank::findOrFail($data['bank_id']);
                    $category = Category::findOrFail($data['category_id']);

                    if ($category->type === 'expense' && ! $bank->hasSufficientBalance($data['amount'])) {
                        Notification::make()
                            ->danger()
                            ->title('Insufficient Balance')
                            ->body("{$bank->name} balance is ".number_format((float) $bank->balance(), 2).", not enough for {$data['amount']}.")
                            ->send();

                        $action->halt();
                    }

                    $baseCurrency = Filament::getTenant()->baseCurrency;

                    $rateToBase = bcdiv($bank->currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);
                    $baseAmount = bcmul($data['amount'], $rateToBase, 4);

                    Transaction::create([
                        'team_id' => Filament::getTenant()->id,
                        'bank_id' => $bank->id,
                        'category_id' => $category->id,
                        'currency_id' => $bank->currency_id,
                        'type' => $category->type,
                        'amount' => $data['amount'],
                        'rate_to_base' => $rateToBase,
                        'base_amount' => $baseAmount,
                        'date' => today(),
                    ]);
                }),
                // ->action(function (array $data) {
                //     $bank = Bank::findOrFail($data['bank_id']);
                //     $category = Category::findOrFail($data['category_id']);
                //     $baseCurrency = Filament::getTenant()->baseCurrency;

                //     $rateToBase = bcdiv($bank->currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);
                //     $baseAmount = bcmul($data['amount'], $rateToBase, 4);

                //     Transaction::create([
                //         'team_id' => Filament::getTenant()->id,
                //         'bank_id' => $bank->id,
                //         'category_id' => $category->id,
                //         'currency_id' => $bank->currency_id,
                //         'type' => $category->type,
                //         'amount' => $data['amount'],
                //         'rate_to_base' => $rateToBase,
                //         'base_amount' => $baseAmount,
                //         'date' => today(),
                //     ]);
                // }),

            CreateAction::make(),
        ];
    }


    private static function recalcExchangeOut(Get $get, Set $set): void
{
    $fromBankId = $get('from_bank_id');
    $toBankId = $get('to_bank_id');
    $amountIn = $get('amount_in');

    if (! $fromBankId || ! $toBankId || ! $amountIn) {
        return;
    }

    if ($get('rate_mode') === 'manual') {
        $rate = $get('manual_rate');
        if (! $rate) {
            return;
        }
    } else {
        $rate = bcdiv(Bank::find($toBankId)->currency->rate_to_pivot, Bank::find($fromBankId)->currency->rate_to_pivot, 12);
    }

    $set('amount_out', bcmul($amountIn, $rate, 4));
}

}
