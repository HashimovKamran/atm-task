<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AtmLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'card_number' => 'required|string|digits:16',
            'pin' => 'required|string|digits:4',
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.required' => 'Kart nömrəsi tələb olunur.',
            'card_number.digits' => 'Kart nömrəsi :digits rəqəmdən ibarət olmalıdır.',
            'pin.required' => 'PİN kodu tələb olunur.',
            'pin.digits' => 'PİN kodu :digits rəqəmdən ibarət olmalıdır.',
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
