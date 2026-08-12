<?php

namespace App\Filament\Team\Resources\Lendings\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LendingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lender_id')
                    ->inlineLabel()
                    ->searchable()
                    ->relationship('lender','name')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('phone')->tel(),
                        TextInput::make('email')->email(),
                    ])
                    ->required(),

                Select::make('bank_id')
                    ->label('Cash account')

                    ->inlinelabel()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('bank','name', fn ($query) => $query->where('type','cash'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->currency->code} {$record->currency->name} Balance: {$record->opening_balance}"),

                // Select::make('currency_id')
                //     ->inlinelabel()
                //     ->required()
                //     ->relationship('currency','code'),

                Radio::make('direction')
                    ->inlinelabel()
                    ->options(['lend' => 'Lending out (Money Leaving)', 'borrow' => 'Borrowing (Money coming in)'])
                    ->required(),

                TextInput::make('amount')
                    ->inlinelabel()
                    ->required()
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
                DatePicker::make('date')
                    ->inlinelabel()
                    ->required(),

                DatePicker::make('due_date')
                    ->inlinelabel(),


                Textarea::make('note')
                    ->columnSpanFull()
                    ->inlinelabel(),

                FileUpload::make('image_path')
                    ->openable()
                    ->imageEditor()
                    // ->disk('s3')
                    ->maxSize(15360)
                    ->preserveFilenames()
                    ->directory(fn () => 'lendings/' . Filament::getTenant()?->id . '/' . now()->format('Y/m'))
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string{
                        $path =  (string) str($file->getClientOriginalName())->prepend(now()->timestamp);
                        return $path;
                    })
                    ->inlineLabel()
                    ->image(),
            ]);
    }
}
