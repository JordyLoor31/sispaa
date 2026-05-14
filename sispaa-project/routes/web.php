<?php

use App\Http\Controllers\Estudiantes\EstudianteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])
    ->prefix('estudiantes')
    ->name('estudiantes.')
    ->group(function () {
        Route::get('/', [EstudianteController::class, 'index'])->name('index');
        Route::get('matriculados', [EstudianteController::class, 'matriculados'])->name('matriculados');
        Route::get('faltas', [EstudianteController::class, 'faltas'])->name('faltas');
        Route::get('justificaciones', [EstudianteController::class, 'justificaciones'])->name('justificaciones');
    });

// Titulación routes(provisional)
Route::middleware(['auth', 'verified'])->prefix('titulacion')->group(function () {
    Route::get('temas-desarrollo', function () {
        return Inertia::render('Titulacion/Temas');
    })->name('titulacion.temas-desarrollo');
    
    Route::get('estudiantes-titulacion', function () {
        return Inertia::render('Titulacion/EnProceso');
    })->name('titulacion.estudiantes-titulacion');
    
    Route::get('estudiantes-titulados', function () {
        return Inertia::render('Titulacion/Graduados');
    })->name('titulacion.estudiantes-titulados');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
