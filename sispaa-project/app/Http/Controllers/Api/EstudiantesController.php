<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudiantesController extends Controller
{
    public function index(Request $request)
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

        $result = $base->orderBy('users.name')->paginate($perPage);

        return response()->json($result);
    }
}
