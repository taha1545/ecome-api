<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|string|max:50|in:cash,satim',
        ];
    }

    public function messages()
    {
        return [
            'order_id.required' => 'Order is required.',
            'order_id.exists' => 'The selected order does not exist.',
            'method.required' => 'Payment method is required.',
        ];
    }
} 