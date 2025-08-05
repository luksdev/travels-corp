<?php

declare(strict_types = 1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * User Registration Request
 *
 * This form request handles validation for user registration.
 * It validates the required fields for creating a new user account.
 *
 * @package App\Http\Requests\Auth
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true as registration is open to all
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
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
            'name.required'      => 'O campo nome é obrigatório.',
            'name.string'        => 'O nome deve ser uma string.',
            'name.max'           => 'O nome não pode ter mais de 255 caracteres.',
            'email.required'     => 'O campo email é obrigatório.',
            'email.string'       => 'O email deve ser uma string.',
            'email.email'        => 'O email deve ser um endereço de email válido.',
            'email.max'          => 'O email não pode ter mais de 255 caracteres.',
            'email.unique'       => 'Este email já está sendo usado.',
            'password.required'  => 'O campo senha é obrigatório.',
            'password.string'    => 'A senha deve ser uma string.',
            'password.min'       => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ];
    }
}
