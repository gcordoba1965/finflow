<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'         => 'required|in:expense,income',
            'category'     => 'required|in:needs,wants,savings,income',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01|max:999999.99',
            'date'         => 'required|date|before_or_equal:today',
            'sub_category' => 'nullable|string|max:100',
            'icon'         => 'nullable|string|max:10',
            'notes'        => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'Selecciona el tipo de movimiento.',
            'category.required'    => 'Selecciona una categoría.',
            'description.required' => 'Ingresa una descripción.',
            'amount.required'      => 'Ingresa el monto.',
            'amount.min'           => 'El monto debe ser mayor a cero.',
            'date.required'        => 'Selecciona la fecha.',
            'date.before_or_equal' => 'La fecha no puede ser en el futuro.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('type') === 'income') {
            $this->merge(['category' => 'income']);
        }
        if (! $this->has('icon')) {
            $icons = ['needs' => '🏠', 'wants' => '🎉', 'savings' => '💰', 'income' => '💵'];
            $this->merge(['icon' => $icons[$this->input('category')] ?? '💳']);
        }
    }
}
