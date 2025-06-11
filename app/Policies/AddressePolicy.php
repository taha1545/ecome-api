<?php

namespace App\Policies;

use App\Models\Addresse;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Addresse $addresse)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $addresse->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Addresse $addresse)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $addresse->user_id;
    }

    public function delete(User $user, Addresse $addresse)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $addresse->user_id;
    }
}
