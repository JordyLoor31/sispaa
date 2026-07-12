<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\Falta;
use App\Models\Estudiantes\JustificacionSolicitud;
use App\Models\Estudiantes\Matricula;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EstudiantesReporteController extends Controller
{
    /**
     * Reporte estadístico de Estudiantes: matriculados por carrera/estado,
     * faltas por materia y justificaciones por estado. Todo agregado en BD
     * (counts agrupados) para que los gráficos no dependan de traer y
     * procesar colecciones completas en PHP.
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

        $faltasQuery = Falta::query();
        if ($periodoId !== 'all') {
            $faltasQuery->where('periodo_id', $periodoId);
        }

        // Matriculados por carrera
        $porCarrera = (clone $matriculasQuery)
            ->join('carreras', 'carreras.id', '=', 'matriculas.carrera_id')
            ->selectRaw('carreras.nombre as label, count(*) as total')
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        // Matriculados por estado
        $porEstado = (clone $matriculasQuery)
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        // Faltas por materia (top 10)
        $faltasPorMateria = (clone $faltasQuery)
            ->join('materias', 'materias.id', '=', 'faltas.materia_id')
            ->selectRaw('materias.nombre as label, count(*) as total')
            ->groupBy('materias.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Faltas justificadas vs no justificadas
        $faltasJustificadas = (clone $faltasQuery)->where('justificada', true)->count();
        $faltasSinJustificar = (clone $faltasQuery)->where('justificada', false)->count();

        // Justificaciones por estado (solicitudes)
        $justificacionesPorEstado = JustificacionSolicitud::query()
            ->when($periodoId !== 'all', function ($q) use ($periodoId) {
                $q->whereHas('falta', fn ($f) => $f->where('periodo_id', $periodoId));
            })
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        $totalMatriculados = (clone $matriculasQuery)->count();
        $totalFaltas = (clone $faltasQuery)->count();

        return Inertia::render('Reportes/Estudiantes', [
            'kpis' => [
                'total_matriculados' => $totalMatriculados,
                'total_faltas' => $totalFaltas,
                'faltas_justificadas' => $faltasJustificadas,
                'faltas_sin_justificar' => $faltasSinJustificar,
                'porcentaje_justificadas' => $totalFaltas > 0 ? round(($faltasJustificadas / $totalFaltas) * 100) : 0,
            ],
            'charts' => [
                'matriculadosPorCarrera' => [
                    'labels' => $porCarrera->pluck('label'),
                    'series' => $porCarrera->pluck('total'),
                ],
                'matriculadosPorEstado' => [
                    'labels' => $porEstado->pluck('label'),
                    'series' => $porEstado->pluck('total'),
                ],
                'faltasPorMateria' => [
                    'labels' => $faltasPorMateria->pluck('label'),
                    'series' => $faltasPorMateria->pluck('total'),
                ],
                'faltasJustificadas' => [
                    'labels' => ['Justificadas', 'Sin justificar'],
                    'series' => [$faltasJustificadas, $faltasSinJustificar],
                ],
                'justificacionesPorEstado' => [
                    'labels' => $justificacionesPorEstado->pluck('label'),
                    'series' => $justificacionesPorEstado->pluck('total'),
                ],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId, 'carrera_id' => $carreraId],
        ]);
    }
}
