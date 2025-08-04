<?php

declare(strict_types = 1);

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Travel Request Status Form Request
 */
class UpdateStatusRequest extends FormRequest
{
    /**
     * Authorize the request
     */
    public function authorize(): bool
    {
        $travelRequest = $this->route('travel_request');

        return $this->user()->can('changeStatus', $travelRequest);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['approved', 'cancelled']),
            ],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required.',
            'status.string'   => 'The status must be a string.',
            'status.in'       => 'The status must be either approved or cancelled.',
        ];
    }
}
