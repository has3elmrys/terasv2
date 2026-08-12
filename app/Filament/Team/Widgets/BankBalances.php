<?php

namespace App\Filament\Team\Widgets;

use App\Models\Bank;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BankBalances extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|array|null $columns = 2;

    protected function getStats(): array
    {
        return Bank::where('team_id', Filament::getTenant()->id)
            ->where('type', 'cash')
            ->get()
            ->map(fn (Bank $bank) => Stat::make(
                $bank->name,
                $bank->currency->symbol.' '.Money::abbreviate($bank->balance())
            )->color('gray'))
            ->toArray();
    }
}
