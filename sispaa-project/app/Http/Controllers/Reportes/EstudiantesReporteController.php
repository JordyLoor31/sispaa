<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\FaltaSemanal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EstudiantesReporteController extends Controller
{
    /**
     * Reporte estadístico de Estudiantes: estudiantes del sistema por carrera
     * y faltas semanales por carrera (registradas manualmente por Secretaría).
     * Todo agregado en BD (counts/sums agrupados) para que los gráficos no
     * dependan de traer y procesar colecciones completas en PHP. Las
     * matrículas se retiraron del sistema.
     */
    public function index(Request $request): Response
    {
        $carreraId = $request->input('carrera_id', 'all');

        $estudiantesQuery = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'estudiante');

        if ($carreraId !== 'all') {
            $estudiantesQuery->where('users.carrera_id', $carreraId);
        }

        $faltasQuery = FaltaSemanal::query();
        if ($request->input('periodo_id') !== 'all') {
            $faltasQuery->where('periodo_id', $request->input('periodo_id'));
        }
        if ($carreraId !== 'all') {
            $faltasQuery->where('carrera_id', $carreraId);
        }

        // Estudiantes del sistema por carrera
        $porCarrera = (clone $estudiantesQuery)
            ->join('carreras', 'carreras.id', '=', 'users.carrera_id')
            ->selectRaw('carreras.nombre as label, carreras.color as color, count(*) as total')
            ->groupBy('carreras.nombre', 'carreras.color')
            ->orderByDesc('total')
            ->get();

        // Faltas semanales por carrera (suma de lo registrado por Secretaría)
        $faltasPorCarrera = (clone $faltasQuery)
            ->join('carreras', 'carreras.id', '=', 'faltas_semanales_carrera.carrera_id')
            ->selectRaw('carreras.nombre as label, carreras.color as color, sum(cantidad_faltas) as total')
            ->groupBy('carreras.nombre', 'carreras.color')
            ->orderByDesc('total')
            ->get();

        $totalEstudiantes = (clone $estudiantesQuery)->count();
        $totalFaltas = (clone $faltasQuery)->sum('cantidad_faltas');

        return Inertia::render('Reportes/Estudiantes', [
            'kpis' => [
                'total_estudiantes' => $totalEstudiantes,
                'total_faltas' => $totalFaltas,
            ],
            'charts' => [
                'estudiantesPorCarrera' => [
                    'labels' => $porCarrera->pluck('label'),
                    'series' => $porCarrera->pluck('total'),
                    'colors' => $porCarrera->pluck('color'),
                ],
                'faltasPorCarrera' => [
                    'labels' => $faltasPorCarrera->pluck('label'),
                    'series' => $faltasPorCarrera->pluck('total'),
                    'colors' => $faltasPorCarrera->pluck('color'),
                ],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $request->input('periodo_id', 'all'), 'carrera_id' => $carreraId],
        ]);
    }
}
