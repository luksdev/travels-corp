<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $authData = $this->authService->register($request->validated());

        $authResource = new AuthResource($authData);

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

        $authData = $this->authService->login($credentials);

        if (! $authData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new AuthResource($authData);
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
        $this->authService->logout();

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
        $authData = $this->authService->refresh();

        return new AuthResource($authData);
    }
}
