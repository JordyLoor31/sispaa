<?php

use App\Http\Controllers\Estudiantes\EstudianteController;
use App\Http\Controllers\Api\EstudiantesController as ApiEstudiantesController;
use App\Http\Controllers\Api\CatalogsController as ApiCatalogsController;
use App\Http\Controllers\Laboratorio\LaboratorioController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| ESTUDIANTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('estudiantes')
    ->name('estudiantes.')
    ->group(function () {

        // API: listado y filtros para la UI (método en EstudianteController)
        Route::get('/api/list', [EstudianteController::class, 'apiList'])
            ->name('api.list');

        Route::get('/api/carreras', [ApiCatalogsController::class, 'carreras'])
            ->name('api.carreras.index');


        Route::get('/', [EstudianteController::class, 'index'])
            ->name('index');

        Route::get('/matriculados', [EstudianteController::class, 'matriculados'])
            ->name('matriculados');

        Route::get('/faltas', [EstudianteController::class, 'faltas'])
            ->name('faltas');

        Route::get('/justificaciones', [EstudianteController::class, 'justificaciones'])
            ->name('justificaciones');
    });


/*
|--------------------------------------------------------------------------
| LABORATORIO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('laboratorio')
    ->name('laboratorio.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard laboratorio
        |--------------------------------------------------------------------------
        */

        Route::get('/', [LaboratorioController::class, 'index'])
            ->name('index');

        /*
        |--------------------------------------------------------------------------
        | Registro de prácticas
        |--------------------------------------------------------------------------
        */

        Route::get('/practicas', [LaboratorioController::class, 'practicas'])
            ->name('practicas');

        /*
        |--------------------------------------------------------------------------
        | Reportes por carrera
        |--------------------------------------------------------------------------
        */

        Route::get('/por-carrera', [LaboratorioController::class, 'porCarrera'])
            ->name('porCarrera');

        /*
        |--------------------------------------------------------------------------
        | Ubicaciones
        |--------------------------------------------------------------------------
        */

        Route::get('/ubicaciones', [LaboratorioController::class, 'ubicaciones'])
            ->name('ubicaciones');

        /*
        |--------------------------------------------------------------------------
        | Crear práctica
        |--------------------------------------------------------------------------
        */

        Route::get('/crear', [LaboratorioController::class, 'create'])
            ->name('create');
    });


/*
|--------------------------------------------------------------------------
| TITULACIÓN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('titulacion')
    ->group(function () {

        Route::get('/temas-desarrollo', function () {
            return Inertia::render('Titulacion/Temas');
        })->name('titulacion.temas-desarrollo');

        Route::get('/estudiantes-titulacion', function () {
            return Inertia::render('Titulacion/EnProceso');
        })->name('titulacion.estudiantes-titulacion');

        Route::get('/estudiantes-titulados', function () {
            return Inertia::render('Titulacion/Graduados');
        })->name('titulacion.estudiantes-titulados');
    });


/*
|--------------------------------------------------------------------------
| VINCULACIÓN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('vinculacion')
    ->group(function () {

        Route::get('/actividades', function () {
            return Inertia::render('Vinculacion/Actividades');
        })->name('vinculacion.actividades');

        Route::get('/empresas-beneficiadas', function () {
            return Inertia::render('Vinculacion/Empresas');
        })->name('vinculacion.empresas');

        Route::get('/lideres', function () {
            return Inertia::render('Vinculacion/AsignarDocente');
        })->name('vinculacion.lideres');
    });


/*
|--------------------------------------------------------------------------
| ARCHIVOS EXTRA
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';