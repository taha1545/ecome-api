<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function rules()
    {
        
        return [
            'value' => 'sometimes|numeric|min:0',
            'max_usage' => "sometimes|integer",
            'expires_at' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'max_usage.min' => 'Maximum usage cannot be less than the number of times already used.',
        ];
    }
} 