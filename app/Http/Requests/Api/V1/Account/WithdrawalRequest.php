<?php

namespace App\Http\Requests\Api\V1\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class WithdrawalRequest extends FormRequest
{
    public function rules(): array
    {
        $minWithdrawal = (float) config('atm.min_withdrawal', 5);
        $maxWithdrawal = (float) config('atm.max_withdrawal_per_tx', 1000);
        $multipleOf = (float) config('atm.smallest_dispensable_unit', 5);

        return [
            'amount' => 'required|numeric|min:' . $minWithdrawal . '|max:' . $maxWithdrawal . '|multiple_of:' . $multipleOf,
        ];
    }

    public function messages(): array
    {
        $multipleOf = (float) config('atm.smallest_dispensable_unit', 5);
        return [
            'amount.required' => 'Çıxarılacaq məbləğ tələb olunur.',
            'amount.numeric' => 'Məbləğ rəqəm olmalıdır.',
            'amount.min' => 'Minimum çıxarış məbləği :min AZN təşkil edir.',
            'amount.max' => 'Maksimum çıxarış məbləği :max AZN təşkil edir.',
            'amount.multiple_of' => 'Məbləğ ' . $multipleOf . ' AZN-in misli olmalıdır.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ], 422));
    }
}
