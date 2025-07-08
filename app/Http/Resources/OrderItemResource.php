<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'total' => (float) ($this->unit_price * $this->quantity - $this->discount_amount),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'description' => $this->product->description,
                    'brand' => $this->product->brand,
                    'price' => (float) $this->product->price,
                    'discount_price' => (float) $this->product->discount_price,
                    'image' => $this->product->files->first() ?  $this->product->files->first()->path : null,
                ];
            }),
            'variant' => $this->whenLoaded('variant', function () {
                return $this->variant ? [
                    'id' => $this->variant->id,
                    'size' => $this->variant->size,
                    'color' => $this->variant->color,
                    'price' => (float) $this->variant->price,
                    'description' => $this->variant->description,
                    'sku' => $this->variant->sku,
                ] : null;
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
