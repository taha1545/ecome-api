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
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'gateway_id' => $this->gateway_id,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'processed_at' => $this->processed_at ? $this->processed_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'order' => $this->whenLoaded('order', function() {
                return [
                    'id' => $this->order->id,
                    'status' => $this->order->status,
                    'subtotal' => (float) $this->order->subtotal,
                    'tax' => (float) $this->order->tax,
                    'shipping_cost' => (float) $this->order->shipping_cost,
                    'total' => (float) $this->order->total,
                    'created_at' => $this->order->created_at->format('Y-m-d H:i:s'),
                    'coupon' => $this->order->coupon && $this->order->coupon->id ? [
                        'id' => $this->order->coupon->id,
                        'code' => $this->order->coupon->code,
                        'value' => (float) $this->order->coupon->value,
                    ] : null,
                ];
            }),
            'gateway_response' => $this->when($request->user() && $request->user()->role === 'admin',
                json_decode($this->gateway_response, true))
        ];
    }
}
