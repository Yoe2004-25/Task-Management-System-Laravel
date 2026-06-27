<?php


namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
    
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->validated());
        
        if (!$token) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }
    
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}