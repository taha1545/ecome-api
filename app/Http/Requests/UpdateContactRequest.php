<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'phone' => 'sometimes|string|max:20',
            'notes' => 'sometimes|string|max:1000',
            'type' => 'sometimes|string|in:personal,emergency,business',
            'is_primary' => 'sometimes|boolean',
            'user_id' => 'sometimes|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'type.in' => 'Invalid contact type.',
        ];
    }
} 