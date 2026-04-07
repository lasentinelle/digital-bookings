<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'targets' => ['nullable', 'array'],
            'targets.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'The monthly budget amount is required.',
            'amount.numeric' => 'The monthly budget must be a valid number.',
            'amount.min' => 'The monthly budget cannot be negative.',
            'targets.*.numeric' => 'Each salesperson target must be a valid number.',
            'targets.*.min' => 'Each salesperson target cannot be negative.',
        ];
    }
}
