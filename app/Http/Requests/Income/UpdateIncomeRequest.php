<?php

namespace App\Http\Requests\Income;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('incomeSource'));
    }

    public function rules(): array
    {
        return [
            'name'      => 'sometimes|string|max:100',
            'icon'      => 'nullable|string|max:10',
            'amount'    => 'sometimes|numeric|min:0.01',
            'frequency' => 'sometimes|in:monthly,biweekly,weekly,annual,variable',
            'is_active' => 'boolean',
            'notes'     => 'nullable|string|max:500',
        ];
    }
}
