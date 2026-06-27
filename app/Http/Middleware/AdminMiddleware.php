<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
        if (!Auth::check()) {
           
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please login first.',
                    'status' => 401
                ], 401);
            }
            
           
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

   
        if (!Auth::user()->isAdmin()) {
          
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden. Admin access required.',
                    'status' => 403
                ], 403);
            }
            
           
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}