<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'items' => 'sometimes|array',
            'items.*.product_id' => 'sometimes|exists:products,id',
            'items.*.product_variant_id' => 'sometimes|exists:product_variants,id',
            'items.*.quantity' => 'sometimes|integer|min:1',
            'coupon_code' => 'sometimes|string|exists:coupons,code',
        ];
    }

    public function messages()
    {
        return [
            'items.*.product_id.exists' => 'One or more selected products do not exist.',
            'items.*.quantity.integer' => 'Quantity must be an integer.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'coupon_code.exists' => 'The coupon code is invalid.',
        ];
    }
} 