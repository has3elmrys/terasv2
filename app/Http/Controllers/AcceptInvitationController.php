<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AcceptInvitationController extends Controller
{

 public function __construct()
    {
        Filament::setCurrentPanel('team');
    }
    public function show(string $token)
    {
        $invite = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invite->accepted_at) {
            return view('invitations.invalid', ['message' => 'Invite ini udah dipake.']);
        }

        if ($invite->isExpired()) {
            return view('invitations.invalid', ['message' => 'Invite ini udah kadaluarsa, minta link baru.']);
        }

        $invite->markOpened();

        if (Auth::check()) {
            if (strcasecmp(Auth::user()->email, $invite->email) !== 0) {
                return view('invitations.invalid', [
                    'message' => "Invite ini buat {$invite->email}. Logout dulu, login pake email itu.",
                ]);
            }

            $this->attach($invite, Auth::user());

            return redirect('/');
        }

        $existingUser = User::where('email', $invite->email)->first();

        if ($existingUser) {
            return view('invitations.login-required', ['invite' => $invite]);
        }

        return view('invitations.accept', ['invite' => $invite]);
    }

    public function store(Request $request, string $token)
{
    if (Auth::check()) {
        return redirect()->route('invitations.accept', $token);
    }

    $invite = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invite->accepted_at || $invite->isExpired()) {
            abort(410);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $invite->email,
            'password' => Hash::make($data['password']),
        ]);

        if (Role::where('name', 'Vault-Member')->exists()) {
            $user->assignRole('Vault-Member');
        }


        $this->attach($invite, $user);

        Auth::login($user);

        return redirect('/');
    }

    protected function attach(TeamInvitation $invite, User $user): void
    {
        $team = $invite->team;

        if (! $team->users()->where('user_id', $user->id)->exists()) {
            $team->users()->attach($user);
        }

        $invite->update(['accepted_at' => now()]);
    }
}
