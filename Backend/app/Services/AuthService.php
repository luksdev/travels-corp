<?php

declare(strict_types = 1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Authentication Service
 */
class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => UserRole::USER,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'message'      => 'User created successfully',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ];
    }

    /**
     * Login user
     */
    public function login(array $credentials): array | null
    {
        if (! $token = auth()->attempt($credentials)) {
            return null;
        }

        return [
            'message'      => 'Login successful',
            'user'         => auth()->user(),
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ];
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): array
    {
        return [
            'message'      => 'Token refreshed successfully',
            'user'         => auth()->user(),
            'access_token' => auth()->refresh(),
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ];
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        auth()->logout();
    }
}
