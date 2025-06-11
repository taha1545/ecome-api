<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;


    public function viewAny(?User $user, Product $product)
    {
        if (!$product->is_active) {
            return $user && $user->role === 'admin';
        }
        
        return true;
    }

 
    public function reviewProduct(User $user, Product $product)
    {
        // 
        if (!$product->is_active) {
            return false;
        }
        
  
        return true;
    }
}
