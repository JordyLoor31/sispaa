<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Estudiantes\Falta;
use App\Models\Estudiantes\JustificacionSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vistas de gestión/staff sobre estudiantes: listado de matriculados,
 * reporte de faltas y reporte de justificaciones (solo lectura; la
 * aprobación/rechazo de justificaciones vive en Secretaría).
 *
 * Alcance por rol: coordinador, secretaría y SystemAdministrador ven todo
 * lo general (incluye Matriculados). Un docente que NO tenga también uno
 * de esos roles solo ve, en Faltas/Justificaciones, a los estudiantes de
 * las materias que tiene asignadas en asignaciones_docente; Matriculados
 * le queda bloqueado por ser información institucional general.
 */
class EstudianteController extends Controller
{
    use HasBreadcrumbs;

    /**
     * true si el usuario solo tiene el rol docente (sin coordinador,
     * secretaría ni SystemAdministrador): dispara el alcance acotado a sus
     * propias materias en vez del listado general.
     */
    private function esSoloDocente(): bool
    {
        $user = Auth::user();

        return $user->hasRole('docente') && !$user->hasAnyRole(['coordinador', 'secretaria', 'SystemAdministrador']);
    }

    /**
     * IDs de las materias asignadas al docente autenticado (cualquier
     * período), usados para acotar Faltas/Justificaciones.
     */
    private function materiaIdsDelDocente(): array
    {
        return AsignacionDocente::where('docente_id', Auth::id())->pluck('materia_id')->all();
    }

    public function index(): Response
    {
        return Inertia::render('Estudiantes/Index');
    }

    public function matriculados(Request $request): Response
    {
        abort_if($this->esSoloDocente(), 403, 'El listado general de estudiantes matriculados es exclusivo de Secretaría, Coordinación y SystemAdministrador.');

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
            ->leftJoin('faltas', 'faltas.estudiante_id', '=', 'users.id')
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
                DB::raw('COUNT(DISTINCT faltas.id) as faltas_count'),
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

    /**
     * Reporte institucional de faltas (solo lectura): quién faltó, en qué
     * materia/período y si ya cuenta con una solicitud de justificación.
     */
    public function faltas(Request $request): Response
    {
        $carreraId = $request->input('carrera_id');
        $periodoId = $request->input('periodo_id');
        $justificada = $request->input('justificada');

        $query = Falta::with(['estudiante:id,name,email,carrera_id', 'estudiante.carrera:id,nombre', 'materia:id,nombre', 'periodo:id,nombre', 'justificacion']);

        // Docente sin otro rol de staff: solo las materias que tiene asignadas.
        if ($this->esSoloDocente()) {
            $query->whereIn('materia_id', $this->materiaIdsDelDocente());
        }

        if ($carreraId) {
            $query->whereHas('estudiante', fn ($q) => $q->where('carrera_id', $carreraId));
        }
        if ($periodoId) {
            $query->where('periodo_id', $periodoId);
        }
        if ($justificada !== null && $justificada !== '') {
            $query->where('justificada', $justificada === '1' || $justificada === 'true');
        }

        $faltas = $query->orderByDesc('fecha')->paginate(15)->withQueryString();

        $faltas->getCollection()->transform(fn ($f) => [
            'id' => $f->id,
            'fecha' => $f->fecha?->format('Y-m-d'),
            'estudiante' => $f->estudiante ? ['id' => $f->estudiante->id, 'name' => $f->estudiante->name] : null,
            'carrera' => $f->estudiante?->carrera?->nombre,
            'materia' => $f->materia?->nombre,
            'periodo' => $f->periodo?->nombre,
            'justificada' => $f->justificada,
            'motivo' => $f->motivo,
            'solicitud_estado' => $f->justificacion?->estado,
        ]);

        return Inertia::render('Estudiantes/Faltas/Index', [
            'faltas' => $faltas,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'periodos' => PeriodoAcademico::orderByDesc('id')->get(['id', 'nombre']),
            'filters' => ['carrera_id' => $carreraId, 'periodo_id' => $periodoId, 'justificada' => $justificada],
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Faltas'),
        ]);
    }

    /**
     * Reporte de solicitudes de justificación (solo lectura). La
     * aprobación/rechazo se hace desde la cola de Secretaría.
     */
    public function justificaciones(Request $request): Response
    {
        $estado = $request->input('estado');

        $query = JustificacionSolicitud::with(['falta.estudiante:id,name', 'falta.materia:id,nombre']);

        // Docente sin otro rol de staff: solo justificaciones de faltas en sus materias.
        if ($this->esSoloDocente()) {
            $materiaIds = $this->materiaIdsDelDocente();
            $query->whereHas('falta', fn ($q) => $q->whereIn('materia_id', $materiaIds));
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $solicitudes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $solicitudes->getCollection()->transform(fn ($s) => [
            'id' => $s->id,
            'estudiante' => $s->falta?->estudiante?->name,
            'materia' => $s->falta?->materia?->nombre,
            'fecha_falta' => $s->falta?->fecha?->format('Y-m-d'),
            'motivo_estudiante' => $s->motivo_estudiante,
            'estado' => $s->estado,
            'comentario_revisor' => $s->comentario_revisor,
            'created_at' => $s->created_at?->format('Y-m-d'),
        ]);

        return Inertia::render('Estudiantes/Justificaciones/Index', [
            'solicitudes' => $solicitudes,
            'filters' => ['estado' => $estado],
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Justificaciones'),
        ]);
    }
}
