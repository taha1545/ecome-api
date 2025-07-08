<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'size' => $this->size,
            'color' => $this->color,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'in_stock' => $this->quantity > 0,
            'description' => $this->description,
        ];
    }
}
