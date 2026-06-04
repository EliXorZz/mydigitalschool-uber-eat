<?php

namespace App\Http\Requests;

use App\States\OrderState;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\ModelStates\Validation\ValidStateRule;

class ListOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', new ValidStateRule(OrderState::class)],

            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],

            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
