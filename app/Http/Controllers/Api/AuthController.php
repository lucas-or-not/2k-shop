<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\GetCurrentUserAction;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        try {
            $user = $action->execute($request->validated());

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $user->createToken('auth-token')->plainTextToken,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        try {
            $result = $action->execute($request->validated());

            return response()->json([
                'message' => 'Login successful',
                'user' => $result['user']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        try {
            $action->execute();

            return response()->json([
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function user(Request $request, GetCurrentUserAction $action): JsonResponse
    {
        $user = $action->execute();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }

        return response()->json([
            'user' => $user,
        ]);
    }
}
