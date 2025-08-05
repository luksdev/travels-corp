<?php

declare(strict_types = 1);

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Travel Request Form Request
 */
class UpdateTravelRequestRequest extends FormRequest
{
    /**
     * Authorize the request
     */
    public function authorize(): bool
    {
        $travelRequest = $this->route('travel_request');

        return $this->user()->can('update', $travelRequest);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'destination'    => 'sometimes|required|string|max:255',
            'departure_date' => 'sometimes|required|date|after_or_equal:today',
            'return_date'    => 'nullable|date|after:departure_date',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'destination.required'          => 'O campo destino é obrigatório.',
            'destination.string'            => 'O destino deve ser uma string.',
            'destination.max'               => 'O destino não pode ter mais de 255 caracteres.',
            'departure_date.required'       => 'O campo data de partida é obrigatório.',
            'departure_date.date'           => 'A data de partida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de partida deve ser hoje ou posterior.',
            'return_date.date'              => 'A data de retorno deve ser uma data válida.',
            'return_date.after'             => 'A data de retorno deve ser posterior à data de partida.',
        ];
    }
}
