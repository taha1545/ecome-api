<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreContactRequest extends FormRequest
{
  
    public function authorize()
    {
        return true;
    }

  
    public function rules()
    {
        return [
            'user_id' => 'sometimes|exists:users,id',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'notes' => 'nullable|string|max:1000',
            'type' => 'nullable|string|in:' . implode(',', array_keys(Contact::TYPES)),
            'is_primary' => 'sometimes|boolean',
        ];
    }

  
    public function messages()
    {
        return [
            'name.required' => 'The contact name is required',
            'phone.required' => 'The contact phone number is required',
            'email.email' => 'Please provide a valid email address',
            'type.in' => 'The selected contact type is invalid',
        ];
    }

    
    public function userAuthorized()
    {
        return Auth::user();
    }
}
