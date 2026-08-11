<?php

namespace App\Filament\Team\Pages;

use App\Filament\Actions\QuickAddTransaction;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Override;

class TeamDashboard extends Dashboard
{
    use HasFiltersForm;

    // #[Override]
    protected function getHeaderActions(): array
    {
        return [
            QuickAddTransaction::make()
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('preset')
                            ->label('Date Range')
                            ->options([
                                'this_week' => 'Last 7 Days',
                                'this_month' => 'This Month',
                                'custom' => 'Custom',
                            ])
                            ->default('this_month')
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'this_week') {
                                    $set('startDate', now()->subDays(6)->toDateString());
                                    $set('endDate', now()->toDateString());
                                } elseif ($state === 'this_month') {
                                    $set('startDate', now()->startOfMonth()->toDateString());
                                    $set('endDate', now()->endOfMonth()->toDateString());
                                }
                            }),

                        Placeholder::make('range_display')
                            ->label('')
                            ->content(fn (Get $get): string => $get('startDate') && $get('endDate')
                                ? \Carbon\Carbon::parse($get('startDate'))->translatedFormat('d M Y') . ' — ' . \Carbon\Carbon::parse($get('endDate'))->translatedFormat('d M Y')
                                : '')
                            ->visible(fn (Get $get) => $get('preset') !== 'custom'),

                        DatePicker::make('startDate')->label('From')->default(now()->startOfMonth()->toDateString())->native(false)
                            ->visible(fn (Get $get) => $get('preset') === 'custom'),
                        DatePicker::make('endDate')->label('To')->default(now()->endOfMonth()->toDateString())->native(false)
                            ->visible(fn (Get $get) => $get('preset') === 'custom'),
                    ])
                    ->columnSpanFull()
                    ->columns(3),
            ]);
    }
}
