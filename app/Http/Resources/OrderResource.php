<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'discount_amount' => (float) $item->discount_amount,
                        'total' => (float) ($item->unit_price * $item->quantity - $item->discount_amount),
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => (float) $item->product->price,
                            'discount_price' => (float) $item->product->discount_price,
                            'brand' => $item->product->brand,
                            'image' => $item->product->files->first() ? $item->product->files->first()->path : null,
                        ] : null,
                        'variant' => $item->variant ? [
                            'id' => $item->variant->id,
                            'size' => $item->variant->size,
                            'color' => $item->variant->color,
                            'price' => (float) $item->variant->price,
                        ] : null,
                    ];
                });
            }),
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
                    'gateway_id' => $payment->gateway_id,
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
