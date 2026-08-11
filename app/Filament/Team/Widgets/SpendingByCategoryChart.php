<?php

namespace App\Filament\Team\Widgets;

use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SpendingByCategoryChart extends ChartWidget
{

    protected static ?int $sort = 3;
    use InteractsWithPageFilters;

    protected ?string $heading = 'Spending by Category';
    protected bool $isCollapsible = true;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getData(): array
    {
        $teamId = Filament::getTenant()->id;
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth()->toDateString();
        $endDate = $this->pageFilters['endDate'] ?? now()->endOfMonth()->toDateString();

        $totals = Transaction::query()
            ->where('team_id', $teamId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('category_id, SUM(base_amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        $palette = ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'];

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $totals->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
                    'backgroundColor' => $totals->map(fn ($row, $i) => $palette[$i % count($palette)])->values()->toArray(),
                ],
            ],
            'labels' => $totals->map(fn ($row) => $row->category?->name ?? 'Uncategorized')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
