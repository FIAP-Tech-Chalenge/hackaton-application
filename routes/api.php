<?php

use App\Http\Controllers\Api\V1\Auth\LoginMedicoController;
use App\Http\Controllers\Api\V1\Auth\LogoutMedicoController;
use App\Http\Controllers\Api\V1\Auth\RegistrarMedicoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('medicos')->group(function () {
        Route::post('/register', [RegistrarMedicoController::class, '__invoke'])
            ->name('medicos.register');
        Route::post('/login', [LoginMedicoController::class, '__invoke'])
            ->name('medicos.login');
        Route::middleware(['auth:sanctum', 'abilities:medico'])->group(function () {
            Route::post('/logout', [LogoutMedicoController::class, '__invoke'])
                ->name('medicos.logout');
        });
    });
});
