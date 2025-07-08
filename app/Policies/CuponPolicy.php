<?php

namespace App\Policies;

use App\Models\Cupon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CuponPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user)
    {
        return $user->role === 'admin';
    }

    public function apply(User $user)
    {
        return true;
    }
}
