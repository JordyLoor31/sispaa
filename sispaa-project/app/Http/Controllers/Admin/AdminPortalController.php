<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\Materia;
use App\Models\Docencia\InformeDocente;
use App\Models\Estudiantes\Matricula;
use App\Models\Investigacion\HitoInvestigacion;
use App\Models\Investigacion\Investigacion;
use App\Models\Laboratorio\Equipo;
use App\Models\Laboratorio\PracticaLaboratorio;
use App\Models\Laboratorio\Reactivo;
use App\Models\Titulacion\Titulacion;
use App\Models\User;
use App\Models\Vinculacion\ActividadVinculacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminPortalController extends Controller
{
    use HasBreadcrumbs;

    /**
     * Dashboard / Métricas Globales
     */
    public function dashboard(): Response
    {
        // 1. Cumplimiento docente (informes subidos/aprobados de total esperados)
        $totalInformes = InformeDocente::count();
        $informesEntregados = InformeDocente::whereIn('estado', ['subido', 'aprobado'])->count();
        $cumplimientoDocente = $totalInformes > 0 ? round(($informesEntregados / $totalInformes) * 100) : 0;

        // 2. Deserción estudiantil
        $totalMatriculados = Matricula::count();
        $totalRetirados = Matricula::where('estado', 'retirado')->count();
        $desercionEstudiantil = $totalMatriculados > 0 ? round(($totalRetirados / $totalMatriculados) * 100, 2) : 0;

        // 3. Inventario de Laboratorio (real, ya no simulado)
        $inventarioStats = [
            'total_equipos' => Equipo::count(),
            'total_reactivos' => Reactivo::count(),
            'reactivos_bajo_stock' => Reactivo::where('estado', 'agotado')->orWhere('cantidad', '<=', 5)->count(),
        ];

        // 4. Investigación
        $totalHitosInvestigacion = HitoInvestigacion::count();
        $investigacionStats = [
            'total_proyectos' => Investigacion::count(),
            'en_curso' => Investigacion::where('estado', 'en_curso')->count(),
            'finalizados' => Investigacion::where('estado', 'finalizada')->count(),
            'hitos_completados' => HitoInvestigacion::where('porcentaje_avance', 100)->count(),
            'total_hitos' => $totalHitosInvestigacion,
        ];

        // 5. Prácticas de Laboratorio
        $laboratorioStats = [
            'total_practicas' => PracticaLaboratorio::count(),
            'estudiantes_atendidos' => (int) PracticaLaboratorio::sum('numero_estudiantes'),
        ];

        // 6. Vinculación
        $vinculacionStats = [
            'total_actividades' => ActividadVinculacion::count(),
            'ejecutadas' => ActividadVinculacion::where('estado', 'ejecutado')->count(),
            'pendientes' => ActividadVinculacion::where('estado', 'pendiente')->count(),
        ];

        // 7. Titulación
        $titulacionStats = [
            'total' => Titulacion::count(),
            'en_proceso' => Titulacion::where('estado', 'en_proceso')->count(),
            'defendido' => Titulacion::where('estado', 'defendido')->count(),
            'graduado' => Titulacion::where('estado', 'graduado')->count(),
        ];

        // 8. Estadísticas generales
        $stats = [
            'cumplimiento_docente' => $cumplimientoDocente,
            'total_informes' => $totalInformes,
            'informes_entregados' => $informesEntregados,
            'desercion_estudiantil' => $desercionEstudiantil,
            'total_estudiantes' => User::role('estudiante')->count(),
            'total_docentes' => User::role('docente')->count(),
            'total_carreras' => Carrera::count(),
            'total_materias' => Materia::count(),
            'inventario' => $inventarioStats,
            'investigacion' => $investigacionStats,
            'laboratorio' => $laboratorioStats,
            'vinculacion' => $vinculacionStats,
            'titulacion' => $titulacionStats,
        ];

        // Cumplimiento por carreras
        $carreras = Carrera::all();
        $cumplimientoPorCarrera = [];
        foreach ($carreras as $carrera) {
            $totalCarrera = InformeDocente::whereHas('materia', function($q) use ($carrera) {
                $q->where('carrera_id', $carrera->id);
            })->count();
            
            $entregadosCarrera = InformeDocente::whereIn('estado', ['subido', 'aprobado'])
                ->whereHas('materia', function($q) use ($carrera) {
                    $q->where('carrera_id', $carrera->id);
                })->count();

            $pct = $totalCarrera > 0 ? round(($entregadosCarrera / $totalCarrera) * 100) : 0;
            $cumplimientoPorCarrera[] = [
                'carrera' => $carrera->nombre,
                'codigo' => $carrera->codigo,
                'porcentaje' => $pct
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'cumplimientoPorCarrera' => $cumplimientoPorCarrera
        ]);
    }

    /**
     * Malla Curricular: Asignaturas (la gestión de Carreras vive en
     * CarreraController, separada para seguir el patrón Index/Create/Edit/Show).
     */
    public function materiasIndex(Request $request): Response
    {
        $carreraId = $request->input('carrera_id');
        $q = $request->input('q');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min(100, $perPage) : 10;

        $carreras = Carrera::orderBy('nombre')->get(['id', 'nombre']);

        $query = Materia::with('carrera');
        if ($carreraId && $carreraId !== 'all') {
            $query->where('carrera_id', $carreraId);
        }

        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%");
            });
        }

        $materias = $query->orderBy('nivel')->orderBy('nombre')->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Materias/Index', [
            'carreras' => $carreras,
            'materias' => $materias,
            'filters' => $request->only(['carrera_id', 'q', 'per_page']),
            'breadcrumbs' => $this->adminBreadcrumbs('Asignaturas'),
        ]);
    }

    public function materiaStore(Request $request)
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:materias',
            'creditos' => 'required|integer|min:1',
            'nivel' => 'required|integer|min:1|max:10',
        ]);

        Materia::create([
            'carrera_id' => $request->carrera_id,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'creditos' => $request->creditos,
            'nivel' => $request->nivel,
            'activa' => true,
        ]);

        return redirect()->back()->with('success', 'Asignatura creada correctamente.');
    }

    public function materiaUpdate(Request $request, Materia $materia)
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:materias,codigo,' . $materia->id,
            'creditos' => 'required|integer|min:1',
            'nivel' => 'required|integer|min:1|max:10',
        ]);

        $materia->update([
            'carrera_id' => $request->carrera_id,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'creditos' => $request->creditos,
            'nivel' => $request->nivel,
        ]);

        return redirect()->back()->with('success', 'Asignatura actualizada correctamente.');
    }

    public function materiaDestroy(Materia $materia)
    {
        $materia->delete();
        return redirect()->back()->with('success', 'Asignatura eliminada correctamente.');
    }

    public function materiaToggleStatus(Materia $materia)
    {
        $materia->update([
            'activa' => !$materia->activa
        ]);

        return redirect()->back()->with('success', 'Estado de la asignatura actualizado.');
    }

    /**
     * Fechas Límite y Convocatorias
     */
    public function fechasIndex(): Response
    {
        $periodos = PeriodoAcademico::orderBy('id', 'desc')->get();

        return Inertia::render('Admin/PeriodosAcademicos', [
            'periodos' => $periodos,
        ]);
    }

    public function periodoStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:periodos_academicos,nombre',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo' => 'required|in:semestral,anual',
        ]);

        // Un periodo es una sola entidad compartida por todas las carreras
        // (ej. "2026-1"), no un registro por carrera.
        PeriodoAcademico::create([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'tipo' => $request->tipo,
            'activo' => false,
        ]);

        return redirect()->back()->with('success', 'Periodo académico creado.');
    }

    public function periodoUpdate(Request $request, PeriodoAcademico $periodo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:periodos_academicos,nombre,' . $periodo->id,
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'required|boolean',
        ]);

        if ($request->activo) {
            // Solo un periodo puede estar activo a la vez a nivel institucional.
            PeriodoAcademico::where('id', '!=', $periodo->id)->update(['activo' => false]);
        }

        $periodo->update([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo,
        ]);

        return redirect()->back()->with('success', 'Periodo académico actualizado.');
    }

    public function periodoDeadlinesUpdate(Request $request, PeriodoAcademico $periodo)
    {
        $request->validate([
            'fecha_limite_silabo' => 'nullable|date',
            'fecha_limite_informe' => 'nullable|date',
        ]);

        $periodo->update([
            'fecha_limite_silabo' => $request->fecha_limite_silabo,
            'fecha_limite_informe' => $request->fecha_limite_informe,
        ]);

        return redirect()->back()->with('success', 'Fechas límites guardadas correctamente.');
    }

    /**
     * Informes de Asignatura (Paginado y Real)
     */
    public function informesAsignatura(Request $request): Response
    {
        $carreraId = $request->input('carrera_id');
        $estado = $request->input('estado');
        $perPage = (int) $request->input('per_page', 8);
        $perPage = $perPage > 0 ? min(100, $perPage) : 8;

        $query = InformeDocente::with(['docente', 'materia.carrera', 'periodo']);

        if ($carreraId && $carreraId !== 'all') {
            $query->whereHas('materia', function($q) use ($carreraId) {
                $q->where('carrera_id', $carreraId);
            });
        }

        if ($estado && $estado !== 'all') {
            // Mapear los estados del frontend ('Cumplido', 'Pendiente', 'Incumplido') a la DB
            // DB tiene ['pendiente', 'subido', 'aprobado']
            // Cumplido -> aprobado, Pendiente -> subido / pendiente
            if ($estado === 'Cumplido') {
                $query->where('estado', 'aprobado');
            } elseif ($estado === 'Pendiente') {
                $query->where('estado', 'subido');
            } elseif ($estado === 'Incumplido') {
                $query->where('estado', 'pendiente');
            }
        }

        $informesPaginated = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        $carreras = Carrera::all();

        return Inertia::render('Docencia/Informes', [
            'informesPaginated' => $informesPaginated,
            'carreras' => $carreras,
            'filters' => $request->only(['carrera_id', 'estado', 'per_page'])
        ]);
    }
}
