<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'price_score' => ['nullable', 'integer', 'min:1', 'max:3'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'type_id' => ['nullable', 'exists:restaurant_types,id'],
            'owner_id' => ['required', 'exists:users,id'],
        ];
    }

    public function owner(): User
    {
        return (new User)
            ->where('id', $this->validated('owner_id'))
            ->firstOrFail();
    }
}
