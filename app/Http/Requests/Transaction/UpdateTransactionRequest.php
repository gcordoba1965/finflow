<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('transaction'));
    }

    public function rules(): array
    {
        return [
            'type'         => 'sometimes|in:expense,income',
            'category'     => 'sometimes|in:needs,wants,savings,income',
            'description'  => 'sometimes|string|max:255',
            'amount'       => 'sometimes|numeric|min:0.01|max:999999.99',
            'date'         => 'sometimes|date|before_or_equal:today',
            'sub_category' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:1000',
        ];
    }
}
