<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $model)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $model->id;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, User $model)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        if ($user->role === 'admin' && $user->id !== $model->id) {
            return true;
        }

        return false;
    }

    public function updatePassword(User $user, User $model)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $model->id;
    }

    public function updateProfileImage(User $user, User $model)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $model->id;
    }
}
