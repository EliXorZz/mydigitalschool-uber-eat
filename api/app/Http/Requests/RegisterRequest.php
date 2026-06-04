<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $emailErrors = $validator->errors()->get('email');

        foreach ($emailErrors as $error) {
            if (str_contains(strtolower($error), 'taken') || str_contains(strtolower($error), 'already')) {
                throw new HttpResponseException(
                    response()->json(['message' => 'Email already in use.'], 409)
                );
            }
        }

        parent::failedValidation($validator);
    }
}
