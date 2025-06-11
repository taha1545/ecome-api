<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'value' => (float) $this->value,
            'max_usage' => $this->max_usage,
            'used_count' => $this->used_count,
            'remaining_usage' => $this->max_usage - $this->used_count,
            'expires_at' => $this->expires_at ? $this->expires_at->format('Y-m-d H:i:s') : null,
            'is_active' => (bool) $this->is_active,
            'is_expired' => $this->expires_at ? $this->expires_at->isPast() : false,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
