<?php
// routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    
   
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    
    Route::apiResource('tasks', TaskController::class);
    
    
    Route::get('/tasks-statistics', [TaskController::class, 'statistics']);
    
   
    Route::middleware('admin')->group(function () {
       
        Route::get('/admin/users-with-tasks', function () {
            return \App\Models\User::with('tasks')->get();
        });
        
       
        Route::get('/admin/all-logs', function () {
            return \App\Models\TaskLog::with(['user', 'task'])->latest()->paginate(50);
        });
    });
});