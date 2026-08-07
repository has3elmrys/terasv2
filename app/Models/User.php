<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password','has_email_authentication','app_authentication_secret','phone_number'])]
#[Hidden(['password', 'remember_token','app_authentication_secret'])]
// class User extends Authenticatable implements FilamentUser, HasTenants, MustVerifyEmail, HasEmailAuthentication
class User extends Authenticatable implements FilamentUser, HasTenants

{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_email_authentication' => 'boolean',
            'app_authentication_secret' => 'encrypted',
        ];
    }

     public function canAccessPanel(Panel $panel): bool
    {
        // Admin panel - only super_admin can access
        if ($panel->getId() === 'admin') {
            return $this->hasRole('super_admin');
        }
        // Team panel - super_admin can access (for login), or users with teams
        if ($panel->getId() === 'team') {
            // return $this->hasRole('super_admin') || $this->teams()->exists();
            return true;
        }
        // Deny access to any other panel
        return false;
}

    public function getTenants(Panel $panel): Collection
        {
            return $this->teams;
        }
        public function canAccessTenant(Model $tenant): bool
        {
            return $this->teams()->whereKey($tenant)->exists();
        }

        public function teams(): BelongsToMany
        {
                return $this->belongsToMany(Team::class);
        }

        public function hasEmailAuthentication(): bool
        {
            return $this->has_email_authentication;
        }

        public function toggleEmailAuthentication(bool $condition): void
        {
            $this->has_email_authentication =$condition;
            $this->save();
        }


}
