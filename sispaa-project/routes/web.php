<?php

use App\Http\Controllers\Estudiantes\EstudianteController;
use App\Http\Controllers\Api\EstudiantesController as ApiEstudiantesController;
use App\Http\Controllers\Api\CatalogsController as ApiCatalogsController;
use App\Http\Controllers\Laboratorio\LaboratorioController;
use App\Http\Controllers\Secretaria\SecretariaController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// HOME
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('dashboard', function () {
    if (auth()->user()->hasRole('estudiante')) {
        return app(\App\Http\Controllers\Estudiantes\StudentPortalController::class)->dashboard();
    }
    return app(\App\Http\Controllers\Admin\AdminPortalController::class)->dashboard();
})->middleware(['auth', 'verified'])->name('dashboard');


// ESTUDIANTES
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


// DOCENCIA
Route::middleware(['auth', 'verified'])->prefix('docencia')->group(function () {
    Route::get('informes-asignatura', [\App\Http\Controllers\Admin\AdminPortalController::class, 'informesAsignatura'])
        ->name('docencia.informes-asignaturas');
});


// LABORATORIO
Route::middleware(['auth', 'verified'])
    ->prefix('laboratorio')
    ->name('laboratorio.')
    ->group(function () {

        // Dashboard laboratorio

        Route::get('/', [LaboratorioController::class, 'index'])
            ->name('index');

        // Registro de prácticas

        Route::get('/practicas', [LaboratorioController::class, 'practicas'])
            ->name('practicas');

        // Reportes por carrera

        Route::get('/por-carrera', [LaboratorioController::class, 'porCarrera'])
            ->name('porCarrera');

        // Ubicaciones

        Route::get('/ubicaciones', [LaboratorioController::class, 'ubicaciones'])
            ->name('ubicaciones');

        // Crear práctica

        Route::get('/crear', [LaboratorioController::class, 'create'])
            ->name('create');
    });


// TITULACIÓN
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


// VINCULACIÓN
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


// PORTAL DEL ESTUDIANTE (Rutas específicas para el estudiante)
Route::middleware(['auth', 'verified', 'role:estudiante'])
    ->prefix('estudiante')
    ->name('student.')
    ->group(function () {
        Route::get('/documentos', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'documentos'])->name('documentos');
        Route::post('/documentos/upload', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'uploadDocumento'])->name('documentos.upload');

        Route::get('/justificaciones', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'justificaciones'])->name('justificaciones');
        Route::post('/justificaciones/store', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'storeJustificacion'])->name('justificaciones.store');

        Route::get('/titulacion', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'titulacion'])->name('titulacion');

        Route::get('/asistencias', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'asistencias'])->name('asistencias');

        Route::get('/perfil', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'perfil'])->name('perfil');

        Route::get('/notificaciones', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'notificaciones'])->name('notificaciones');
        Route::post('/notificaciones/read', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'readNotificaciones'])->name('notificaciones.read');
    });

// PORTAL DEL ADMINISTRADOR (Rutas específicas para el administrador)
Route::middleware(['auth', 'verified', 'role:administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Gestión de Usuarios
        Route::get('/usuarios', [\App\Http\Controllers\Admin\AdminPortalController::class, 'usuariosIndex'])->name('usuarios.index');
        Route::post('/usuarios', [\App\Http\Controllers\Admin\AdminPortalController::class, 'usuariosStore'])->name('usuarios.store');
        Route::put('/usuarios/{user}', [\App\Http\Controllers\Admin\AdminPortalController::class, 'usuariosUpdate'])->name('usuarios.update');
        Route::post('/usuarios/{user}/toggle-status', [\App\Http\Controllers\Admin\AdminPortalController::class, 'usuariosToggleStatus'])->name('usuarios.toggle-status');

        // Configuración de Malla Curricular
        Route::get('/malla', [\App\Http\Controllers\Admin\AdminPortalController::class, 'mallaIndex'])->name('malla.index');
        Route::post('/carreras', [\App\Http\Controllers\Admin\AdminPortalController::class, 'carreraStore'])->name('carreras.store');
        Route::put('/carreras/{carrera}', [\App\Http\Controllers\Admin\AdminPortalController::class, 'carreraUpdate'])->name('carreras.update');
        Route::post('/carreras/{carrera}/toggle-status', [\App\Http\Controllers\Admin\AdminPortalController::class, 'carreraToggleStatus'])->name('carreras.toggle-status');
        Route::post('/materias', [\App\Http\Controllers\Admin\AdminPortalController::class, 'materiaStore'])->name('materias.store');
        Route::put('/materias/{materia}', [\App\Http\Controllers\Admin\AdminPortalController::class, 'materiaUpdate'])->name('materias.update');
        Route::delete('/materias/{materia}', [\App\Http\Controllers\Admin\AdminPortalController::class, 'materiaDestroy'])->name('materias.destroy');
        Route::post('/materias/{materia}/toggle-status', [\App\Http\Controllers\Admin\AdminPortalController::class, 'materiaToggleStatus'])->name('materias.toggle-status');

        // Fechas Límite y Convocatorias
        Route::get('/fechas', [\App\Http\Controllers\Admin\AdminPortalController::class, 'fechasIndex'])->name('fechas.index');
        Route::post('/fechas/periodos', [\App\Http\Controllers\Admin\AdminPortalController::class, 'periodoStore'])->name('periodos.store');
        Route::put('/fechas/periodos/{periodo}', [\App\Http\Controllers\Admin\AdminPortalController::class, 'periodoUpdate'])->name('periodos.update');
        Route::put('/fechas/periodos/{periodo}/deadlines', [\App\Http\Controllers\Admin\AdminPortalController::class, 'periodoDeadlinesUpdate'])->name('periodos.deadlines.update');
    });


// SECRETARÍA
Route::middleware(['auth', 'verified', 'role:secretaria'])
    ->prefix('secretaria')
    ->name('secretaria.')
    ->group(function () {

        // Expediente SGA
        Route::get('/expediente', [SecretariaController::class, 'expedienteIndex'])
            ->name('expediente.index');
        Route::patch('/expediente/{documento}/review', [SecretariaController::class, 'expedienteReview'])
            ->name('expediente.review');

        // Justificaciones
        Route::get('/justificaciones', [SecretariaController::class, 'justificacionesIndex'])
            ->name('justificaciones.index');
        Route::patch('/justificaciones/{justificacion}/review', [SecretariaController::class, 'justificacionReview'])
            ->name('justificacion.review');

        // Matrículas
        Route::get('/matriculas', [SecretariaController::class, 'matriculasIndex'])
            ->name('matriculas.index');
        Route::post('/matriculas', [SecretariaController::class, 'matriculaStore'])
            ->name('matriculas.store');
        Route::patch('/matriculas/{matricula}/estado', [SecretariaController::class, 'matriculaUpdateEstado'])
            ->name('matriculas.update-estado');
    });


// ARCHIVOS EXTRA

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';