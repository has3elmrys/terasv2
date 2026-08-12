<?php

namespace App\Filament\Team\Resources\Banks\Pages;

use App\Filament\Team\Resources\Banks\BankResource;
use App\Models\Bank;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\RawJs;
use Illuminate\Support\Str;

class ListBanks extends ListRecords
{
    protected static string $resource = BankResource::class;

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
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
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
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->helperText('Calculated automatically from Amount Received × rate. Switch to manual to correct it.'),
                ])
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
