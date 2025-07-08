<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
  
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'type' => $this->type,
            'is_primary' => (bool) $this->is_primary,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => $this->when($request->user() && $request->user()->role === 'admin', function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'profile_image' => $this->user->profile_image,
                ];
            }),
        ];
    }


    protected function getTypeLabel(string $type): string
    {
        return $this->resource::TYPES[$type] ?? $type;
    }
}
