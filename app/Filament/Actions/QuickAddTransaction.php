<?php

namespace App\Filament\Actions;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class QuickAddTransaction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'Add transaction';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Add transaction')
            ->form([

                TextInput::make('note')
                ->label('Transaction name'),

                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->autofocus(),

                ToggleButtons::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::where('team_id', Filament::getTenant()->id)
                        ->get()
                        ->mapWithKeys(fn ($category) => [$category->id => "{$category->name} ({$category->type})"]))
                    ->icons(fn () => Category::where('team_id', Filament::getTenant()->id)->pluck('icon', 'id')->toArray())
                    ->required(),

                Select::make('bank_id')
                    ->label('Bank')
                    ->options(fn () => Bank::where('team_id', Filament::getTenant()->id)->where('type', 'cash')->pluck('name', 'id'))
                    ->default(fn () => Bank::where('team_id', Filament::getTenant()->id)->where('type', 'cash')->orderBy('id')->value('id'))
                    ->required(),


                FileUpload::make('image_path')
                    ->openable()
                    ->imageEditor()
                    ->maxSize(15360)
                    ->preserveFilenames()
                    ->directory(fn () => 'receipts/'.Filament::getTenant()?->id.'/'.now()->format('Y/m'))
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                        return (string) str($file->getClientOriginalName())->prepend(now()->timestamp);
                    })
                    ->image(),
            ])
            ->action(function (array $data, Action $action) {
                $bank = Bank::findOrFail($data['bank_id']);
                $category = Category::findOrFail($data['category_id']);

                if ($category->type === 'expense' && ! $bank->hasSufficientBalance($data['amount'])) {
                    Notification::make()
                        ->danger()
                        ->title('Insufficient Balance')
                        ->body("{$bank->name} balance is ".number_format((float) $bank->balance(), 2).", not enough for {$data['amount']}.")
                        ->send();

                    $action->halt();
                }

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
                    'note' => $data['note'] ?? null,
                    'image_path' => $data['image_path'] ?? null,
                    'date' => today(),
                ]);
            });
    }
}
