<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Register;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;
use Spatie\Permission\Models\Role;

class CustomRegistration extends Register
{
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        $user = parent::handleRegistration($data);

        if (Role::where('name', 'Vault-Admin')->exists()) {
            $user->assignRole('Vault-Admin');
        }

        return $user;
    }
}
