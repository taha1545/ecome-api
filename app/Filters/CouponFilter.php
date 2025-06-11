<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponFilter extends Filter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->filterByCode()
             ->filterByActive()
             ->filterByExpired()
             ->filterByValue()
             ->filterByUsage()
             ->filterByDateRange()
             ->applySorting();

        return $this->query;
    }

    protected function filterByCode(): self
    {
        if ($this->request->has('code')) {
            $this->query->where('code', 'like', "%{$this->request->code}%");
        }

        return $this;
    }

    protected function filterByActive(): self
    {
        if ($this->request->has('active')) {
            $this->query->where('is_active', $this->request->boolean('active'));
        }

        return $this;
    }

    protected function filterByExpired(): self
    {
        if ($this->request->has('expired')) {
            if ($this->request->boolean('expired')) {
                $this->query->where(function($query) {
                    $query->whereNotNull('expires_at')
                          ->where('expires_at', '<', now());
                });
            } else {
                $this->query->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                });
            }
        }

        return $this;
    }

    protected function filterByValue(): self
    {
        if ($this->request->has('min_value')) {
            $this->query->where('value', '>=', $this->request->min_value);
        }

        if ($this->request->has('max_value')) {
            $this->query->where('value', '<=', $this->request->max_value);
        }

        return $this;
    }

    protected function filterByUsage(): self
    {
        if ($this->request->has('available')) {
            if ($this->request->boolean('available')) {
                $this->query->where(function($query) {
                    $query->whereRaw('used_count < max_usage')
                          ->orWhereNull('max_usage');
                });
            } else {
                $this->query->whereNotNull('max_usage')
                            ->whereRaw('used_count >= max_usage');
            }
        }

        return $this;
    }

    protected function filterByDateRange(): self
    {
        if ($this->request->has('created_after')) {
            $this->query->whereDate('created_at', '>=', $this->request->created_after);
        }

        if ($this->request->has('created_before')) {
            $this->query->whereDate('created_at', '<=', $this->request->created_before);
        }

        if ($this->request->has('expires_after')) {
            $this->query->whereDate('expires_at', '>=', $this->request->expires_after);
        }

        if ($this->request->has('expires_before')) {
            $this->query->whereDate('expires_at', '<=', $this->request->expires_before);
        }

        return $this;
    }

    protected function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'created_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');
        $allowedSortFields = ['code', 'value', 'created_at', 'expires_at', 'used_count', 'max_usage'];

        if (in_array($sortField, $allowedSortFields)) {
            $this->query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $this;
    }
}
