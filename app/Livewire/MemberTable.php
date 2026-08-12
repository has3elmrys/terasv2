<?php

namespace App\Livewire;

use App\Models\TeamInvitation;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MemberTable extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => TeamInvitation::query()->where('team_id', Filament::getTenant()->id))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email'),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (TeamInvitation $record) => match (true) {
                        $record->accepted_at !== null => 'Accepted',
                        $record->isExpired() => 'Expired',
                        $record->opened_at !== null => 'Opened',
                        default => 'Pending',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Accepted' => 'success',
                        'Expired' => 'danger',
                        'Opened' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('inviter.name')->label('Invited By'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('copyLink')
                    ->label('Copy Link')
                    ->icon('heroicon-o-clipboard')
                    ->visible(fn (TeamInvitation $record) => is_null($record->accepted_at) && ! $record->isExpired())
                    ->action(function (TeamInvitation $record) {
                        Notification::make()
                            ->title('Link invite')
                            ->body(url("/invite/{$record->token}"))
                            ->persistent()
                            ->send();
                    }),

                Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->visible(fn (TeamInvitation $record) => is_null($record->accepted_at))
                    ->action(fn (TeamInvitation $record) => $record->delete()),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
