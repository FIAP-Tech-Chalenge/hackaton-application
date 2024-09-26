<?php

use App\Http\Controllers\Api\V1\Auth\Medico\LoginMedicoController;
use App\Http\Controllers\Api\V1\Auth\Medico\LogoutMedicoController;
use App\Http\Controllers\Api\V1\Auth\Medico\RegistrarMedicoController;
use App\Http\Controllers\Api\V1\Auth\Paciente\LoginPacienteController;
use App\Http\Controllers\Api\V1\Auth\Paciente\LogoutPacienteController;
use App\Http\Controllers\Api\V1\Auth\Paciente\RegistrarPacienteController;
use App\Http\Controllers\Api\V1\Medicos\Horarios\AlterarHorarioDoDiaController;
use App\Http\Controllers\Api\V1\Medicos\Horarios\LiberarHorariosDoDiaController;
use App\Http\Controllers\Api\V1\Pacientes\Agendamentos\ReservarHorarioController;
use App\Http\Controllers\Api\V1\Pacientes\Horarios\ListarHorariosDisponiveisController;
use App\Http\Controllers\Api\V1\Pacientes\Medicos\ListarMedicosController;
use App\Http\Controllers\Api\V1\Shared\LGPD\SolicitarAnonimizacaoController;
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

            Route::group(['prefix' => 'horarios'], function () {
                Route::post('liberar', [LiberarHorariosDoDiaController::class, '__invoke'])
                    ->name('medicos.horarios.liberar-agenda-do-dia');
                Route::post('alterar', [AlterarHorarioDoDiaController::class, '__invoke'])
                    ->name('medicos.horarios.alterar');
            });

            Route::group(['prefix' => 'lgpd'], function () {
                Route::post('solicitar-anonimizacao', [SolicitarAnonimizacaoController::class, '__invoke'])
                    ->name('medicos.lgpd.solicitar-anonimizacao');
            });
        });
    });
    Route::prefix('pacientes')->group(function () {
        Route::post('/register', [RegistrarPacienteController::class, '__invoke'])
            ->name('pacientes.register');
        Route::post('/login', [LoginPacienteController::class, '__invoke'])
            ->name('pacientes.login');
        Route::middleware(['auth:sanctum', 'abilities:paciente'])->group(function () {
            Route::post('/logout', [LogoutPacienteController::class, '__invoke'])
                ->name('pacientes.logout');

            Route::group(['prefix' => 'medicos'], function () {
                Route::get('/', [ListarMedicosController::class, '__invoke'])
                    ->name('pacientes.medicos.listar');
            });

            Route::group(['prefix' => 'horarios'], function () {
                Route::get('disponiveis/{medicoUuid}/{data}', [ListarHorariosDisponiveisController::class, '__invoke'])
                    ->whereUuid('medicoUuid')
                    ->where('data', '\d{4}-\d{2}-\d{2}')
                    ->name('pacientes.horarios.disponiveis');
            });

            Route::group(['prefix' => 'agendamentos'], function () {
                Route::post('reservar', [ReservarHorarioController::class, '__invoke'])
                    ->name('pacientes.agendamentos.reservar');
            });

            Route::group(['prefix' => 'lgpd'], function () {
                Route::post('solicitar-anonimizacao', [SolicitarAnonimizacaoController::class, '__invoke'])
                    ->name('pacientes.lgpd.solicitar-anonimizacao');
            });
        });
    });
});
