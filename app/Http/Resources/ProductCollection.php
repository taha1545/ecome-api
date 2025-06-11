<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\FileResource;
use App\Http\Resources\VariantResource;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'is_active' => $product->is_active,
                    'views' => $product->views,
                    'average_rating' => round($product->reviews_avg_rating ?? 0, 1),
                    'reviews_count' => $product->reviews_count,
                    'categories' => CategoryResource::collection($product->categories),
                    'tags' => TagResource::collection($product->tags),
                    'files' => FileResource::collection($product->files),
                    'in_stock' => $product->variants->sum('quantity') > 0,
                ];
            }),
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'links' => [
                    'next' => $this->nextPageUrl(),
                    'prev' => $this->previousPageUrl(),
                ],
            ],
        ];
    }
}
