<?php

namespace App\Filament\Team\Widgets;

use App\Models\Bank;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $teamId = Filament::getTenant()->id;

        $netWorth = Bank::where('team_id', $teamId)
            ->get()
            ->reduce(function (string $carry, Bank $bank) {
                $rateToBase = bcdiv($bank->currency->rate_to_pivot, Filament::getTenant()->baseCurrency->rate_to_pivot, 12);
                $balanceInBase = bcmul($bank->balance(), $rateToBase, 4);

                return bcadd($carry, $balanceInBase, 4);
            }, '0');

        $income = Transaction::where('team_id', $teamId)
            ->where('type', 'income')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('base_amount');

        $expense = Transaction::where('team_id', $teamId)
            ->where('type', 'expense')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('base_amount');

        return [
            Stat::make('Net Worth', number_format((float) $netWorth, 0))
                ->color('primary'),

            Stat::make('Pemasukan Bulan Ini', number_format((float) $income, 0))
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', number_format((float) $expense, 0))
                ->color('danger'),
        ];
    }
}

