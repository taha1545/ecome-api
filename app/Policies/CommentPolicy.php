<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
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
        //
        if (!$product->is_active) {
            return $user->role === 'admin';
        }
        
        return true;
    }

   
    public function delete(User $user, Comment $comment)
    {
        // 
        if ($user->role === 'admin') {
            return true;
        }
        
        //
        return $user->id === $comment->user_id;
    }
}
