<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentFilter extends Filter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->filterByUser()
            ->filterByOrder()
            ->filterByStatus()
            ->filterByDateRange()
            ->filterByAmountRange()
            ->filterByTransactionId()
            ->filterByOrderNumber()
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

    protected function filterByOrder(): self
    {
        if ($this->request->has('order_id')) {
            $this->query->where('order_id', $this->request->order_id);
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

        if ($this->request->has('processed_from')) {
            $this->query->whereDate('processed_at', '>=', $this->request->processed_from);
        }

        if ($this->request->has('processed_to')) {
            $this->query->whereDate('processed_at', '<=', $this->request->processed_to);
        }

        return $this;
    }

    protected function filterByAmountRange(): self
    {
        if ($this->request->has('min_amount')) {
            $this->query->where('amount', '>=', $this->request->min_amount);
        }

        if ($this->request->has('max_amount')) {
            $this->query->where('amount', '<=', $this->request->max_amount);
        }

        return $this;
    }

    protected function filterByTransactionId(): self
    {
        if ($this->request->has('transaction_id')) {
            $this->query->where('transaction_id',   $this->request->transaction_id);
        }
        return $this;
    }

    protected function filterByOrderNumber(): self
    {
        if ($this->request->has('order_number')) {
            $this->query->where('order_number',   $this->request->transaction_id);
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
            'processed_at',
            'status',
            'amount',
            'method',
            'order_id'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $this->query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $this;
    }
}
