<?php

namespace App\Filament\Team\Widgets;

use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
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
            ->heading('Recent Activity')
            ->paginated(false)
            ->columns([
                TextColumn::make('date')->date('d M'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->default('Transfer')
                    ->badge()
                    ->color(fn ($record) => match ($record->type) {
                        'income', 'transfer_in' => 'success',
                        'expense', 'transfer_out' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('bank.name')->label('Bank'),
                TextColumn::make('base_amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->bank->currency->code ?? 'IDR'),
            ]);
    }
}
