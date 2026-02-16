<?php

use App\Http\Api\Auth\AuthController;
use App\Http\Api\Dashboard\DashboardStatsController;
use App\Http\Api\Tasks\TaskController;
use App\Http\Api\Users\StaffController;
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

    Route::get('/my-tasks', [TaskController::class, 'myTasks']);
    Route::get('/supervisor/tasks', [TaskController::class, 'supervisorTasks']);
    Route::get('/users/staff', StaffController::class);

    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::apiResource('tasks', TaskController::class);
});
