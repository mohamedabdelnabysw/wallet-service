<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_wallet_id' => 'required|uuid|exists:wallets,id',
            'target_wallet_id' => 'required|uuid|exists:wallets,id|different:source_wallet_id',
            'amount' => 'required|integer|min:1',
        ];
    }
}
