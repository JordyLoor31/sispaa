<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

// Vinculación routes (provisionalS)
Route::middleware(['auth', 'verified'])->prefix('vinculacion')->group(function () {
    
    // Vista de actividades
    Route::get('actividades', function () {
        return Inertia::render('Vinculacion/Actividades');
    })->name('vinculacion.actividades');

    // Vista de empresas beneficiadas
    Route::get('empresas-beneficiadas', function () {
        return Inertia::render('Vinculacion/Empresas');
    })->name('vinculacion.empresas');

    // Vista de asignación de docentes
    Route::get('lideres', function () {
        return Inertia::render('Vinculacion/AsignarDocente');
    })->name('vinculacion.lideres');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
