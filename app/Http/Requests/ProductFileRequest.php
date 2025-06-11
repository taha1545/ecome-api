<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductFileRequest extends FormRequest
{
  
    public function authorize()
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

   
    public function rules()
    {
        return [
            'file' => 'required|file|max:10240',
            'type' => 'required|string|in:image,document,3d_model',
        ];
    }

   
    public function messages()
    {
        return [
            'file.required' => 'A file is required',
            'file.file' => 'The uploaded file is invalid',
            'file.max' => 'The file size cannot exceed 10MB',
            'type.required' => 'The file type is required',
            'type.in' => 'The file type must be one of: image, document, 3d_model',
        ];
    }
}
