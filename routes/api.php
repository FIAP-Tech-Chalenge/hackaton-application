<?php

use App\Http\Controllers\Api\V1\Auth\Medico\LoginMedicoController;
use App\Http\Controllers\Api\V1\Auth\Medico\LogoutMedicoController;
use App\Http\Controllers\Api\V1\Auth\Medico\RegistrarMedicoController;
use App\Http\Controllers\Api\V1\Auth\Paciente\LoginPacienteController;
use App\Http\Controllers\Api\V1\Auth\Paciente\LogoutPacienteController;
use App\Http\Controllers\Api\V1\Auth\Paciente\RegistrarPacienteController;
use App\Http\Controllers\Api\V1\Medicos\Horarios\LiberarHorariosDoDiaController;
use App\Http\Controllers\Api\V1\Pacientes\Medicos\ListarMedicosController;
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
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/logout', [LogoutMedicoController::class, '__invoke'])
                ->name('medicos.logout');
            Route::post('horarios/liberar', [LiberarHorariosDoDiaController::class, '__invoke'])
                ->name('medicos.horarios.liberar-agenda-dodia');
        });
    });
    Route::prefix('pacientes')->group(function () {
        Route::post('/register', [RegistrarPacienteController::class, '__invoke'])
            ->name('pacientes.register');
        Route::post('/login', [LoginPacienteController::class, '__invoke'])
            ->name('pacientes.login');
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/logout', [LogoutPacienteController::class, '__invoke'])
                ->name('pacientes.logout');
            Route::get('medicos', [ListarMedicosController::class, '__invoke'])
                ->name('pacientes.medicos.listar');
        });
    });
});
