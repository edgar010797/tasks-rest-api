<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->name('v1.')->group(function () {

	Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
	Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

	Route::middleware(['jwt'])->group(function () {
		Route::get('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
		Route::post('auth/update', [AuthController::class, 'updateUser'])->name('auth.update');

		Route::apiResource('tasks', TaskController::class);
	});
});
