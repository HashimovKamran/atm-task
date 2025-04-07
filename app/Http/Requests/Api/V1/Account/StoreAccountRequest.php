<?php

namespace App\Http\Requests\Api\V1\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_number' => 'required|string|unique:accounts,account_number|max:255',
            'balance' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'user_id' => 'nullable|integer|exists:users,id',
            'card_number' => 'nullable|string|digits:16|unique:accounts,card_number',
            'pin' => 'nullable|string|digits:4',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'balance' => 0.00,
            'currency' => config('atm.currency|AZN'),
        ]);
    }
}
