<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\FaltaSemanal;
use App\Models\Estudiantes\Matricula;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EstudiantesReporteController extends Controller
{
    /**
     * Reporte estadístico de Estudiantes: matriculados por carrera/estado y
     * faltas semanales por carrera (registradas manualmente por Secretaría,
     * ya no por estudiante individual). Todo agregado en BD (counts/sums
     * agrupados) para que los gráficos no dependan de traer y procesar
     * colecciones completas en PHP.
     */
    public function index(Request $request): Response
    {
        $periodoId = $request->input('periodo_id', 'all');
        $carreraId = $request->input('carrera_id', 'all');

        $matriculasQuery = Matricula::query();
        if ($periodoId !== 'all') {
            $matriculasQuery->where('periodo_id', $periodoId);
        }
        if ($carreraId !== 'all') {
            $matriculasQuery->where('carrera_id', $carreraId);
        }

        $faltasQuery = FaltaSemanal::query();
        if ($periodoId !== 'all') {
            $faltasQuery->where('periodo_id', $periodoId);
        }
        if ($carreraId !== 'all') {
            $faltasQuery->where('carrera_id', $carreraId);
        }

        // Matriculados por carrera
        $porCarrera = (clone $matriculasQuery)
            ->join('carreras', 'carreras.id', '=', 'matriculas.carrera_id')
            ->selectRaw('carreras.nombre as label, carreras.color as color, count(*) as total')
            ->groupBy('carreras.nombre', 'carreras.color')
            ->orderByDesc('total')
            ->get();

        // Matriculados por estado
        $porEstado = (clone $matriculasQuery)
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        // Faltas semanales por carrera (suma de lo registrado por Secretaría)
        $faltasPorCarrera = (clone $faltasQuery)
            ->join('carreras', 'carreras.id', '=', 'faltas_semanales_carrera.carrera_id')
            ->selectRaw('carreras.nombre as label, carreras.color as color, sum(cantidad_faltas) as total')
            ->groupBy('carreras.nombre', 'carreras.color')
            ->orderByDesc('total')
            ->get();

        $totalMatriculados = (clone $matriculasQuery)->count();
        $totalFaltas = (clone $faltasQuery)->sum('cantidad_faltas');

        return Inertia::render('Reportes/Estudiantes', [
            'kpis' => [
                'total_matriculados' => $totalMatriculados,
                'total_faltas' => $totalFaltas,
            ],
            'charts' => [
                'matriculadosPorCarrera' => [
                    'labels' => $porCarrera->pluck('label'),
                    'series' => $porCarrera->pluck('total'),
                    'colors' => $porCarrera->pluck('color'),
                ],
                'matriculadosPorEstado' => [
                    'labels' => $porEstado->pluck('label'),
                    'series' => $porEstado->pluck('total'),
                ],
                'faltasPorCarrera' => [
                    'labels' => $faltasPorCarrera->pluck('label'),
                    'series' => $faltasPorCarrera->pluck('total'),
                    'colors' => $faltasPorCarrera->pluck('color'),
                ],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId, 'carrera_id' => $carreraId],
        ]);
    }
}
