<?php

namespace App\Policies;

use App\Models\Chart;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChartPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Chart $chart)
    {
        return $user->role === 'admin';
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Chart $chart)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Chart $chart)
    {
        return $user->role === 'admin';
    }
}
