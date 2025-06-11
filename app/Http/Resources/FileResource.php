<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
   
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'path' => $this->path,
            'type' => $this->type,
            'url' => url('storage/' . $this->path),
        ];
    }
}
