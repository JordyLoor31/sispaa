<?php

use App\Http\Controllers\Estudiantes\EstudianteController;
use App\Http\Controllers\Api\EstudiantesController as ApiEstudiantesController;
use App\Http\Controllers\Docencia\DocenteController;
use App\Http\Controllers\Docencia\SilaboController;
use App\Http\Controllers\Investigacion\InvestigacionController;
use App\Http\Controllers\Laboratorio\EquipoController;
use App\Http\Controllers\Laboratorio\EspacioLaboratorioController;
use App\Http\Controllers\Laboratorio\LaboratorioController;
use App\Http\Controllers\Laboratorio\PracticaLaboratorioController;
use App\Http\Controllers\Laboratorio\ReactivoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\Reportes\ReporteController;
use App\Http\Controllers\Reportes\EstudiantesReporteController;
use App\Http\Controllers\Reportes\SilabosReporteController;
use App\Http\Controllers\Reportes\InformesReporteController;
use App\Http\Controllers\Reportes\VinculacionReporteController;
use App\Http\Controllers\Reportes\TitulacionReporteController;
use App\Http\Controllers\Reportes\LaboratorioReporteController;
use App\Http\Controllers\Titulacion\TitulacionController;

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
        Route::get('/recientes', [NotificacionController::class, 'recientes'])->name('recientes');
    });


// ESTUDIANTES (gestión/staff: coordinador y secretaría ven todo el listado
// institucional; docente ve únicamente, en Faltas/Justificaciones, a los
// estudiantes de las materias que tiene asignadas -- scoping en el
// controlador. 'Matriculados' es información general y queda reservada a
// secretaría/coordinador/SystemAdministrador).
Route::middleware(['auth', 'verified', 'role:coordinador|secretaria|docente|SystemAdministrador'])
    ->prefix('estudiantes')
    ->name('estudiantes.')
    ->group(function () {

        Route::get('/', [EstudianteController::class, 'index'])
            ->name('index');

        Route::get('/matriculados', [EstudianteController::class, 'matriculados'])
            ->name('matriculados');

        Route::get('/faltas', [EstudianteController::class, 'faltas'])
            ->name('faltas');

        Route::get('/justificaciones', [EstudianteController::class, 'justificaciones'])
            ->name('justificaciones');
    });


// Nota: la vista de gestión/supervisión de Informes de Asignatura (todos los
// docentes) vivía aquí como docencia.informes-asignaturas. Se reemplazó por
// secretaria.informes.* (InformeRevisionController), que sigue el mismo
// patrón Index+Show con revisión que Sílabos/Justificaciones.

// DOCENCIA - Autoservicio del Docente (Mis Sílabos / Mis Informes)
Route::middleware(['auth', 'verified', 'role:docente|SystemAdministrador'])
    ->prefix('docencia')
    ->name('docencia.')
    ->group(function () {
        Route::get('/mis-silabos', [SilaboController::class, 'index'])->name('mis-silabos');
        Route::post('/mis-silabos/upload', [SilaboController::class, 'store'])->name('mis-silabos.upload');
        Route::get('/mis-silabos/{silabo}/ver', [SilaboController::class, 'ver'])->name('mis-silabos.ver');

        Route::get('/mis-informes', [DocenteController::class, 'informes'])->name('mis-informes');
        Route::post('/mis-informes/upload', [DocenteController::class, 'storeInforme'])->name('mis-informes.upload');
        Route::get('/mis-informes/{informe}/ver', [DocenteController::class, 'verInforme'])->name('mis-informes.ver');
    });


// INVESTIGACIÓN (docente: proyectos propios / coordinador: proyectos supervisados)
Route::middleware(['auth', 'verified', 'role:docente|coordinador|SystemAdministrador'])
    ->prefix('investigacion')
    ->name('investigacion.')
    ->group(function () {
        Route::get('/', [InvestigacionController::class, 'index'])->name('index');
        Route::get('/crear', [InvestigacionController::class, 'create'])->name('create');
        Route::post('/', [InvestigacionController::class, 'store'])->name('store');
        Route::get('/{investigacion}/editar', [InvestigacionController::class, 'edit'])->name('edit');
        Route::put('/{investigacion}', [InvestigacionController::class, 'update'])->name('update');
        Route::delete('/{investigacion}', [InvestigacionController::class, 'destroy'])->name('destroy');

        Route::get('/{investigacion}/hitos', [InvestigacionController::class, 'show'])->name('hitos');
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

        // Prácticas
        Route::get('/practicas', [PracticaLaboratorioController::class, 'index'])->name('practicas');
        Route::get('/practicas/crear', [PracticaLaboratorioController::class, 'create'])->name('practicas.create');
        Route::post('/practicas', [PracticaLaboratorioController::class, 'store'])->name('practicas.store');
        Route::get('/practicas/{practica}/editar', [PracticaLaboratorioController::class, 'edit'])->name('practicas.edit');
        Route::put('/practicas/{practica}', [PracticaLaboratorioController::class, 'update'])->name('practicas.update');
        Route::delete('/practicas/{practica}', [PracticaLaboratorioController::class, 'destroy'])->name('practicas.destroy');

        // Asistencia por práctica
        Route::get('/practicas/{practica}/asistencia', [PracticaLaboratorioController::class, 'asistencia'])->name('practicas.asistencia');
        Route::post('/practicas/{practica}/asistencia', [PracticaLaboratorioController::class, 'storeAsistencia'])->name('practicas.asistencia.store');

        // Reportes por carrera
        Route::get('/por-carrera', [LaboratorioController::class, 'porCarrera'])->name('porCarrera');

        // Laboratorios (espacios físicos; antes "Ubicaciones")
        Route::get('/laboratorios', [EspacioLaboratorioController::class, 'index'])->name('laboratorios');
        Route::get('/laboratorios/crear', [EspacioLaboratorioController::class, 'create'])->name('laboratorios.create');
        Route::post('/laboratorios', [EspacioLaboratorioController::class, 'store'])->name('laboratorios.store');
        Route::get('/laboratorios/{laboratorio}', [EspacioLaboratorioController::class, 'show'])->name('laboratorios.show');
        Route::get('/laboratorios/{laboratorio}/editar', [EspacioLaboratorioController::class, 'edit'])->name('laboratorios.edit');
        Route::put('/laboratorios/{laboratorio}', [EspacioLaboratorioController::class, 'update'])->name('laboratorios.update');
        Route::delete('/laboratorios/{laboratorio}', [EspacioLaboratorioController::class, 'destroy'])->name('laboratorios.destroy');

        // Equipos
        Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos');
        Route::get('/equipos/crear', [EquipoController::class, 'create'])->name('equipos.create');
        Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
        Route::get('/equipos/{equipo}', [EquipoController::class, 'show'])->name('equipos.show');
        Route::get('/equipos/{equipo}/editar', [EquipoController::class, 'edit'])->name('equipos.edit');
        Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
        Route::delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');

        // Reactivos
        Route::get('/reactivos', [ReactivoController::class, 'index'])->name('reactivos');
        Route::get('/reactivos/crear', [ReactivoController::class, 'create'])->name('reactivos.create');
        Route::post('/reactivos', [ReactivoController::class, 'store'])->name('reactivos.store');
        Route::get('/reactivos/{reactivo}', [ReactivoController::class, 'show'])->name('reactivos.show');
        Route::get('/reactivos/{reactivo}/editar', [ReactivoController::class, 'edit'])->name('reactivos.edit');
        Route::put('/reactivos/{reactivo}', [ReactivoController::class, 'update'])->name('reactivos.update');
        Route::delete('/reactivos/{reactivo}', [ReactivoController::class, 'destroy'])->name('reactivos.destroy');
    });


// TITULACIÓN (panel único del coordinador; consolida temas/en-proceso/graduados)
// Ver (index/show): coordinador y SystemAdministrador ven todos los procesos;
// secretaría también ve todos (solo lectura); docente ve únicamente los
// procesos donde él es el tutor asignado (scoping en el controlador).
// Gestionar (crear/editar/eliminar/cambiar estado): solo coordinador y
// SystemAdministrador, como antes.
// Escritura (coordinador/SystemAdministrador). IMPORTANTE: este grupo va
// ANTES del de lectura para que la ruta literal '/crear' se registre antes
// que la comodín '/{titulacion}' (show); si no, Laravel interpreta "crear"
// como un id de titulación y devuelve 404 al abrir "Registrar Tema".
// COORDINADOR - Revisión de Sílabos: exclusivo de coordinador/SystemAdministrador
// (secretaría ya no tiene acceso). Coordinador y SystemAdministrador ven y
// revisan los sílabos de CUALQUIER docente, sin acotar por carrera.
Route::middleware(['auth', 'verified', 'role:coordinador|SystemAdministrador'])
    ->prefix('coordinador/silabos')
    ->name('coordinador.silabos.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Secretaria\SilaboRevisionController::class, 'index'])
            ->name('index');
        Route::get('/{silabo}', [\App\Http\Controllers\Secretaria\SilaboRevisionController::class, 'show'])
            ->name('show');
        Route::patch('/{silabo}/review', [\App\Http\Controllers\Secretaria\SilaboRevisionController::class, 'review'])
            ->name('review');
    });

Route::middleware(['auth', 'verified', 'role:coordinador|SystemAdministrador'])
    ->prefix('titulacion')
    ->name('titulacion.')
    ->group(function () {
        Route::get('/crear', [TitulacionController::class, 'create'])->name('create');
        Route::post('/', [TitulacionController::class, 'store'])->name('store');
        Route::get('/{titulacion}/editar', [TitulacionController::class, 'edit'])->name('edit');
        Route::put('/{titulacion}', [TitulacionController::class, 'update'])->name('update');
        Route::delete('/{titulacion}', [TitulacionController::class, 'destroy'])->name('destroy');
    });

// Lectura (coordinador/secretaría/docente/SystemAdministrador).
Route::middleware(['auth', 'verified', 'role:coordinador|secretaria|docente|SystemAdministrador'])
    ->prefix('titulacion')
    ->name('titulacion.')
    ->group(function () {
        Route::get('/', [TitulacionController::class, 'index'])->name('index');
        Route::get('/{titulacion}', [TitulacionController::class, 'show'])->name('show');
    });


// VINCULACIÓN (líder de actividad se asigna dentro del propio formulario)
Route::middleware(['auth', 'verified', 'role:coordinador|SystemAdministrador'])
    ->prefix('vinculacion')
    ->name('vinculacion.')
    ->group(function () {
        Route::get('/actividades', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'index'])->name('actividades');
        Route::get('/actividades/crear', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'create'])->name('actividades.create');
        Route::post('/actividades', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'store'])->name('actividades.store');
        Route::get('/actividades/{actividad}', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'show'])->name('actividades.show');
        Route::get('/actividades/{actividad}/editar', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'edit'])->name('actividades.edit');
        Route::put('/actividades/{actividad}', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'update'])->name('actividades.update');
        Route::delete('/actividades/{actividad}', [\App\Http\Controllers\Vinculacion\ActividadVinculacionController::class, 'destroy'])->name('actividades.destroy');

        Route::get('/empresas-beneficiadas', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'index'])->name('empresas');
        Route::get('/empresas-beneficiadas/crear', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'create'])->name('empresas.create');
        Route::post('/empresas-beneficiadas', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('/empresas-beneficiadas/{empresa}', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'show'])->name('empresas.show');
        Route::get('/empresas-beneficiadas/{empresa}/editar', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'edit'])->name('empresas.edit');
        Route::put('/empresas-beneficiadas/{empresa}', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'update'])->name('empresas.update');
        Route::delete('/empresas-beneficiadas/{empresa}', [\App\Http\Controllers\Vinculacion\EmpresaController::class, 'destroy'])->name('empresas.destroy');
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

        // Sub-reportes estadísticos (gráficos ApexCharts, controladores dedicados por módulo)
        Route::get('/estudiantes', [EstudiantesReporteController::class, 'index'])->name('estudiantes');
        Route::get('/silabos', [SilabosReporteController::class, 'index'])->name('silabos');
        Route::get('/informes', [InformesReporteController::class, 'index'])->name('informes');
        Route::get('/vinculacion', [VinculacionReporteController::class, 'index'])->name('vinculacion');
        Route::get('/titulacion', [TitulacionReporteController::class, 'index'])->name('titulacion');
        Route::get('/laboratorio', [LaboratorioReporteController::class, 'index'])->name('laboratorio');
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

        // Plantillas de Documentos publicadas por Secretaría (solo lectura +
        // descarga; la descarga en sí vive en la ruta compartida plantillas.descargar).
        Route::get('/plantillas', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'plantillas'])->name('plantillas');

        Route::get('/perfil', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'perfil'])->name('perfil');

        // "Mis Datos": vista de solo lectura con todo lo completado en el wizard
        Route::get('/perfil/datos', [\App\Http\Controllers\Estudiantes\PerfilEstudianteController::class, 'misDatos'])->name('perfil.show');

        // Wizard "Completar mi perfil" (perfiles_estudiantes + estudiante_datos_adicionales)
        Route::get('/perfil/editar', [\App\Http\Controllers\Estudiantes\PerfilEstudianteController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil', [\App\Http\Controllers\Estudiantes\PerfilEstudianteController::class, 'update'])->name('perfil.update');

        // Familiares/representantes (estudiante_familiares), CRUD inline dentro del paso 4 del wizard
        Route::post('/perfil/familiares', [\App\Http\Controllers\Estudiantes\EstudianteFamiliarController::class, 'store'])->name('familiares.store');
        Route::put('/perfil/familiares/{familiar}', [\App\Http\Controllers\Estudiantes\EstudianteFamiliarController::class, 'update'])->name('familiares.update');
        Route::delete('/perfil/familiares/{familiar}', [\App\Http\Controllers\Estudiantes\EstudianteFamiliarController::class, 'destroy'])->name('familiares.destroy');

        Route::get('/notificaciones', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'notificaciones'])->name('notificaciones');
        Route::post('/notificaciones/read', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'readNotificaciones'])->name('notificaciones.read');
        Route::get('/notificaciones/recientes', [\App\Http\Controllers\Estudiantes\StudentPortalController::class, 'recientesNotificaciones'])->name('notificaciones.recientes');
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

        // Gestión de Periodos (CRUD completo, ver PeriodoAcademicoController).
        // Reemplaza la antigua pantalla "Fechas y Convocatorias": aquí se crea
        // y edita el periodo junto con sus fechas límite de sílabos/informes.
        Route::get('/periodos', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'index'])->name('periodos.index');
        Route::get('/periodos/crear', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'create'])->name('periodos.create');
        Route::post('/periodos', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'store'])->name('periodos.store');
        Route::get('/periodos/{periodo}', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'show'])->name('periodos.show');
        Route::get('/periodos/{periodo}/editar', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'edit'])->name('periodos.edit');
        Route::put('/periodos/{periodo}', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'update'])->name('periodos.update');
        Route::post('/periodos/{periodo}/activar', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'activate'])->name('periodos.activate');
        Route::post('/periodos/{periodo}/finalizar', [\App\Http\Controllers\Admin\PeriodoAcademicoController::class, 'finalize'])->name('periodos.finalize');

        // Datos adicionales de un estudiante (solo lectura): se llega desde
        // Admin/Usuarios/Show.vue con el botón "Ver Datos Adicionales", no
        // desde un listado propio. {estudiante} llega ya resuelto por route
        // model binding al mismo PerfilEstudianteController que usa el
        // wizard de autoservicio.
        Route::get('/estudiantes/perfiles/{estudiante}', [\App\Http\Controllers\Estudiantes\PerfilEstudianteController::class, 'show'])->name('estudiantes.perfiles.show');
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

        // Revisión de Sílabos: se movió a coordinador.silabos.* (ver más abajo,
        // grupo "COORDINADOR - Revisión de Sílabos"). Secretaría ya no tiene
        // acceso a este módulo.

        // Revisión de Informes de Asignatura (mismo patrón que Sílabos)
        Route::get('/informes', [\App\Http\Controllers\Secretaria\InformeRevisionController::class, 'index'])
            ->name('informes.index');
        Route::get('/informes/{informe}', [\App\Http\Controllers\Secretaria\InformeRevisionController::class, 'show'])
            ->name('informes.show');
        Route::patch('/informes/{informe}/review', [\App\Http\Controllers\Secretaria\InformeRevisionController::class, 'review'])
            ->name('informes.review');
        Route::get('/informes/{informe}/ver', [\App\Http\Controllers\Secretaria\InformeRevisionController::class, 'ver'])
            ->name('informes.ver');

        // Asignación de Docentes (vincula docente + materia + período + grupo;
        // alimenta Mis Sílabos/Mis Informes/Mis Estudiantes/Titulación del docente)
        Route::get('/asignaciones-docente', [\App\Http\Controllers\Secretaria\AsignacionDocenteController::class, 'index'])
            ->name('asignaciones-docente.index');
        Route::get('/asignaciones-docente/crear', [\App\Http\Controllers\Secretaria\AsignacionDocenteController::class, 'create'])
            ->name('asignaciones-docente.create');
        Route::post('/asignaciones-docente', [\App\Http\Controllers\Secretaria\AsignacionDocenteController::class, 'store'])
            ->name('asignaciones-docente.store');
        Route::get('/asignaciones-docente/{asignacion}/editar', [\App\Http\Controllers\Secretaria\AsignacionDocenteController::class, 'edit'])
            ->name('asignaciones-docente.edit');
        Route::put('/asignaciones-docente/{asignacion}', [\App\Http\Controllers\Secretaria\AsignacionDocenteController::class, 'update'])
            ->name('asignaciones-docente.update');
        Route::delete('/asignaciones-docente/{asignacion}', [\App\Http\Controllers\Secretaria\AsignacionDocenteController::class, 'destroy'])
            ->name('asignaciones-docente.destroy');

        // Plantillas de Documentos (formatos institucionales que sube
        // Secretaría y cualquier estudiante puede descargar desde su portal;
        // ver también la ruta compartida plantillas.descargar más abajo).
        Route::get('/plantillas', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'index'])
            ->name('plantillas.index');
        Route::get('/plantillas/crear', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'create'])
            ->name('plantillas.create');
        Route::post('/plantillas', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'store'])
            ->name('plantillas.store');
        Route::get('/plantillas/{plantilla}/editar', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'edit'])
            ->name('plantillas.edit');
        Route::put('/plantillas/{plantilla}', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'update'])
            ->name('plantillas.update');
        Route::delete('/plantillas/{plantilla}', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'destroy'])
            ->name('plantillas.destroy');
    });

// Descarga de Plantillas de Documentos: compartida entre Secretaría (las
// administra) y cualquier estudiante (las descarga desde su portal, ver
// student.plantillas). No es información sensible por estudiante, así que
// basta con estar autenticado (sin restricción de rol).
Route::middleware(['auth', 'verified'])
    ->get('/plantillas/{plantilla}/descargar', [\App\Http\Controllers\Secretaria\PlantillaDocumentoController::class, 'descargar'])
    ->name('plantillas.descargar');

// Archivos SENSIBLES por estudiante (expediente y adjuntos de justificaciones):
// datos personales servidos desde el disco privado con control de acceso en el
// controlador (dueño o Secretaría/SystemAdministrador). No son un simple gate
// por rol, por eso solo llevan 'auth'/'verified' aquí.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/documentos/{documento}/archivo', [\App\Http\Controllers\Documentos\DocumentoEstudianController::class, 'documento'])
        ->name('documentos.archivo');
    Route::get('/justificaciones/{justificacion}/archivo', [\App\Http\Controllers\Documentos\DocumentoEstudianController::class, 'justificacion'])
        ->name('justificaciones.archivo');
});


// ARCHIVOS EXTRA

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';