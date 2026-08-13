<?php

namespace App\Filament\Team\Widgets;

use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
     use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    // protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->where('team_id', Filament::getTenant()->id)
                    ->whereHas('bank', fn ($q) => $q->where('type', 'cash'))
                    ->latest('date')
                    ->latest('id')
                    ->limit(5)
            )
            ->heading('Recent Transaction')
            ->paginated(false)
            ->columns([
                // TextColumn::make('date')->date('d M'),
                // TextColumn::make('category.name')
                //     ->label('Category')
                //     ->default('Transfer')
                //     ->badge()
                //     ->color(fn ($record) => match ($record->type) {
                //         'income', 'transfer_in' => 'success',
                //         'expense', 'transfer_out' => 'danger',
                //         default => 'gray',
                //     }),
                // TextColumn::make('bank.name')->label('Bank'),
                // TextColumn::make('base_amount')
                //     ->label('Amount')
                //     ->money(fn ($record) => $record->bank->currency->code ?? 'IDR'),
                TextColumn::make('date')
                ->date('d F Y'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->default(function ($record) {
                        if (! $record->lending) {
                            return 'Currency Exchange';
                        }

                        return match ($record->lending->direction.':'.$record->type) {
                            'lend:transfer_out' => 'Lend Out',
                            'lend:transfer_in' => 'Lend Repaid',
                            'borrow:transfer_in' => 'Borrowed',
                            'borrow:transfer_out' => 'Borrow Repaid',
                            default => 'Lending',
                        };
                    })
                    ->icon(fn ($record) => $record->category?->icon ?? ($record->lending ? 'heroicon-o-banknotes' : 'heroicon-o-arrows-right-left'))
                    ->badge()
                    ->color(fn ($record) => match ($record->type) {
                        'income', 'transfer_in' => 'success',
                        'expense', 'transfer_out' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('bank.name')
                    ->label('Transaction name')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->lending && in_array($record->bank->type, ['receivable', 'payable'])) {
                            return $record->lending->lender->name;
                        }

                        return $state;
                    })
                    ->description(function ($record) {
                        if ($record->lending && $record->bank->type === 'cash') {
                            $label = $record->lending->direction === 'lend' ? 'Lending' : 'Borrow';

                            return "{$record->lending->lender->name} · {$label}";
                        }

                        return $record->note;
                    }, position: 'above'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state, $record) => $record->currency->symbol.number_format((float) $state, $record->currency->decimal_places)),
            ]);
    }
}
