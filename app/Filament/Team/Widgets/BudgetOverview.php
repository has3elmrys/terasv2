<?php

namespace App\Filament\Team\Widgets;

use App\Models\Budget;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

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
                    $baseCurrency->symbol.' '.number_format((float) $budget->spent(), 0).' / '.number_format((float) $budget->amount_limit, 0)
                )
                    ->description($budget->percentUsed().'% used')
                    ->color($color);
            })
            ->toArray();
    }
}
