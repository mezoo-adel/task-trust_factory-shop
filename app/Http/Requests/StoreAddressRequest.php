<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'label' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone' => ['required', 'string', 'max:20'],
            'address' => 'required|string|max:500',
            'is_default' => 'boolean',
        ];
    }
}
