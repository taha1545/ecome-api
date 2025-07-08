<?php

namespace App\Filters;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderFilter
{
    protected $request;
    protected $query;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->filterByUser()
            ->filterByStatus()
            ->filterByDateRange()
            ->filterByPriceRange()
            ->filterByPaymentStatus()
            ->filterByHasItems()
            ->filterByItemCount()
            ->filterBySearch()
            ->applySorting();

        return $this->query;
    }

    protected function filterByUser(): self
    {
        if ($this->request->has('user_id')) {
            $this->query->where('user_id', $this->request->user_id);
        }

        $user = $this->request->user();
        if ($user && $user->role !== 'admin') {
            $this->query->where('user_id', $user->id);
        }

        return $this;
    }

    protected function filterByStatus(): self
    {
        if ($this->request->has('status')) {
            $status = $this->request->status;

            if (is_array($status)) {
                $this->query->whereIn('status', $status);
            } else {
                $this->query->where('status', $status);
            }
        }

        return $this;
    }

    protected function filterByDateRange(): self
    {
        if ($this->request->has('from_date')) {
            $this->query->whereDate('created_at', '>=', $this->request->from_date);
        }

        if ($this->request->has('to_date')) {
            $this->query->whereDate('created_at', '<=', $this->request->to_date);
        }

        return $this;
    }

    protected function filterByPriceRange(): self
    {
        if ($this->request->has('min_total')) {
            $this->query->where('total', '>=', $this->request->min_total);
        }

        if ($this->request->has('max_total')) {
            $this->query->where('total', '<=', $this->request->max_total);
        }

        return $this;
    }

    protected function filterByPaymentStatus(): self
    {
        if ($this->request->has('payment_status')) {
            $status = $this->request->payment_status;

            if (is_array($status)) {
                $this->query->whereIn('payment_status', $status);
            } else {
                $this->query->where('payment_status', $status);
            }
        }

        return $this;
    }


    protected function filterByHasItems(): self
    {
        if ($this->request->has('has_product')) {
            $productId = $this->request->has_product;
            $this->query->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            });
        }

        if ($this->request->has('has_variant')) {
            $variantId = $this->request->has_variant;
            $this->query->whereHas('items', function ($query) use ($variantId) {
                $query->where('variant_id', $variantId);
            });
        }

        return $this;
    }

    protected function filterByItemCount(): self
    {
        if ($this->request->has('min_items')) {
            $minItems = (int) $this->request->min_items;
            $this->query->has('items', '>=', $minItems);
        }

        if ($this->request->has('max_items')) {
            $maxItems = (int) $this->request->max_items;
            $this->query->has('items', '<=', $maxItems);
        }

        return $this;
    }

    protected function filterBySearch(): self
    {
        if ($this->request->has('search')) {
            $search = $this->request->search;
            $this->query->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $this;
    }

    protected function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'created_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');

        $allowedSortFields = [
            'id',
            'created_at',
            'updated_at',
            'status',
            'payment_status',
            'total',
            'subtotal',
            'payment_method'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $this->query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        if ($sortField === 'items_count') {
            $this->query->withCount('items')
                ->orderBy('items_count', $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $this;
    }
}
