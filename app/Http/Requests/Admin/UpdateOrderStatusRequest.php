<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $statuses = array_map(fn($status) => $status->value, OrderStatusEnum::cases());

        return [
            'status' => ['required', 'string', Rule::in($statuses)],
        ];
    }
}
