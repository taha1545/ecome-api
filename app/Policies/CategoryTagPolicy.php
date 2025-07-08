<?php

namespace App\Policies;

use App\Models\Categorie;
use App\Models\Tag;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryTagPolicy
{
    use HandlesAuthorization;


    public function viewAnyCategories(?User $user)
    {
        return true;
    }


    public function viewAnyTags(?User $user)
    {
        return true;
    }


    public function viewProductsByCategory(?User $user, Categorie $category)
    {
        return true;
    }


    public function viewProductsByTag(?User $user, Tag $tag)
    {
        return true;
    }


    public function createCategory(User $user)
    {
        return $user->role === 'admin';
    }


    public function createTag(User $user)
    {
        return $user->role === 'admin';
    }


    public function deleteCategory(User $user)
    {
        return $user->role === 'admin';
    }


    public function deleteTag(User $user)
    {
        return $user->role === 'admin';
    }


    public function addTag(User $user)
    {
        return $user->role === 'admin';
    }


    public function addCategory(User $user)
    {
        return $user->role === 'admin';
    }
}
