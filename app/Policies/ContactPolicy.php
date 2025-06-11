<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Contact $contact)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $contact->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Contact $contact)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $contact->user_id;
    }

    public function delete(User $user, Contact $contact)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $contact->user_id;
    }

    public function setPrimary(User $user, Contact $contact)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $contact->user_id;
    }
}
