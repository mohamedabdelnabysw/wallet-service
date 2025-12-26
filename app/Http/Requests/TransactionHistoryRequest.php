<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TransactionType;
use Illuminate\Validation\Rules\Enum;

class TransactionHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', new Enum(TransactionType::class)],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
    }
}
