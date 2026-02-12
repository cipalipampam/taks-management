<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::view('/notifications', 'pages.notifications')->name('notifications');
    Route::view('/your-tasks', 'pages.your-tasks')->name('your-tasks');

    Route::middleware('can:tasks.manage.staff')->group(function () {
        Route::view('/supervisor/tasks', 'pages.supervisor-tasks')->name('supervisor.tasks');
    });
});

require __DIR__.'/settings.php';
