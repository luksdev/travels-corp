<?php

declare(strict_types = 1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * User Login Request
 *
 * This form request handles validation for user login.
 * It validates the required credentials for user authentication.
 *
 * @package App\Http\Requests\Auth
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true as login is open to all
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
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'O campo email é obrigatório.',
            'email.email'       => 'O email deve ser um endereço de email válido.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string'   => 'A senha deve ser uma string.',
        ];
    }
}
