<?php

namespace App\Filament\Team\Pages;

use App\Filament\Actions\InviteMember;
use App\Livewire\MemberTable;
use App\Models\TeamInvitation;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Members extends Page
{
    protected string $view = 'filament.team.pages.members';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected function getFooterWidgets(): array
{
    return [
       MemberTable::class,
    ];
}

// protected function getFooterWidgetsColumns(): int|array
// {
//     return 1;
// }

    protected function getHeaderActions(): array
    {
        return [
            InviteMember::make(),
        ];
    }

    public function getMembers()
    {
        return Filament::getTenant()->users;
    }

    public function getPendingInvitations()
    {
        return TeamInvitation::where('team_id', Filament::getTenant()->id)
            ->whereNull('accepted_at')
            ->latest()
            ->get();
    }

    public function isOwner($member): bool
    {
        return $member->id === Filament::getTenant()->owner_id;
    }

    public function inviteStatus(TeamInvitation $invite): string
    {
        if ($invite->isExpired()) {
            return 'Expired';
        }

        return $invite->opened_at ? 'Opened' : 'Pending';
    }

    public function inviteLink(TeamInvitation $invite): string
    {
        return url("/invite/{$invite->token}");
    }
}
