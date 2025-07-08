<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240',
            'type' => 'nullable|string|in:image,video,3d_model,document',
        ];
    }
}
