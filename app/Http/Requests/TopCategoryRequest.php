<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'nullable|date|date_format:Y-m-d',
        ];
    }
    public function messages(): array
    {
        return [
            'date.date' => 'Date format is incorrect',
            'date.date_format' => 'Date format is yyyy-mm-dd for example 2025-08-12',
        ];
    }
}
