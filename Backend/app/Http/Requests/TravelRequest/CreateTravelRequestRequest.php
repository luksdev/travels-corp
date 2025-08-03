<?php

declare(strict_types = 1);

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Travel Request Form Request
 */
class CreateTravelRequestRequest extends FormRequest
{
    /**
     * Authorize the request
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\TravelRequest::class);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'destination'    => 'required|string|max:255',
            'departure_date' => 'required|date|after:today',
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
