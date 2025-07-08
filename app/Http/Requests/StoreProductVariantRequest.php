<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'size' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:200',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
        ];
    }
}
