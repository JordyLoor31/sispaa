<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vistas de gestión/staff sobre estudiantes: listado institucional de los
 * estudiantes registrados en el sistema (las matrículas se retiraron del
 * sistema). El reporte de faltas ahora es agregado por carrera (ver
 * Secretaria\FaltaSemanalController y Reportes\EstudiantesReporteController).
 */
class EstudianteController extends Controller
{
    use HasBreadcrumbs;

    /**
     * Listado de estudiantes del sistema con búsqueda y filtro por carrera.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = $perPage > 0 ? min(100, $perPage) : 12;

        $carreraId = $request->input('carrera_id');
        $q = $request->input('q');

        $base = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
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
                DB::raw('COUNT(DISTINCT documentos_estudiante.id) as documentos_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.cedula', 'users.telefono', 'users.carrera_id', 'carreras.nombre');

        if ($carreraId) {
            $base->where('users.carrera_id', $carreraId);
        }

        if ($q) {
            $base->where(function ($w) use ($q) {
                $w->where('users.name', 'ilike', "%{$q}%")
                  ->orWhere('users.email', 'ilike', "%{$q}%")
                  ->orWhere('users.cedula', 'ilike', "%{$q}%");
            });
        }

        $students = $base->orderBy('users.name')->paginate($perPage)->withQueryString();

        return Inertia::render('Estudiantes/Index', [
            'students' => $students,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['carrera_id' => $carreraId, 'q' => $q],
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Estudiantes'),
        ]);
    }
}
