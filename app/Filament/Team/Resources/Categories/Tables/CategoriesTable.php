<?php

namespace App\Filament\Team\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->icon(fn ($record) => $record->icon)
                    ->searchable(),

                TextColumn::make('type')
                    ->color(fn (string $state) => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'income' => Heroicon::ArrowLongDown,
                        'expense' => Heroicon::ArrowLongUp,
                    })
                    ->badge(),

                TextColumn::make('budget_spent')
                    ->label('Spent This Month')
                    ->getStateUsing(fn (Category $record) => $record->budget
                        ? $record->team->baseCurrency->symbol.' '.number_format((float) $record->budget->spent(), 0).' / '.number_format((float) $record->budget->amount_limit, 0)
                        : null)
                    ->placeholder('—'),

                TextColumn::make('budget_status')
                    ->label('Budget')
                    ->getStateUsing(fn (Category $record) => $record->budget ? match ($record->budget->status()) {
                        'over' => 'Over budget',
                        'warning' => 'Near limit',
                        default => 'On track',
                    } : null)
                    ->color(fn (Category $record) => match ($record->budget?->status()) {
                        'over' => 'danger',
                        'warning' => 'warning',
                        default => 'success',
                    })
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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
