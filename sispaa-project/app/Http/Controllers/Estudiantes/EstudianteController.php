<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
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

        $students = $base->orderBy('users.name')->paginate($perPage);

        // Si la consulta principal no devuelve resultados, intentar una consulta alternativa
        // que use whereExists sobre model_has_roles (a veces los joins con scopes o estructuras
        // complejas pueden filtrar registros inesperadamente).
        if ($students->total() === 0) {
            $alt = DB::table('users')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('model_has_roles')
                      ->whereColumn('model_has_roles.model_id', 'users.id')
                      ->where('model_has_roles.model_type', 'App\\Models\\User')
                      ->whereIn('model_has_roles.role_id', function ($qr) {
                          $qr->select('id')->from('roles')->where('name', 'estudiante');
                      });
                })
                ->select('users.id', 'users.name', 'users.email', 'users.cedula', 'users.telefono', 'users.carrera_id')
                ->orderBy('users.name')
                ->paginate($perPage);

            if ($alt->total() > 0) {
                // Map alt to include expected aggregate keys with defaults
                $students = $alt->through(function ($item) {
                    return (object) array_merge((array) $item, [
                        'carrera_nombre' => null,
                        'matricula_estado' => null,
                        'faltas_count' => 0,
                        'documentos_count' => 0,
                    ]);
                });
            }
        }

        $carreras = DB::table('carreras')->select('id', 'nombre')->get();

        return Inertia::render('Estudiantes/Matriculados', [
            'students' => $students,
            'carreras' => $carreras,
        ]);
    }

    public function faltas(): Response
    {
        return Inertia::render('Estudiantes/Faltas');
    }

    public function justificaciones(): Response
    {
        return Inertia::render('Estudiantes/Justificaciones');
    }

    public function apiList(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = $perPage > 0 ? min(100, $perPage) : 15;

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

        $result = $base->orderBy('users.name')->paginate($perPage);

        return response()->json($result);
    }
}
