<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dishes' => ['required', 'array', 'min:1'],
            'dishes.*.id' => ['required', 'exists:dishes,id'],
            'dishes.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
