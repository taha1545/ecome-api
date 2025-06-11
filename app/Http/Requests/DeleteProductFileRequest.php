<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteProductFileRequest extends FormRequest
{
   
    public function authorize()
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

   
    public function rules()
    {
        return [
           
        ];
    }
}
