<?php

namespace App\Filament\Team\Widgets;

use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class SpendingByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Pengeluaran per Kategori (Bulan Ini)';

    protected function getData(): array
    {
        $teamId = Filament::getTenant()->id;

        $totals = Transaction::query()
            ->where('team_id', $teamId)
            ->where('type', 'expense')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('category_id, SUM(base_amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $totals->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
                ],
            ],
            'labels' => $totals->map(fn ($row) => $row->category?->name ?? 'Tanpa kategori')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
