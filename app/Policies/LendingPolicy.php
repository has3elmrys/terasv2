<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Lending;
use Illuminate\Auth\Access\HandlesAuthorization;

class LendingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lending');
    }

    public function view(AuthUser $authUser, Lending $lending): bool
    {
        return $authUser->can('View:Lending');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lending');
    }

    public function update(AuthUser $authUser, Lending $lending): bool
    {
        return $authUser->can('Update:Lending');
    }

    public function delete(AuthUser $authUser, Lending $lending): bool
    {
        return $authUser->can('Delete:Lending');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Lending');
    }

    public function restore(AuthUser $authUser, Lending $lending): bool
    {
        return $authUser->can('Restore:Lending');
    }

    public function forceDelete(AuthUser $authUser, Lending $lending): bool
    {
        return $authUser->can('ForceDelete:Lending');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lending');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lending');
    }

    public function replicate(AuthUser $authUser, Lending $lending): bool
    {
        return $authUser->can('Replicate:Lending');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lending');
    }

}