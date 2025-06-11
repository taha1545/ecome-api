<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SavedProductPolicy
{
    use HandlesAuthorization;

 
    public function viewSaved(User $user)
    {
        return true; 
    }

 
    public function toggleSave(User $user, Product $product)
    {
        //
        if (!$product->is_active) {
            return $user->role === 'admin';
        }
        
        return true;
    }

    
    public function checkSaved(User $user, Product $product)
    {
        return true;
    }
}
