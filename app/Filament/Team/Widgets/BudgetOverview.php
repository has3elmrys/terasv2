<?php

namespace App\Filament\Team\Widgets;

use App\Models\Budget;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetOverview extends StatsOverviewWidget
{
     use InteractsWithPageFilters;
    protected static ?int $sort = 3;

    protected int|array|null $columns = 2;

    protected function getStats(): array
    {
        $baseCurrency = Filament::getTenant()->baseCurrency;

        return Budget::where('team_id', Filament::getTenant()->id)
            ->get()
            ->map(function (Budget $budget) use ($baseCurrency) {
                $color = match ($budget->status()) {
                    'over' => 'danger',
                    'warning' => 'warning',
                    default => 'success',
                };

                return Stat::make(
                    $budget->category->name,
                    $baseCurrency->symbol.' '.Money::abbreviate($budget->spent()).' / '.Money::abbreviate($budget->amount_limit)
                )
                    ->description($budget->percentUsed().'% used')
                    ->color($color);
            })
            ->toArray();
    }
}
