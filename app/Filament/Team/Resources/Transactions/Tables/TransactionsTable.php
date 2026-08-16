<?php

namespace App\Filament\Team\Resources\Transactions\Tables;

use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query) => $query->orderByDesc('date')->orderByDesc('id'))
            ->modifyQueryUsing(fn ($query) => $query->whereHas('bank', fn ($q) => $q->where('type', 'cash')))
            ->columns([
                TextColumn::make('date')
                ->date('d F Y')
                ->sortable(),

                // TextColumn::make('category.name')
                //     ->label('Category')
                //     ->formatStateUsing(fn ($state, $record) => $state ?? 'Tukar Valas')
                //     ->icon(fn ($record) => $record->category?->icon ?? 'heroicon-o-arrows-right-left')
                //     ->badge()
                //     ->color(fn ($record) => match ($record->type) {
                //         'income' => 'success',
                //         'expense' => 'danger',
                //         default => 'gray',
                //     }),

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
                    ->formatStateUsing(fn ($state, $record) => $record->currency->symbol.number_format((float) $state, $record->currency->decimal_places))
                    ->sortable(),

                TextColumn::make('type')->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rate_to_base')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('base_amount')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image_path')->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->hidden(fn (Transaction $record) => filled($record->lending_id)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
