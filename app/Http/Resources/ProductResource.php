<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\FileResource;
use App\Http\Resources\VariantResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReviewResource;

class ProductResource extends JsonResource
{
 
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'is_active' => $this->is_active,
            'views' => $this->views,
            'average_rating' => round($this->reviews_avg_rating ?? 0, 1),
            'reviews_count' => $this->reviews_count,
            'categories' => CategoryResource::collection($this->categories),
            'tags' => TagResource::collection($this->tags),
            'files' => FileResource::collection($this->files),
            'variants' => VariantResource::collection($this->variants),
            'in_stock' => $this->variants->sum('quantity') > 0,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
