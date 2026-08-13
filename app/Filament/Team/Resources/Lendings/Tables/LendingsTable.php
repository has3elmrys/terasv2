<?php

namespace App\Filament\Team\Resources\Lendings\Tables;

use App\Models\Bank;
use App\Models\Lending;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LendingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lender.name')->label('Lender'),
                TextColumn::make('direction')->badge(),
                TextColumn::make('bank.name')->label('Account'),
                TextColumn::make('currency.code')->label('Currency'),
                TextColumn::make('amount')->numeric()->sortable(),
                TextColumn::make('remaining')
                    ->label('Remaining')
                    ->state(fn (Lending $record) => $record->remaining())
                    ->numeric(),
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('due_date')->date()->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('recordPayment')
                    ->label('Record Payment')
                    ->visible(fn (Lending $record) => in_array($record->status, ['active', 'partially_paid']))
                    ->form([
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),

                        Select::make('bank_id')
                            ->label('Deposit to / Pay from')
                            ->relationship('bank', 'name', fn ($query) => $query->where('type', 'cash'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->currency->code}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('date')
                            ->default(today())
                            ->required(),

                        FileUpload::make('image_path')
                            ->label('Payment Proof')
                            ->openable()
                            ->disk('s3')
                            ->visibility('private')
                            ->optimize('webp')
                            ->imageEditor()
                            // ->disk('s3')
                            ->maxSize(15360)
                            ->preserveFilenames()
                            ->directory(fn () => 'lendings/'.Filament::getTenant()?->id.'/'.now()->format('Y/m'))
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                $path = (string) str($file->getClientOriginalName())->prepend(now()->timestamp);

                                return $path;
                            })
                            ->image(),
                    ])
                    ->action(function (Lending $record, array $data, Action $action) {
                        if ($record->direction === 'borrow') {
                            $bank = Bank::findOrFail($data['bank_id']);

                            if (! $bank->hasSufficientBalance($data['amount'])) {
                                Notification::make()
                                    ->danger()
                                    ->title('Insufficient balance')
                                    ->body("{$bank->name} balance is ".number_format((float) $bank->balance(), 2).", not enough for {$data['amount']}.")
                                    ->send();

                                $action->halt();
                            }
                        }

                        DB::transaction(function () use ($record, $data) {
                            $rateToBase = bcdiv($record->currency->rate_to_pivot, $record->team->baseCurrency->rate_to_pivot, 12);
                            $baseAmount = bcmul($data['amount'], $rateToBase, 4);

                            $ghostType = $record->direction === 'lend' ? 'receivable' : 'payable';

                            $ghostBank = Bank::where('team_id', $record->team_id)
                                ->where('currency_id', $record->currency_id)
                                ->where('type', $ghostType)
                                ->first();

                            $transferGroupId = (string) Str::uuid();

                            $legs = $record->direction === 'lend'
                                ? [
                                    ['bank_id' => $ghostBank->id, 'type' => 'transfer_out'],
                                    ['bank_id' => $data['bank_id'], 'type' => 'transfer_in'],
                                ]
                                : [
                                    ['bank_id' => $data['bank_id'], 'type' => 'transfer_out'],
                                    ['bank_id' => $ghostBank->id, 'type' => 'transfer_in'],
                                ];

                            foreach ($legs as $leg) {
                                Transaction::create([
                                    'team_id' => $record->team_id,
                                    'bank_id' => $leg['bank_id'],
                                    'currency_id' => $record->currency_id,
                                    'type' => $leg['type'],
                                    'transfer_group_id' => $transferGroupId,
                                    'amount' => $data['amount'],
                                    'rate_to_base' => $rateToBase,
                                    'base_amount' => $baseAmount,
                                    'date' => $data['date'],
                                    'lending_id' => $record->id,
                                    'image_path' => $leg['bank_id'] === $data['bank_id'] ? ($data['image_path'] ?? null) : null,
                                ]);
                            }

                            $signedRemaining = $record->signedRemaining();
                            $record->status = bccomp($signedRemaining, '0', 4) <= 0 ? 'paid' : 'partially_paid';
                            $record->save();
                        });
                    }),
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
