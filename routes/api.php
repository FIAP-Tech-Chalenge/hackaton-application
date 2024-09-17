<?php

use App\Http\Controllers\Api\V1\Medico\RegistrarMedicoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('medicos')->group(function () {
        Route::post('/', [RegistrarMedicoController::class, '__invoke'])
            ->name('medicos.register');
    });
});
