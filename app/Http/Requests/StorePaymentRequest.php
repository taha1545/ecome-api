<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePaymentRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'method' => 'required|string|in:credit_card,paypal,bank_transfer,stripe,other',
            'status' => 'required|string|in:pending,processing,succeeded,failed',
            'transaction_id' => 'nullable|string',
            'gateway_id' => 'nullable|string',
            'gateway_response' => 'nullable',
            'error_code' => 'nullable|string',
            'error_message' => 'nullable|string',
            'payment_data' => 'nullable|array',
            'payment_data.card_token' => 'required_if:method,credit_card,stripe',
            'payment_data.payer_id' => 'required_if:method,paypal',
        ];
    }

    public function messages()
    {
        return [
            'order_id.required' => 'An order ID is required',
            'order_id.exists' => 'The selected order does not exist',
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Payment amount must be a number',
            'amount.min' => 'Payment amount must be at least 0.01',
            'currency.required' => 'Currency code is required',
            'currency.size' => 'Currency code must be 3 characters (e.g., USD)',
            'method.required' => 'Payment method is required',
            'method.in' => 'Invalid payment method selected',
            'payment_data.card_token.required_if' => 'Card token is required for credit card payments',
            'payment_data.payer_id.required_if' => 'Payer ID is required for PayPal payments',
        ];
    }


    public function userAuthorized()
    {
        return Auth::user();
    }
}
