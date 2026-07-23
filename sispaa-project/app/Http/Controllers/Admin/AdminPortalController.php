<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
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

        // Informes vencidos: pendientes cuyo periodo ya superó la fecha límite de entrega
        $informesIncumplidos = InformeDocente::where('estado', 'pendiente')
            ->whereHas('periodo', function ($q) {
                $q->whereNotNull('fecha_limite_informe')->where('fecha_limite_informe', '<', now());
            })->count();
        $informesPendientes = InformeDocente::where('estado', 'pendiente')->count() - $informesIncumplidos;

        $docenciaStats = [
            'informes_cumplidos' => $informesEntregados,
            'informes_pendientes' => $informesPendientes,
            'informes_incumplidos' => $informesIncumplidos,
        ];

        // 2. Deserción estudiantil
        $totalMatriculados = Matricula::count();
        $totalRetirados = Matricula::where('estado', 'retirado')->count();
        $desercionEstudiantil = $totalMatriculados > 0 ? round(($totalRetirados / $totalMatriculados) * 100, 2) : 0;

        $estudiantesStats = [
            'matriculados' => $totalMatriculados,
            'activos' => Matricula::where('estado', 'activo')->count(),
            'retirados' => $totalRetirados,
            'egresados' => Matricula::where('estado', 'egresado')->count(),
        ];

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
        $practicasPorCarrera = PracticaLaboratorio::join('materias', 'materias.id', '=', 'practicas_laboratorio.materia_id')
            ->join('carreras', 'carreras.id', '=', 'materias.carrera_id')
            ->select('carreras.nombre as carrera', DB::raw('count(*) as total'))
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        $laboratorioStats = [
            'total_practicas' => PracticaLaboratorio::count(),
            'estudiantes_atendidos' => (int) PracticaLaboratorio::sum('numero_estudiantes'),
            'por_carrera' => $practicasPorCarrera,
        ];

        // 6. Vinculación
        $vinculacionStats = [
            'total_actividades' => ActividadVinculacion::count(),
            'ejecutadas' => ActividadVinculacion::where('estado', 'ejecutado')->count(),
            'pendientes' => ActividadVinculacion::where('estado', 'en_ejecucion')->count(),
            'empresas_beneficiadas' => ActividadVinculacion::whereNotNull('beneficiario_id')
                ->distinct('beneficiario_id')
                ->count('beneficiario_id'),
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
            'docencia' => $docenciaStats,
            'estudiantes' => $estudiantesStats,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
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

    // La supervisión de "Informes de Asignatura" (antes informesAsignatura()
    // aquí) se movió a Secretaria\InformeRevisionController, con el mismo
    // patrón Index+Show con revisión que Sílabos/Justificaciones. Ver
    // rutas secretaria.informes.*.
}
