<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'message' => 'required|string|min:2|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'message.required' => 'Comment message is required.',
            'message.min' => 'Comment is too short.',
            'message.max' => 'Comment is too long.',
        ];
    }
} 