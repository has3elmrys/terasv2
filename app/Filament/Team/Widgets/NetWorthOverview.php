<?php

namespace App\Filament\Team\Widgets;

use App\Models\Bank;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NetWorthOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $netWorth = Bank::where('team_id', Filament::getTenant()->id)
            ->get()
            ->reduce(function (string $carry, Bank $bank) {
                $rateToBase = bcdiv($bank->currency->rate_to_pivot, Filament::getTenant()->baseCurrency->rate_to_pivot, 12);
                $balanceInBase = bcmul($bank->balance(), $rateToBase, 4);

                return bcadd($carry, $balanceInBase, 4);
            }, '0');

        return [
            Stat::make('Net Worth', Money::abbreviate($netWorth))
                ->description('As of today')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
