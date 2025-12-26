<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListWalletsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_name' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
        ];
    }
}
