<?php

namespace App\Http\Requests\Income;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:100',
            'icon'      => 'nullable|string|max:10',
            'amount'    => 'required|numeric|min:0.01',
            'frequency' => 'required|in:monthly,biweekly,weekly,annual,variable',
            'notes'     => 'nullable|string|max:500',
        ];
    }
}
