<?php

namespace App\Filament\Team\Resources\Transactions\Pages;

use App\Filament\Team\Resources\Transactions\TransactionResource;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickAdd')
                ->label('Quick Add')
                ->form([
                    TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->autofocus(),

                    ToggleButtons::make('category_id')
                        ->label('Category')
                        ->options(fn () => Category::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                        ->icons(fn () => Category::where('team_id', Filament::getTenant()->id)->pluck('icon', 'id')->toArray())
                        ->required(),

                    Select::make('bank_id')
                        ->label('Bank')
                        ->relationship('bank', 'name', fn ($query) => $query->where('type', 'cash'))
                        ->default(fn () => Bank::where('team_id', Filament::getTenant()->id)->where('type', 'cash')->orderBy('id')->value('id'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $bank = Bank::findOrFail($data['bank_id']);
                    $category = Category::findOrFail($data['category_id']);
                    $baseCurrency = Filament::getTenant()->baseCurrency;

                    $rateToBase = bcdiv($bank->currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);
                    $baseAmount = bcmul($data['amount'], $rateToBase, 4);

                    Transaction::create([
                        'team_id' => Filament::getTenant()->id,
                        'bank_id' => $bank->id,
                        'category_id' => $category->id,
                        'currency_id' => $bank->currency_id,
                        'type' => $category->type,
                        'amount' => $data['amount'],
                        'rate_to_base' => $rateToBase,
                        'base_amount' => $baseAmount,
                        'date' => today(),
                    ]);
                }),

            CreateAction::make(),
        ];
    }
}
