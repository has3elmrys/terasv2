<?php

namespace App\Filament\Team\Widgets;

use App\Models\Bank;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewStats extends StatsOverviewWidget
{

    protected static ?int $sort = 1;
        protected int|array|null $columns = 2;
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $teamId = Filament::getTenant()->id;
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth()->toDateString();
        $endDate = $this->pageFilters['endDate'] ?? now()->endOfMonth()->toDateString();


        $netWorth = Bank::where('team_id', $teamId)
            ->get()
            ->reduce(function (string $carry, Bank $bank) {
                $rateToBase = bcdiv($bank->currency->rate_to_pivot, Filament::getTenant()->baseCurrency->rate_to_pivot, 12);
                $balanceInBase = bcmul($bank->balance(), $rateToBase, 4);

                return bcadd($carry, $balanceInBase, 4);
            }, '0');

        $income = Transaction::where('team_id', $teamId)
            ->where('type', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('base_amount');

        $expense = Transaction::where('team_id', $teamId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('base_amount');

        $net = bcsub($income, $expense, 4);

        return [


            Stat::make('Income', number_format((float) $income, 0))
                ->color('success'),

            Stat::make('Expense', number_format((float) $expense, 0))
                ->color('danger'),

            Stat::make('Net Worth', number_format((float) $netWorth, 0))
                ->color('primary'),

            Stat::make('Net', number_format((float) $net, 0))
                ->color(bccomp($net, '0', 4) >= 0 ? 'success' : 'danger'),
        ];
    }
}
