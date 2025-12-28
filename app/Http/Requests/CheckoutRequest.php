<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $isAuthenticated = auth()->check();

        $rules = [
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
        ];

        // For authenticated users, they can either select an existing address or create a new one
        if ($isAuthenticated) {
            $rules['address_id'] = ['nullable', 'exists:addresses,id'];
            $rules['address'] = 'required_without:address_id|string|max:500';
            $rules['full_name'] = 'nullable|string|max:255';
        } else {
            $rules['name'] = 'required|string|max:255';
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['full_name'] = 'nullable|string|max:255';
            $rules['address'] = 'required|string|max:500';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'The Address name is required',
            'email.unique' => 'This email already exists. Please login to continue with your checkout.',
            'phone.unique' => 'This phone number is already associated with one of your addresses.',
            'address_id.exists' => 'The selected address does not exist or does not belong to you.',
            'full_name.required_without' => 'Full name is required when not selecting an existing address.',
            'phone.required_without' => 'Phone number is required when not selecting an existing address.',
            'address.required_without' => 'Address is required when not selecting an existing address.',
        ];
    }
}
