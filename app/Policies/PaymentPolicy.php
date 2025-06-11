<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Payment $payment)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $payment->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Payment $payment)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Payment $payment)
    {
        return $user->role === 'admin';
    }

    public function refund(User $user, Payment $payment)
    {
        return $user->role === 'admin';
    }

    public function updateStatus(User $user, Payment $payment)
    {
        return $user->role === 'admin';
    }
}
