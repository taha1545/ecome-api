<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOrderRequest extends FormRequest
{
   
    public function authorize()
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'shipping_address_id' => 'nullable|exists:addresses,id',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:pending,processing,confirmed,shipped,delivered',
        ];
    }


    public function messages()
    {
        return [
            'user_id.required' => 'A user ID is required',
            'user_id.exists' => 'The selected user does not exist',
            'items.required' => 'Order items are required',
            'items.array' => 'Order items must be an array',
            'items.min' => 'At least one order item is required',
            'items.*.product_id.required' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'One or more products do not exist',
            'items.*.product_variant_id.exists' => 'One or more product variants do not exist',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.integer' => 'Quantity must be an integer',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'coupon_code.exists' => 'The coupon code is invalid',
            'shipping_address_id.exists' => 'The shipping address does not exist',
        ];
    }
}
