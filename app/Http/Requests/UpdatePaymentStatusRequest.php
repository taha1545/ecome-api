<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'status' => 'sometimes|string|in:pending,processing,completed,failed,refunded',
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'Invalid payment status.',
        ];
    }
} 