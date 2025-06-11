<?php

namespace App\Policies;

use App\Models\ProductFile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductFilePolicy
{
    use HandlesAuthorization;

 
    public function viewAny(?User $user, Product $product)
    {
        //
        if (!$product->is_active) {
            return $user && $user->role === 'admin';
        }
        
        return true;
    }

   
    public function create(User $user, Product $product)
    {
        return $user->role === 'admin';
    }


    public function delete(User $user, ProductFile $file)
    {
        return $user->role === 'admin';
    }
}
