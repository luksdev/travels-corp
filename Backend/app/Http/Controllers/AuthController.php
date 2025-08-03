<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => UserRole::USER,
        ]);

        $token = JWTAuth::fromUser($user);

        $authResource = new AuthResource([
            'message'      => 'User created successfully',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ]);

        return response()->json([
            'data' => $authResource->toArray(request()),
        ], Response::HTTP_CREATED);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): AuthResource | JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return new AuthResource([
            'message'      => 'Login successful',
            'user'         => auth()->user(),
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Get authenticated user
     */
    public function getUser(): UserResource
    {
        return new UserResource(auth()->user());
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ], Response::HTTP_OK);
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): AuthResource
    {
        return new AuthResource([
            'message'      => 'Token refreshed successfully',
            'user'         => auth()->user(),
            'access_token' => auth()->refresh(),
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ]);
    }
}
