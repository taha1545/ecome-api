<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource;

class OrderResource extends JsonResource
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
                'address' => $this->whenLoaded('user', function () {
                    return $this->user->addresse ? [
                        'id' => $this->user->addresse->id,
                        'address_line1' => $this->user->addresse->address_line1,
                        'address_line2' => $this->user->addresse->address_line2,
                        'city' => $this->user->addresse->city,
                        'postal_code' => $this->user->addresse->postal_code,
                        'phone' => $this->user->addresse->phone,
                    ] : null;
                }),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'coupon' => $this->whenLoaded('coupon', function () {
                return $this->coupon->id ? [
                    'id' => $this->coupon->id,
                    'code' => $this->coupon->code,
                    'value' => (float) $this->coupon->value,
                ] : null;
            }),
            'payment' => $this->whenLoaded('payments', function () {
                $payment = $this->payments->first();
                return $payment ? [
                    'id' => $payment->id,
                    'method' => $payment->method,
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'processed_at' => $payment->processed_at ? $payment->processed_at->format('Y-m-d H:i:s') : null,
                ] : null;
            }),
            'financial_details' => [
                'subtotal' => (float) $this->subtotal,
                'tax' => (float) $this->tax,
                'shipping_cost' => (float) $this->shipping_cost,
                'discount' => $this->coupon && $this->coupon->id ? (float) $this->coupon->value : 0,
                'total' => (float) $this->total,
            ],
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
