<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;     
use Illuminate\Support\Facades\Cache;    
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
       
        $validated = $request->validate([
            'name' => 'required|string|max:255',         
            'email' => 'required|string|email|max:255|unique:users',  
            'password' => 'required|string|min:8|confirmed', 
        ]);

       
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),  
            'role' => 'user' 
        ]);

       
        $token = $user->createToken('auth_token')->plainTextToken;
       
        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);  
    }

   
    public function login(Request $request)
    {
       
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

       
        $user = User::where('email', $request->email)->first();

        
        if (!$user || !Hash::check($request->password, $user->password)) {
           
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials. Please check your email and password.'],
            ]);
        }

        
        $lastLogin = Cache::remember("user_{$user->id}_last_login", 3600, function () use ($user) {
            return $user->updated_at ?? now()->toDateTimeString();
        });

        
        $token = $user->createToken('auth_token')->plainTextToken;

        
        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => $token,
            'token_type' => 'Bearer',
            'last_login' => $lastLogin
        ]);
    }

   
    public function logout(Request $request)
    {
    
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

   
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}