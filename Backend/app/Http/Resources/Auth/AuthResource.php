<?php

declare(strict_types = 1);

namespace App\Http\Resources\Auth;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Authentication Resource
 *
 * This resource transforms authentication response data including
 * user information and JWT token details for API responses.
 *
 * @package App\Http\Resources\Auth
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request
     * @return array<string, mixed> The transformed authentication data
     */
    public function toArray(Request $request): array
    {
        return [
            'success'      => true,
            'message'      => $this->resource['message'] ?? 'Operation successful',
            'user'         => new UserResource($this->resource['user']),
            'access_token' => $this->resource['access_token'],
            'token_type'   => $this->resource['token_type'] ?? 'bearer',
            'expires_in'   => $this->resource['expires_in'],
        ];
    }
}
