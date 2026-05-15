<?php


use App\Http\Controllers\Estudiantes\EstudianteController;
use App\Http\Controllers\Vinculacion\VinculacionController; // controlador de vinculacion
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


// Vinculación routes (provisional)
Route::middleware(['auth', 'verified'])->prefix('vinculacion')->group(function () {
    
    // Actividades con datos dinámicos
    Route::get('actividades', [VinculacionController::class, 'actividades'])
        ->name('vinculacion.actividades');

    // Empresas beneficiadas
    Route::get('empresas-beneficiadas', function () {
        return Inertia::render('Vinculacion/Empresas');
    })->name('vinculacion.empresas');

    // Líderes de vinculación
    Route::get('lideres', function () {
        return Inertia::render('Vinculacion/AsignarDocente');
    })->name('vinculacion.lideres');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
