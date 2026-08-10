<?php

namespace App\Filament\Team\Widgets;

use App\Models\Bank;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class BankBalancesChart extends ChartWidget
{
    protected ?string $heading = 'Saldo per Bank';

    protected function getData(): array
    {
        $banks = Bank::where('team_id', Filament::getTenant()->id)
            ->where('type', 'cash')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Saldo',
                    'data' => $banks->map(fn (Bank $bank) => (float) $bank->balance())->toArray(),
                ],
            ],
            'labels' => $banks->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
