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
            'departure_date' => 'sometimes|required|date|after:today',
            'return_date'    => 'nullable|date|after:departure_date',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'destination.required'    => 'The destination field is required.',
            'destination.string'      => 'The destination must be a string.',
            'destination.max'         => 'The destination may not be greater than 255 characters.',
            'departure_date.required' => 'The departure date field is required.',
            'departure_date.date'     => 'The departure date must be a valid date.',
            'departure_date.after'    => 'The departure date must be after today.',
            'return_date.date'        => 'The return date must be a valid date.',
            'return_date.after'       => 'The return date must be after the departure date.',
        ];
    }
}
