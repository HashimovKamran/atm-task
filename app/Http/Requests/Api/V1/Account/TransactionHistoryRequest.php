<?php

namespace App\Http\Requests\Api\V1\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TransactionType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransactionHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        $validTypes = array_column(TransactionType::cases(), 'value');

        return [
            'index' => 'sometimes|integer|min:0',
            'size' => 'sometimes|integer|min:1|max:100',
            'from' => 'sometimes|integer|min:0|lte:index',
            'type' => ['sometimes', 'integer', Rule::in($validTypes)],
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }

    protected function prepareForValidation()
    {
        $this->mergeIfMissing([
            'index' => 0,
            'size' => 15,
            'from' => 0
        ]);
    }

    public function messages(): array
    {
        return [
            'index.integer' => 'Səhifə indeksi tam ədəd olmalıdır.',
            'index.min' => 'Səhifə indeksi 0-dan kiçik ola bilməz.',
            'size.integer' => 'Səhifə ölçüsü tam ədəd olmalıdır.',
            'size.min' => 'Səhifə ölçüsü minimum 1 olmalıdır.',
            'size.max' => 'Səhifə ölçüsü maksimum 100 ola bilər.',
            'from.integer' => '"From" parametri tam ədəd olmalıdır.',
            'from.min' => '"From" parametri 0-dan kiçik ola bilməz.',
            'from.lte' => '"From" parametri "index" parametrindən böyük ola bilməz.',
            'type.integer' => 'Tranzaksiya növü etibarlı deyil.',
            'type.in' => 'Seçilmiş tranzaksiya növü etibarlı deyil.',
            'start_date.date_format' => 'Başlanğıc tarix formatı YYYY-MM-DD olmalıdır.',
            'end_date.date_format' => 'Son tarix formatı YYYY-MM-DD olmalıdır.',
            'end_date.after_or_equal' => 'Son tarix başlanğıc tarixdən əvvəl ola bilməz.',
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
