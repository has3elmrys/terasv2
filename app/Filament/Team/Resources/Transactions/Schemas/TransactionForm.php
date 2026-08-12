<?php

namespace App\Filament\Team\Resources\Transactions\Schemas;

use App\Models\Bank;
use App\Models\Category;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bank_id')
                    ->inlineLabel()
                    ->relationship('bank', 'name', fn ($query) => $query->where('type', 'cash'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} {$record->currency->code} {$record->currency->symbol}")
                    ->live()
                    ->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcFromBank($state, $get, $set))
                    ->required(),

                Select::make('category_id')
                    ->inlineLabel()
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->type} ")
                    ->live()
                    ->afterStateUpdated(fn ($state, Set $set) => $set('type', $state ? Category::find($state)?->type : null))
                    ->required(),

                Hidden::make('type')
                ->required(),

                // Select::make('currency_id')
                //     ->relationship('currency', 'code')
                //     ->hidden()
                //     ->required(),

                // Select::make('type')
                //     ->inlineLabel()
                //     ->options([
                //         'income' => 'Income',
                //         'expense' => 'Expense',
                //         'transfer_out' => 'Transfer out',
                //         'transfer_in' => 'Transfer in',
                //     ])
                //     ->required(),

                TextInput::make('amount')
                    ->inlineLabel()
                    ->mask(RawJs::make('$money($input)'))
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcFromBank($get('bank_id'), $get, $set)),

                TextInput::make('rate_to_base')
                    ->inlineLabel()
                    ->required()
                    ->numeric()
                    ->helperText('Filled automatically from the bank rate. Can be corrected manually if needed (e.g. old transactions)'),

                TextInput::make('base_amount')
                    ->inlineLabel()
                     ->helperText(fn () => 'Automatically converted to your base currency ('.Filament::getTenant()->baseCurrency->code.'). Only edit to correct an old transaction.')
                    ->required()
                    ->numeric(),

                DatePicker::make('date')
                    ->label('Transaction date')
                    ->default('now')
                    ->inlineLabel()
                    ->required(),

                Textarea::make('note')
                    ->inlineLabel()
                    ->columnSpanFull(),

                FileUpload::make('image_path')
                    ->openable()
                    ->imageEditor()
                    // ->disk('s3')
                    ->maxSize(15360)
                    ->preserveFilenames()
                    ->directory(fn () => 'receipts/'.Filament::getTenant()?->id.'/'.now()->format('Y/m'))
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                        $path = (string) str($file->getClientOriginalName())->prepend(now()->timestamp);

                        return $path;
                    })
                    ->inlineLabel()
                    ->image(),
            ]);
    }

    private static function recalcFromBank($bankId, Get $get, Set $set): void
    {
        if (! $bankId) {
            return;
        }

        $bank = Bank::find($bankId);
        $baseCurrency = Filament::getTenant()->baseCurrency;

        if (! $bank || ! $baseCurrency) {
            return;
        }

        $set('currency_id', $bank->currency_id);

        $amount = $get('amount');

        if (! $amount) {
            return;
        }

        $rateToBase = bcdiv($bank->currency->rate_to_pivot, $baseCurrency->rate_to_pivot, 12);

        $set('rate_to_base', $rateToBase);
        $set('base_amount', bcmul($amount, $rateToBase, 4));
    }
}
