<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TurnoController;

Route::get('/', [TurnoController::class, 'index'])->name('turnos.index');
Route::post('/turnos', [TurnoController::class, 'store'])->name('turnos.store');
Route::delete('/turnos/{id}', [TurnoController::class, 'cancel'])->name('turnos.cancel');
Route::post('/turnos/siguiente', [TurnoController::class, 'siguiente'])->name('turnos.siguiente');
Route::post('/turnos/reordenar', [TurnoController::class, 'reordenar'])->name('turnos.reordenar');
Route::get('/pantalla', [TurnoController::class, 'pantalla'])->name('turnos.pantalla');
Route::get('/admin', [TurnoController::class, 'admin'])->name('turnos.admin');
Route::get('/turnos/cola', [TurnoController::class, 'cola'])->name('turnos.cola');