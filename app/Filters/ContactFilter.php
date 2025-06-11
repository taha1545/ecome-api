<?php

namespace App\Filters;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class ContactFilter extends Filter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->filterByUser()
             ->filterByType()
             ->filterByPrimary()
             ->filterBySearch()
             ->applySorting();

        return $this->query;
    }

    protected function filterByUser(): self
    {
        $user = $this->request->user();
        
        // 
        if ($user && $user->role !== 'admin') {
            $this->query->where('user_id', $user->id);
        } elseif ($this->request->has('user_id')) {
            $this->query->where('user_id', $this->request->user_id);
        }

        return $this;
    }

    protected function filterByType(): self
    {
        if ($this->request->has('type')) {
            $type = $this->request->type;
            //
            if (is_array($type)) {
                $this->query->whereIn('type', $type);
            } else {
                $this->query->where('type', $type);
            }
        }

        return $this;
    }

    protected function filterByPrimary(): self
    {
        if ($this->request->has('is_primary')) {
            $isPrimary = filter_var($this->request->is_primary, FILTER_VALIDATE_BOOLEAN);
            $this->query->where('is_primary', $isPrimary);
        }

        return $this;
    }

    protected function filterBySearch(): self
    {
        if ($this->request->has('search')) {
            $search = $this->request->search;
            $this->query->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $this;
    }

    protected function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'created_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');

        $allowedSortFields = [
            'id', 'name', 'email', 'phone', 'type', 'is_primary', 'created_at'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $this->query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $this;
    }
}
