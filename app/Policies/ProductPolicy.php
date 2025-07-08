<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user)
    {
        return true; 
    }

 
    public function view(?User $user, Product $product)
    {
        // 
        if (!$product->is_active) {
            return $user && $user->role === 'admin';
        }
        
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

  
    public function search(?User $user)
    {
        return true; 
    }
}
