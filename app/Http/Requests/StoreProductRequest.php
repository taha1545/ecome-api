<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{


    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            // Basic product information
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'required|string',
            'brand' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'is_active' => 'boolean',

            // Categories and tags
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

            // Variants
            'variants' => 'nullable|array',
            'variants.*.size' => 'nullable|string|max:10',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.quantity' => 'required|integer|min:0',
            'variants.*.price' => 'required|numeric|min:0',

            // Files
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,xls,xlsx,zip|max:10240',
            'file_types' => 'required_with:files|array',
            'file_types.*' => 'required|string|in:image,document,3d_model,other',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'description.required' => 'The product description is required.',
            'brand.required' => 'The product brand is required.',
            'price.required' => 'The product price is required.',
            'price.numeric' => 'The product price must be a number.',
            'price.min' => 'The product price must be at least 0.',
            'discount_price.lt' => 'The discount price must be less than the regular price.',
            'variants.*.quantity.required' => 'The variant quantity is required.',
            'variants.*.price.required' => 'The variant price is required.',
            'files.*.mimes' => 'The file must be a valid type: jpeg, png, jpg, gif, webp, pdf, doc, docx, xls, xlsx, zip.',
            'files.*.max' => 'The file size must not exceed 10MB.',
            'file_types.required_with' => 'File types are required when uploading files.',
            'file_types.size' => 'The number of file types must match the number of files.',
            'file_types.*.in' => 'The file type must be one of: image, document, 3d_model, other.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422));
    }
}
