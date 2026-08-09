<?php

namespace App\Filament\Team\Resources\Categories\Tables;

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
                // TextColumn::make('team_id')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('name')
                //     ->searchable(),
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
