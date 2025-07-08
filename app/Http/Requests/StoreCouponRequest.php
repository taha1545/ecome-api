<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code',
            'value' => 'required|numeric|min:0',
            'max_usage' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Coupon code is required.',
            'code.unique' => 'This coupon code already exists.',
            'value.required' => 'Coupon value is required.',
            'max_usage.required' => 'Maximum usage is required.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }
} 