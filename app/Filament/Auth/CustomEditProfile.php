<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CustomEditProfile extends EditProfile
{
    /**
     * Override the form to exclude the email field entirely.
     */
    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             $this->getNameFormComponent(),
    //             // Email field removed - users cannot change email from profile
    //             $this->getPasswordFormComponent(),
    //             $this->getPasswordConfirmationFormComponent(),
    //             $this->getCurrentPasswordFormComponent(),
    //         ]);
    // }

    /**
     * Override the current password component to only require it when changing password.
     */
    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
            ->belowContent(__('filament-panels::auth/pages/edit-profile.form.current_password.below_content'))
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            // Only require current password when changing password
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }

    /**
     * Redirect to dashboard after successful save.
     */
    protected function getRedirectUrl(): ?string
    {
        // Super admins go to /admin, others go back to current panel
        if (auth()->user()?->hasRole('super_admin')) {
            return '/admin';
        }

        return filament()->getUrl();
    }
}
