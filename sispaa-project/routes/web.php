<?php

use App\Http\Controllers\Estudiantes\EstudianteController;
use App\Http\Controllers\Api\EstudiantesController as ApiEstudiantesController;
use App\Http\Controllers\Api\CatalogsController as ApiCatalogsController;
use App\Http\Controllers\Docencia\DocenteController;
use App\Http\Controllers\Docencia\SilaboController;
use App\Http\Controllers\Investigacion\InvestigacionController;
use App\Http\Controllers\Laboratorio\LaboratorioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\Reportes\ReporteController;
use App\Http\Controllers\Titulacion\TitulacionController;
use App\Http\Controllers\Vinculacion\VinculacionController;

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


// NOTIFICACIONES (centro compartido de staff: docente/coordinador/secretaría/
// SystemAdministrador; el estudiante tiene el suyo propio bajo 'estudiante/notificaciones')
Route::middleware(['auth', 'verified', 'role:docente|coordinador|secretaria|SystemAdministrador'])
    ->prefix('notificaciones')
    ->name('notificaciones.')
    ->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/read', [NotificacionController::class, 'markRead'])->name('read');
    });


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


// DOCENCIA (vista de gestión/coordinación, sin restricción de rol específica)
Route::middleware(['auth', 'verified'])->prefix('docencia')->group(function () {
    Route::get('informes-asignatura', [\App\Http\Controllers\Admin\AdminPortalController::class, 'informesAsignatura'])
        ->name('docencia.informes-asignaturas');
});

// DOCENCIA - Autoservicio del Docente (Mis Sílabos / Mis Informes)
Route::middleware(['auth', 'verified', 'role:docente|SystemAdministrador'])
    ->prefix('docencia')
    ->name('docencia.')
    ->group(function () {
        Route::get('/mis-silabos', [SilaboController::class, 'index'])->name('mis-silabos');
        Route::post('/mis-silabos/upload', [SilaboController::class, 'store'])->name('mis-silabos.upload');

        Route::get('/mis-informes', [DocenteController::class, 'informes'])->name('mis-informes');
        Route::post('/mis-informes/upload', [DocenteController::class, 'storeInforme'])->name('mis-informes.upload');
    });


// INVESTIGACIÓN (docente: proyectos propios / coordinador: proyectos supervisados)
Route::middleware(['auth', 'verified', 'role:docente|coordinador|SystemAdministrador'])
    ->prefix('investigacion')
    ->name('investigacion.')
    ->group(function () {
        Route::get('/', [InvestigacionController::class, 'index'])->name('index');
        Route::post('/', [InvestigacionController::class, 'store'])->name('store');
        Route::put('/{investigacion}', [InvestigacionController::class, 'update'])->name('update');
        Route::delete('/{investigacion}', [InvestigacionController::class, 'destroy'])->name('destroy');

        Route::get('/{investigacion}/hitos', [InvestigacionController::class, 'hitos'])->name('hitos');
        Route::post('/{investigacion}/hitos', [InvestigacionController::class, 'storeHito'])->name('hitos.store');
        Route::put('/hitos/{hito}', [InvestigacionController::class, 'updateHito'])->name('hitos.update');

        Route::post('/{investigacion}/seguimiento', [InvestigacionController::class, 'storeSeguimiento'])->name('seguimiento.store');
        Route::patch('/seguimiento/{seguimiento}/responder', [InvestigacionController::class, 'responderSeguimiento'])->name('seguimiento.responder');
    });


// LABORATORIO
Route::middleware(['auth', 'verified', 'role:docente|SystemAdministrador'])
    ->prefix('laboratorio')
    ->name('laboratorio.')
    ->group(function () {
        Route::get('/', [LaboratorioController::class, 'index'])->name('index');

        // Prácticas (listado + CRUD; el registro se hace desde un modal aquí mismo)
        Route::get('/practicas', [LaboratorioController::class, 'practicas'])->name('practicas');
        Route::post('/practicas', [LaboratorioController::class, 'storePractica'])->name('practicas.store');
        Route::put('/practicas/{practica}', [LaboratorioController::class, 'updatePractica'])->name('practicas.update');
        Route::delete('/practicas/{practica}', [LaboratorioController::class, 'destroyPractica'])->name('practicas.destroy');

        // Asistencia por práctica
        Route::get('/practicas/{practica}/asistencia', [LaboratorioController::class, 'asistencia'])->name('practicas.asistencia');
        Route::post('/practicas/{practica}/asistencia', [LaboratorioController::class, 'storeAsistencia'])->name('practicas.asistencia.store');

        // Reportes por carrera
        Route::get('/por-carrera', [LaboratorioController::class, 'porCarrera'])->name('porCarrera');

        // Laboratorios (espacios físicos; antes "Ubicaciones")
        Route::get('/laboratorios', [LaboratorioController::class, 'laboratorios'])->name('laboratorios');
        Route::post('/laboratorios', [LaboratorioController::class, 'storeLaboratorio'])->name('laboratorios.store');
        Route::put('/laboratorios/{laboratorio}', [LaboratorioController::class, 'updateLaboratorio'])->name('laboratorios.update');
        Route::delete('/laboratorios/{laboratorio}', [LaboratorioController::class, 'destroyLaboratorio'])->name('laboratorios.destroy');

        // Equipos
        Route::get('/equipos', [LaboratorioController::class, 'equipos'])->name('equipos');
        Route::post('/equipos', [LaboratorioController::class, 'storeEquipo'])->name('equipos.store');
        Route::put('/equipos/{equipo}', [LaboratorioController::class, 'updateEquipo'])->name('equipos.update');
        Route::delete('/equipos/{equipo}', [LaboratorioController::class, 'destroyEquipo'])->name('equipos.destroy');

        // Reactivos
        Route::get('/reactivos', [LaboratorioController::class, 'reactivos'])->name('reactivos');
        Route::post('/reactivos', [LaboratorioController::class, 'storeReactivo'])->name('reactivos.store');
        Route::put('/reactivos/{reactivo}', [LaboratorioController::class, 'updateReactivo'])->name('reactivos.update');
        Route::delete('/reactivos/{reactivo}', [LaboratorioController::class, 'destroyReactivo'])->name('reactivos.destroy');
    });


// TITULACIÓN (panel único del coordinador; consolida temas/en-proceso/graduados)
Route::middleware(['auth', 'verified', 'role:coordinador|SystemAdministrador'])
    ->prefix('titulacion')
    ->name('titulacion.')
    ->group(function () {
        Route::get('/', [TitulacionController::class, 'index'])->name('index');
        Route::post('/', [TitulacionController::class, 'store'])->name('store');
        Route::put('/{titulacion}', [TitulacionController::class, 'update'])->name('update');
        Route::delete('/{titulacion}', [TitulacionController::class, 'destroy'])->name('destroy');
    });


// VINCULACIÓN (líder de actividad se asigna dentro del propio formulario)
Route::middleware(['auth', 'verified', 'role:coordinador|SystemAdministrador'])
    ->prefix('vinculacion')
    ->name('vinculacion.')
    ->group(function () {
        Route::get('/actividades', [VinculacionController::class, 'actividades'])->name('actividades');
        Route::post('/actividades', [VinculacionController::class, 'storeActividad'])->name('actividades.store');
        Route::put('/actividades/{actividad}', [VinculacionController::class, 'updateActividad'])->name('actividades.update');
        Route::delete('/actividades/{actividad}', [VinculacionController::class, 'destroyActividad'])->name('actividades.destroy');

        Route::get('/empresas-beneficiadas', [VinculacionController::class, 'empresas'])->name('empresas');
        Route::post('/empresas-beneficiadas', [VinculacionController::class, 'storeEmpresa'])->name('empresas.store');
        Route::put('/empresas-beneficiadas/{empresa}', [VinculacionController::class, 'updateEmpresa'])->name('empresas.update');
        Route::delete('/empresas-beneficiadas/{empresa}', [VinculacionController::class, 'destroyEmpresa'])->name('empresas.destroy');
    });


// REPORTES (exportación CSV/XLSX/PDF; solo Secretaría y SystemAdministrador)
Route::middleware(['auth', 'verified', 'role:secretaria|SystemAdministrador'])
    ->prefix('reportes')
    ->name('reportes.')
    ->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/export/csv', [ReporteController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/xlsx', [ReporteController::class, 'exportXlsx'])->name('export.xlsx');
        Route::get('/export/pdf', [ReporteController::class, 'exportPdf'])->name('export.pdf');
    });


// PORTAL DEL ESTUDIANTE (Rutas específicas para el estudiante)
// Nota: 'role:' de Spatie no respeta el Gate::before bypass de SystemAdministrador
// (ese bypass solo cubre Gate::allows()/permission:), por eso se agrega explícitamente
// como alternativa aquí para que pueda entrar a cualquier vista que ve en el Sidebar.
Route::middleware(['auth', 'verified', 'role:estudiante|SystemAdministrador'])
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

// PORTAL DEL ADMINISTRADOR (Rutas específicas para SystemAdministrador)
Route::middleware(['auth', 'verified', 'role:SystemAdministrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Gestión de Usuarios (CRUD completo, ver UserController)
        Route::get('/usuarios', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('usuarios.show');
        Route::get('/usuarios/{user}/editar', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('usuarios.toggle-status');

        // Carreras (CRUD completo, ver CarreraController)
        Route::get('/carreras', [\App\Http\Controllers\Admin\CarreraController::class, 'index'])->name('carreras.index');
        Route::get('/carreras/crear', [\App\Http\Controllers\Admin\CarreraController::class, 'create'])->name('carreras.create');
        Route::post('/carreras', [\App\Http\Controllers\Admin\CarreraController::class, 'store'])->name('carreras.store');
        Route::get('/carreras/{carrera}', [\App\Http\Controllers\Admin\CarreraController::class, 'show'])->name('carreras.show');
        Route::get('/carreras/{carrera}/editar', [\App\Http\Controllers\Admin\CarreraController::class, 'edit'])->name('carreras.edit');
        Route::put('/carreras/{carrera}', [\App\Http\Controllers\Admin\CarreraController::class, 'update'])->name('carreras.update');
        Route::post('/carreras/{carrera}/toggle-status', [\App\Http\Controllers\Admin\CarreraController::class, 'toggleStatus'])->name('carreras.toggle-status');

        // Asignaturas (malla curricular por carrera)
        Route::get('/materias', [\App\Http\Controllers\Admin\AdminPortalController::class, 'materiasIndex'])->name('materias.index');
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
Route::middleware(['auth', 'verified', 'role:secretaria|SystemAdministrador'])
    ->prefix('secretaria')
    ->name('secretaria.')
    ->group(function () {

        // Expediente SGA
        Route::get('/expediente', [\App\Http\Controllers\Secretaria\ExpedienteController::class, 'index'])
            ->name('expediente.index');
        Route::get('/expediente/{documento}', [\App\Http\Controllers\Secretaria\ExpedienteController::class, 'show'])
            ->name('expediente.show');
        Route::patch('/expediente/{documento}/review', [\App\Http\Controllers\Secretaria\ExpedienteController::class, 'review'])
            ->name('expediente.review');

        // Justificaciones
        Route::get('/justificaciones', [\App\Http\Controllers\Secretaria\JustificacionController::class, 'index'])
            ->name('justificaciones.index');
        Route::get('/justificaciones/{justificacion}', [\App\Http\Controllers\Secretaria\JustificacionController::class, 'show'])
            ->name('justificaciones.show');
        Route::patch('/justificaciones/{justificacion}/review', [\App\Http\Controllers\Secretaria\JustificacionController::class, 'review'])
            ->name('justificacion.review');

        // Matrículas
        Route::get('/matriculas', [\App\Http\Controllers\Secretaria\MatriculaController::class, 'index'])
            ->name('matriculas.index');
        Route::get('/matriculas/crear', [\App\Http\Controllers\Secretaria\MatriculaController::class, 'create'])
            ->name('matriculas.create');
        Route::post('/matriculas', [\App\Http\Controllers\Secretaria\MatriculaController::class, 'store'])
            ->name('matriculas.store');
        Route::patch('/matriculas/{matricula}/estado', [\App\Http\Controllers\Secretaria\MatriculaController::class, 'updateEstado'])
            ->name('matriculas.update-estado');

        // Convocatorias
        Route::get('/convocatorias', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'index'])
            ->name('convocatorias.index');
        Route::get('/convocatorias/crear', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'create'])
            ->name('convocatorias.create');
        Route::post('/convocatorias', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'store'])
            ->name('convocatorias.store');
        Route::get('/convocatorias/{convocatoria}', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'show'])
            ->name('convocatorias.show');
        Route::get('/convocatorias/{convocatoria}/editar', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'edit'])
            ->name('convocatorias.edit');
        Route::put('/convocatorias/{convocatoria}', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'update'])
            ->name('convocatorias.update');
        Route::delete('/convocatorias/{convocatoria}', [\App\Http\Controllers\Secretaria\ConvocatoriaController::class, 'destroy'])
            ->name('convocatorias.destroy');

        // Grupos de Documentos
        Route::get('/grupos-documentos', [\App\Http\Controllers\Secretaria\GrupoDocumentoController::class, 'index'])
            ->name('grupos-documentos.index');
        Route::get('/grupos-documentos/crear', [\App\Http\Controllers\Secretaria\GrupoDocumentoController::class, 'create'])
            ->name('grupos-documentos.create');
        Route::post('/grupos-documentos', [\App\Http\Controllers\Secretaria\GrupoDocumentoController::class, 'store'])
            ->name('grupos-documentos.store');
        Route::get('/grupos-documentos/{grupo}', [\App\Http\Controllers\Secretaria\GrupoDocumentoController::class, 'show'])
            ->name('grupos-documentos.show');
        Route::post('/grupos-documentos/{grupo}/requisitos', [\App\Http\Controllers\Secretaria\GrupoDocumentoController::class, 'requisitoStore'])
            ->name('grupos-documentos.requisitos.store');
        Route::post('/grupos-documentos/{grupo}/toggle', [\App\Http\Controllers\Secretaria\GrupoDocumentoController::class, 'toggle'])
            ->name('grupos-documentos.toggle');

        // Notificaciones Masivas
        Route::get('/notificaciones-masivas', [\App\Http\Controllers\Secretaria\NotificacionMasivaController::class, 'index'])
            ->name('notificaciones-masivas.index');
        Route::post('/notificaciones-masivas', [\App\Http\Controllers\Secretaria\NotificacionMasivaController::class, 'store'])
            ->name('notificaciones-masivas.store');

        // Revisión de Sílabos
        Route::get('/silabos', [\App\Http\Controllers\Secretaria\SilaboRevisionController::class, 'index'])
            ->name('silabos.index');
        Route::get('/silabos/{silabo}', [\App\Http\Controllers\Secretaria\SilaboRevisionController::class, 'show'])
            ->name('silabos.show');
        Route::patch('/silabos/{silabo}/review', [\App\Http\Controllers\Secretaria\SilaboRevisionController::class, 'review'])
            ->name('silabos.review');
    });


// ARCHIVOS EXTRA

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';