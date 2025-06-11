<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderCollectionResource extends JsonResource
{
   
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'profile_image' => $this->user->profile_image,
            ],
            'items_count' => $this->items_count ?? 0,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
