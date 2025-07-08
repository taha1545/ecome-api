<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{

    public function rules()
    {
        return [
            'phone' => 'required|string|max:20',
            'notes' => 'required|string|max:1000',
            'type' => 'nullable|string|in:personal,emergency,business',
            'is_primary' => 'sometimes|boolean',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Phone number is required.',
            'notes.required' => 'Notes are required.',
            'type.in' => 'Invalid contact type :personal,emergency,business ',
        ];
    }
}
