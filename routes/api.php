<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransacaoController;
use App\Http\Controllers\Api\UsuarioController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deposito',      [TransacaoController::class, 'depositar']);
    Route::post('/transferencia', [TransacaoController::class, 'transferir']);
    Route::post('/reversao/{id}', [TransacaoController::class, 'reverter']);
});

Route::middleware('auth:sanctum')->get('/usuarios', [UsuarioController::class, 'listar']);
