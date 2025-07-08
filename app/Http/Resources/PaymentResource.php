<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'profile_image' => $this->user->profile_image,
                'role' => $this->user->role,
                'address' => $this->user->addresse ? [
                    'id' => $this->user->addresse->id,
                    'address_line1' => $this->user->addresse->address_line1,
                    'address_line2' => $this->user->addresse->address_line2,
                    'city' => $this->user->addresse->city,
                    'postal_code' => $this->user->addresse->postal_code,
                    'phone' => $this->user->addresse->phone,
                ] : null,
            ],

            'desc' => $this->desc,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'order_number' => $this->order_number,
            'transaction_id' => $this->transaction_id,
            'recu_path' => $this->recu_path,
            'error_message' => $this->error_message,

            'processed_at' => optional($this->processed_at)->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id,
                    'status' => $this->order->status,
                    'subtotal' => (float) $this->order->subtotal,
                    'tax' => (float) $this->order->tax,
                    'shipping_cost' => (float) $this->order->shipping_cost,
                    'total' => (float) $this->order->total,
                    'created_at' => $this->order->created_at->format('Y-m-d H:i:s'),
                    'coupon' => optional($this->order->coupon, function ($coupon) {
                        return [
                            'id' => $coupon->id,
                            'code' => $coupon->code,
                            'value' => (float) $coupon->value,
                        ];
                    }),
                ];
            }),

            'gateway_response' => $this->when(
                $request->user() && $request->user()->role === 'admin',
                fn() => json_decode($this->gateway_response, true)
            ),
        ];
    }
}
