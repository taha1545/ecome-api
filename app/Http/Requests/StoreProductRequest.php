<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            //
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'required|string',
            'brand' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',

            // Categories
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',

            // Tags
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',

            // Variants
            'variants' => 'nullable|array',
            'variants.*.size' => 'nullable|string|max:10',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.description' => 'nullable|string|max:200',
            'variants.*.quantity' => 'required|integer|min:0',
            'variants.*.price' => 'required|numeric|min:0',

            // Files
            'files' => 'required|array',
            'files.*' => 'file|max:2048',
            'file_types' => 'nullable|array',
            'file_types.*' => 'string|in:image,document,video,3d_model,other',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'The product name is required.',
            'name.unique' => 'This product name already exists.',
            'description.required' => 'A description is required.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'discount_price.numeric' => 'The discount price must be a number.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
            'variants.*.quantity.required' => 'Each variant must have a quantity.',
            'variants.*.price.required' => 'Each variant must have a price.',
            'files.*.file' => 'Each file must be a valid file.',
            'files.*.mimes' => 'Files must be of type: jpg, jpeg, png, pdf, docx.',
            'files.*.max' => 'Each file must not exceed 2MB.',
            'file_types.*.in' => 'File type must be one of: image, document,video ,3d_model, other.',
        ];
    }
}
