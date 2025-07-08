<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Order $order)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $order->user_id;
    }


    public function create(User $user)
    {

        return true;
    }

    public function update(User $user)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user)
    {
        return $user->role === 'admin';
    }

    public function cancel(User $user, Order $order)
    {
        if ($user->role === 'admin') {
            return true;
        }

        $cancellableStatuses = ['pending', 'processing'];
        return $user->id === $order->user_id && in_array($order->status, $cancellableStatuses);
    }

    public function updateStatus(User $user)
    {
        return $user->role === 'admin';
    }
}
