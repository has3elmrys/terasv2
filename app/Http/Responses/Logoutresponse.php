<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Contract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LogoutResponse implements Contract

{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->to(Filament::getPanel('team')->getLoginUrl());
    }
}



