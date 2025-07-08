<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'description' => 'sometimes|string',
            'brand' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',

            // Categories
            'categories' => 'sometimes|array',
            'categories.*' => 'sometimes|integer|exists:categories,id',

            // Tags
            'tags' => 'sometimes|array',
            'tags.*' => 'sometimes|integer|exists:tags,id',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'This product name already exists.',
            'price.numeric' => 'The price must be a number.',
            'discount_price.numeric' => 'The discount price must be a number.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
        ];
    }
} 