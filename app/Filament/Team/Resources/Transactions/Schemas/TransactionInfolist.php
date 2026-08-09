<?php

namespace App\Filament\Team\Resources\Transactions\Schemas;

use App\Models\Transaction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('team_id')
                    ->numeric(),
                TextEntry::make('bank_id')
                    ->numeric(),
                TextEntry::make('category_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('currency_id')
                    ->numeric(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('transfer_group_id')
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('rate_to_base')
                    ->numeric(),
                TextEntry::make('base_amount')
                    ->numeric(),
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                ImageEntry::make('image_path')
                    ->placeholder('-'),
                TextEntry::make('subscription_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('occurrence_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('lending_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Transaction $record): bool => $record->trashed()),
            ]);
    }
}
