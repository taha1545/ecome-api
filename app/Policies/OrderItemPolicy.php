<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, OrderItem $orderItem)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $orderItem->order->user_id;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, OrderItem $orderItem)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, OrderItem $orderItem)
    {
        return $user->role === 'admin';
    }
}
