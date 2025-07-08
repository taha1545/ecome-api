<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'size' => 'sometimes|string|max:10',
            'color' => 'sometimes|string|max:50',
            'quantity' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'quantity.integer' => 'Quantity must be an integer.',
            'price.numeric' => 'Price must be a number.',
        ];
    }
} 