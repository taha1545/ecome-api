<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
 
    public function authorize()
    {
        return true; 
    }

   
    public function rules()
    {
        return [
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'refund_data' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'amount.numeric' => 'Refund amount must be a number',
            'amount.min' => 'Refund amount must be at least 0.01',
            'reason.max' => 'Refund reason cannot exceed 255 characters',
        ];
    }
}
