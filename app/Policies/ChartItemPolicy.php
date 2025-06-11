<?php

namespace App\Policies;

use App\Models\ChartItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChartItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ChartItem $chartItem)
    {
        return $user->role === 'admin';
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, ChartItem $chartItem)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, ChartItem $chartItem)
    {
        return $user->role === 'admin';
    }
}
