<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardStatsController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'email_verified_at']);
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/stats', DashboardStatsController::class);

    Route::apiResource('tasks', TaskController::class);
});
