<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint with rate limiting
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,mp4,mov,avi,wmv',
            'folder' => 'nullable|string|max:255',
            'uploadable_id' => 'nullable|integer',
            'uploadable_type' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'Only images (jpg, jpeg, png, webp, gif), videos (mp4, mov, avi, wmv), and documents (pdf, doc, docx) are allowed.',
        ];
    }
}
