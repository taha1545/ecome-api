<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductFilter extends Filter
{


    public function apply(Builder $query): Builder
    {
        $this->query = $query;


        $this->filterByCategory()
            ->filterByTag()
            ->filterBySearch()
            ->filterByBrand()
            ->filterByPrice()
            ->filterByDiscount()
            ->filterByStock()
            ->filterByActive()
            ->filterByDate()
            ->applySorting();

        return $this->query;
    }


    protected function filterByCategory(): self
    {
        // Single category filter
        if ($this->request->has('category')) {
            $this->query->whereHas('categories', function ($q) {
                $q->where('categories.id', $this->request->category);
            });
        }

        // Multiple categories filter
        if ($this->request->has('categories')) {
            $categoryIds = is_array($this->request->categories)
                ? $this->request->categories
                : explode(',', $this->request->categories);

            $this->query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        return $this;
    }

    protected function filterByTag(): self
    {
        // Single tag filter
        if ($this->request->has('tag')) {
            $this->query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->request->tag);
            });
        }

        // Multiple tags filter
        if ($this->request->has('tags')) {
            $tagIds = is_array($this->request->tags)
                ? $this->request->tags
                : explode(',', $this->request->tags);

            $this->query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        return $this;
    }

    protected function filterBySearch(): self
    {
        if ($this->request->has('search')) {
            $searchTerm = $this->request->search;
            $this->query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('brand', 'like', "%{$searchTerm}%");
            });
        }

        return $this;
    }

    protected function filterByBrand(): self
    {
        if ($this->request->has('brand')) {
            $this->query->where('brand', 'like', "%{$this->request->brand}%");
        }

        return $this;
    }


    protected function filterByPrice(): self
    {
        if ($this->request->has('price_min')) {
            $this->query->where('price', '>=', $this->request->price_min);
        }

        if ($this->request->has('price_max')) {
            $this->query->where('price', '<=', $this->request->price_max);
        }

        return $this;
    }


    protected function filterByDiscount(): self
    {
        if ($this->request->has('has_discount') && $this->request->boolean('has_discount')) {
            $this->query->whereNotNull('discount_price');
        }

        return $this;
    }


    protected function filterByStock(): self
    {
        if ($this->request->has('in_stock') && $this->request->boolean('in_stock')) {
            $this->query->whereHas('variants', function ($q) {
                $q->where('quantity', '>', 0);
            });
        }

        return $this;
    }


    protected function filterByActive(): self
    {
        if ($this->request->has('active')) {
            $this->query->where('is_active', $this->request->boolean('active'));
        } else {
            $this->query->where('is_active', true);
        }

        return $this;
    }


    protected function filterByDate(): self
    {
        if ($this->request->has('created_after')) {
            $this->query->whereDate('created_at', '>=', $this->request->created_after);
        }

        if ($this->request->has('created_before')) {
            $this->query->whereDate('created_at', '<=', $this->request->created_before);
        }

        return $this;
    }


    protected function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'popularity');
        $sortDirection = $this->request->get('sort_direction', 'desc');
        $allowedSortFields = [
            'name',
            'price',
            'created_at',
            'views',
            'discount_price',
            'popularity',
            'discount_percentage',
            'average_rating',
            'orders_count',
            'in_stock'
        ];

        //
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc'])
            ? $sortDirection
            : 'desc';

        switch ($sortField) {
            case 'popularity':
                // 
                $this->query->orderByRaw(
                    "COALESCE(views / GREATEST(1, CURRENT_DATE - DATE(created_at)), 0) $sortDirection"
                );
                break;

            case 'discount_percentage':
                // 
                $this->query->orderByRaw(
                    "CASE 
                    WHEN price > 0 AND discount_price IS NOT NULL 
                    THEN ((price - discount_price)::float / price) * 100 
                    ELSE 0 
                END $sortDirection"
                );
                break;

            case 'average_rating':
                $this->query->withAvg('reviews as average_rating', 'rating')
                    ->orderBy('average_rating', $sortDirection);
                break;

            case 'orders_count':
                $this->query->withCount('orders as orders_count')
                    ->orderBy('orders_count', $sortDirection);
                break;

            case 'in_stock':
                $this->query->orderByRaw(
                    "CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM product_variants 
                        WHERE product_id = products.id 
                        AND quantity > 0
                    ) THEN 1 
                    ELSE 0 
                END $sortDirection"
                );
                break;

            default:
                if (in_array($sortField, $allowedSortFields)) {
                    $this->query->orderBy($sortField, $sortDirection);
                }
                break;
        }

        return $this;
    }
}
