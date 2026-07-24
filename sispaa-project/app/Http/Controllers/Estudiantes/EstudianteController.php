<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vistas de gestión/staff sobre estudiantes: listado de matriculados y
 * panel general. El reporte de faltas ahora es agregado por carrera (ver
 * Secretaria\FaltaSemanalController y Reportes\EstudiantesReporteController),
 * no hay más un listado individual de faltas/justificaciones por estudiante.
 */
class EstudianteController extends Controller
{
    use HasBreadcrumbs;

    /**
     * Panel de estudiantes: tarjetas + gráficos con datos reales (antes
     * eran valores fijos de ejemplo). El estado por estudiante reutiliza el
     * mismo criterio de agregación que matriculados() (MAX(matriculas.estado)
     * entre todos sus períodos), para que los dos módulos sean consistentes.
     */
    public function index(): Response
    {
        $porEstudiante = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('matriculas', 'matriculas.estudiante_id', '=', 'users.id')
            ->where('roles.name', 'estudiante')
            ->select(
                'users.id',
                DB::raw('MAX(matriculas.estado) as matricula_estado')
            )
            ->groupBy('users.id')
            ->get();

        $totalEstudiantes = $porEstudiante->count();
        $retirados = $porEstudiante->where('matricula_estado', 'retirado')->count();
        $activos = max(0, $totalEstudiantes - $retirados);

        $porCarrera = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('carreras', 'carreras.id', '=', 'users.carrera_id')
            ->where('roles.name', 'estudiante')
            ->select('carreras.nombre as carrera', DB::raw('COUNT(*) as total'))
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        $ultimosRegistros = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('carreras', 'carreras.id', '=', 'users.carrera_id')
            ->leftJoin('matriculas', 'matriculas.estudiante_id', '=', 'users.id')
            ->where('roles.name', 'estudiante')
            ->select(
                'users.id',
                'users.name',
                'carreras.nombre as carrera',
                DB::raw('MAX(matriculas.estado) as estado'),
                DB::raw('MAX(matriculas.fecha_matricula) as ultima_matricula')
            )
            ->groupBy('users.id', 'users.name', 'carreras.nombre')
            ->orderByDesc('users.id')
            ->limit(5)
            ->get()
            ->map(function ($u) {
                $estadoLabel = match (true) {
                    $u->estado === 'retirado' => 'Retirado',
                    $u->estado === 'egresado' => 'Egresado',
                    $u->estado === 'activo' => 'Activo',
                    default => 'Sin matrícula',
                };

                return [
                    'name' => $u->name,
                    'career' => $u->carrera ?? '—',
                    'status' => $estadoLabel,
                    'lastActivity' => $u->ultima_matricula
                        ? 'Matriculado: ' . \Illuminate\Support\Carbon::parse($u->ultima_matricula)->format('d/m/Y')
                        : 'Sin actividad reciente',
                ];
            });

        return Inertia::render('Estudiantes/Index', [
            'stats' => [
                'total' => $totalEstudiantes,
                'activos' => $activos,
            ],
            'estadoMatricula' => [
                'activos' => $activos,
                'retirados' => $retirados,
            ],
            'porCarrera' => $porCarrera,
            'ultimosRegistros' => $ultimosRegistros,
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Panel Estudiantes'),
        ]);
    }

    public function matriculados(Request $request): Response
    {
        // Exclusivo de Secretaría/SystemAdministrador: ni coordinador ni
        // docente ven el listado general de matriculados (a pedido, para no
        // duplicar el acceso institucional que ya administra Secretaría).
        abort_unless(
            Auth::user()->hasAnyRole(['secretaria', 'SystemAdministrador']),
            403,
            'El listado general de estudiantes matriculados es exclusivo de Secretaría y SystemAdministrador.'
        );

        $perPage = (int) $request->input('per_page', 12);
        $perPage = $perPage > 0 ? min(100, $perPage) : 12;

        $carreraId = $request->input('carrera_id');
        $periodoId = $request->input('periodo_id');
        $estado = $request->input('estado');
        $q = $request->input('q');

        $base = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('matriculas', 'matriculas.estudiante_id', '=', 'users.id')
            ->leftJoin('carreras', 'carreras.id', '=', 'users.carrera_id')
            ->leftJoin('documentos_estudiante', 'documentos_estudiante.estudiante_id', '=', 'users.id')
            ->where('roles.name', 'estudiante')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.cedula',
                'users.telefono',
                'users.carrera_id',
                'carreras.nombre as carrera_nombre',
                DB::raw('MAX(matriculas.estado) as matricula_estado'),
                DB::raw('COUNT(DISTINCT documentos_estudiante.id) as documentos_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.cedula', 'users.telefono', 'users.carrera_id', 'carreras.nombre');

        if ($carreraId) {
            $base->where('users.carrera_id', $carreraId);
        }

        if ($periodoId) {
            $base->where('matriculas.periodo_id', $periodoId);
        }

        if ($estado) {
            $base->where('matriculas.estado', $estado);
        }

        if ($q) {
            $base->where(function ($w) use ($q) {
                $w->where('users.name', 'ilike', "%{$q}%")
                  ->orWhere('users.email', 'ilike', "%{$q}%")
                  ->orWhere('users.cedula', 'ilike', "%{$q}%");
            });
        }

        $students = $base->orderBy('users.name')->paginate($perPage)->withQueryString();

        return Inertia::render('Estudiantes/Matriculados/Index', [
            'students' => $students,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['carrera_id' => $carreraId, 'periodo_id' => $periodoId, 'estado' => $estado, 'q' => $q],
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Matriculados'),
        ]);
    }
}
