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

    public function view(User $user, Cupon $cupon)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Cupon $cupon)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Cupon $cupon)
    {
        return $user->role === 'admin';
    }

    public function apply(User $user, Cupon $cupon)
    {
        if (!$cupon->is_active) {
            return false;
        }

        if ($cupon->expires_at && $cupon->expires_at->isPast()) {
            return false;
        }

        if ($cupon->max_usage > 0 && $cupon->used_count >= $cupon->max_usage) {
            return false;
        }

        return true;
    }
}
