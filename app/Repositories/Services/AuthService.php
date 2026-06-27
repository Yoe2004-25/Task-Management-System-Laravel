<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected UserRepositoryInterface $userRepository;
    
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    public function register(array $data): object
    {
        return $this->userRepository->createUser($data);
    }
    
    public function login(array $credentials): ?string
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }
        
        $user = Auth::user();
        return $user->createToken('auth_token')->plainTextToken;
    }
    
    public function logout(): void
    {
        Auth::user()->currentAccessToken()->delete();
    }
}