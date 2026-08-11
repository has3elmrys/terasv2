<?php

namespace App\Filament\Team\Resources\Transactions\Pages;

use App\Filament\Team\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'income')),
            'expense' => Tab::make('Expenses')
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'expense')),
            'transfer' => Tab::make('Transfer')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('type', ['transfer_in', 'transfer_out'])),
        ];
    }



    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
