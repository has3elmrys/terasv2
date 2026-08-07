<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

class AuthenticateAdmin extends FilamentAuthenticate
{
    protected function redirectTo($request): ?string
    {
        return Filament::getPanel('team')->getLoginUrl();
    }
}
