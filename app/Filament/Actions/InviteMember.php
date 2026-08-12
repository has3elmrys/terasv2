<?php

namespace App\Filament\Actions;

use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class InviteMember extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'inviteMember';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Invite Member')
            ->visible(fn () => $this->canInvite())
            ->form([
                TextInput::make('email')
                    ->email()
                    ->required(),
            ])
            ->action(function (array $data, Action $action) {
                $team = Filament::getTenant();

                $existingUser = User::where('email', $data['email'])->first();

                if ($existingUser && $team->users()->where('user_id', $existingUser->id)->exists()) {
                    Notification::make()
                        ->danger()
                        ->title('Udah jadi member')
                        ->body("{$data['email']} udah jadi anggota {$team->name}.")
                        ->send();

                    $action->halt();
                }

                $invite = TeamInvitation::create([
                    'team_id' => $team->id,
                    'email' => $data['email'],
                    'invited_by' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Invite link dibuat, berlaku 1 jam')
                    ->body(url("/invite/{$invite->token}"))
                    ->persistent()
                    ->success()
                    ->send();
            });


    }

    protected function canInvite(): bool
    {
        $team = Filament::getTenant();

        return auth()->user()->hasRole('super_admin') || $team->owner_id === auth()->id();
    }
}
