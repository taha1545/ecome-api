<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentStatusRequest extends FormRequest
{
    
    public function authorize()
    {
        return true; 
    }

    
    public function rules()
    {
        return [
            'status' => 'required|string|in:pending,processing,succeeded,failed,refunded,requires_action',
            'transaction_id' => 'nullable|string',
            'gateway_id' => 'nullable|string',
            'error_code' => 'nullable|string',
            'error_message' => 'nullable|string',
            'processed_at' => 'nullable|date',
            'gateway_response' => 'nullable|json',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Payment status is required',
            'status.in' => 'Invalid payment status. Must be one of: pending, processing, succeeded, failed, refunded, requires_action',
            'processed_at.date' => 'Processed date must be a valid date',
            'gateway_response.json' => 'Gateway response must be valid JSON',
        ];
    }
}
