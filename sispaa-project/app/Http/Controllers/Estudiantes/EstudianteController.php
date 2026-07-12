<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\Falta;
use App\Models\Estudiantes\JustificacionSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vistas de gestión/staff sobre estudiantes: listado de matriculados,
 * reporte de faltas y reporte de justificaciones (solo lectura; la
 * aprobación/rechazo de justificaciones vive en Secretaría).
 */
class EstudianteController extends Controller
{
    use HasBreadcrumbs;

    public function index(): Response
    {
        return Inertia::render('Estudiantes/Index');
    }

    public function matriculados(Request $request): Response
    {
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
