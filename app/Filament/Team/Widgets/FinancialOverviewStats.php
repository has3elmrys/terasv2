<?php

namespace App\Filament\Team\Widgets;

use App\Models\Transaction;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    protected int|array|null $columns = 3;
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $teamId = Filament::getTenant()->id;
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth()->toDateString();
        $endDate = $this->pageFilters['endDate'] ?? now()->endOfMonth()->toDateString();

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
            Stat::make('Income', Money::abbreviate($income))
                ->description('This period')
                ->color('success')
                ->icon(Heroicon::ArrowLongDown),

            Stat::make('Expense', Money::abbreviate($expense))
                ->description('This period')
                ->color('danger')
                ->icon(Heroicon::ArrowLongUp),

            Stat::make('Net', Money::abbreviate($net))
                ->description('This period')
                ->color(bccomp($net, '0', 4) >= 0 ? 'success' : 'danger')
                ->icon(bccomp($net, '0', 4) >= 0 ? Heroicon::ArrowLongDown : Heroicon::ArrowLongUp),
        ];
    }
}
