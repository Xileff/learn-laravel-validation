<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'email', 'max:100'],
            'password' => ['required', Password::min(6)->letters()->numbers()->symbols()]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'username' => strtolower($this->input('username'))
        ]);
    }

    protected function passedValidation()
    {
        $this->merge([
            'password' => bcrypt($this->input('password'))
        ]);
    }
}
