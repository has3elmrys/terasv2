<?php

namespace App\Filament\Team\Resources\Subscriptions\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->inlineLabel()
                    ->relationship('category', 'name'),

                Select::make('bank_id')
                    ->inlineLabel()
                    ->label('Billing account')
                    ->relationship('bank', 'name', fn ($query) => $query->where('type', 'cash'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->currency->code}")
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->inlineLabel()
                    ->required(),

                TextInput::make('amount')
                    ->inlineLabel()
                    ->required()
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->stripCharacters(','),

                Select::make('frequency')
                    ->inlineLabel()
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ])
                    ->required(),

                TextInput::make('interval')
                    ->inlineLabel()
                    ->required()
                    ->numeric()
                    ->default(1),

                DatePicker::make('start_date')
                    ->inlineLabel()
                    ->required(),

                DatePicker::make('next_date')
                    ->inlineLabel()
                    ->label('Next billing date (leave blank = same as start date)'),

                Textarea::make('description')
                    ->inlineLabel()
                    ->columnSpanFull(),

                FileUpload::make('receipt_template')
                    ->openable()
                    ->imageEditor()
                    // ->disk('s3')
                    ->maxSize(15360)
                    ->preserveFilenames()
                    ->directory(fn () => 'subscriptions/' . Filament::getTenant()?->id . '/' . now()->format('Y/m'))
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string{
                        $path =  (string) str($file->getClientOriginalName())->prepend(now()->timestamp);
                        return $path;
                    })
                    ->inlineLabel()
                    ->image(),
            ]);
    }
}
