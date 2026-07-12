<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Laboratorio\Equipo;
use App\Models\Laboratorio\PracticaLaboratorio;
use App\Models\Laboratorio\Reactivo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaboratorioReporteController extends Controller
{
    /**
     * Reporte estadístico de Laboratorio: prácticas por carrera/laboratorio,
     * y estado de inventario (equipos y reactivos).
     */
    public function index(Request $request): Response
    {
        $periodoId = $request->input('periodo_id', 'all');
        $carreraId = $request->input('carrera_id', 'all');

        $base = PracticaLaboratorio::query()->join('materias', 'materias.id', '=', 'practicas_laboratorio.materia_id');
        if ($periodoId !== 'all') {
            $base->where('practicas_laboratorio.periodo_id', $periodoId);
        }
        if ($carreraId !== 'all') {
            $base->where('materias.carrera_id', $carreraId);
        }

        $porCarrera = (clone $base)
            ->join('carreras', 'carreras.id', '=', 'materias.carrera_id')
            ->selectRaw('carreras.nombre as label, count(*) as total')
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        $porLaboratorio = (clone $base)
            ->join('laboratorios', 'laboratorios.id', '=', 'practicas_laboratorio.laboratorio_id')
            ->selectRaw('laboratorios.nombre as label, count(*) as total')
            ->groupBy('laboratorios.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $equiposPorEstado = Equipo::query()
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        $reactivosPorEstado = Reactivo::query()
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        return Inertia::render('Reportes/Laboratorio', [
            'kpis' => [
                'total_practicas' => (clone $base)->count(),
                'total_equipos' => Equipo::count(),
                'total_reactivos' => Reactivo::count(),
            ],
            'charts' => [
                'practicasPorCarrera' => ['labels' => $porCarrera->pluck('label'), 'series' => $porCarrera->pluck('total')],
                'practicasPorLaboratorio' => ['labels' => $porLaboratorio->pluck('label'), 'series' => $porLaboratorio->pluck('total')],
                'equiposPorEstado' => ['labels' => $equiposPorEstado->pluck('label'), 'series' => $equiposPorEstado->pluck('total')],
                'reactivosPorEstado' => ['labels' => $reactivosPorEstado->pluck('label'), 'series' => $reactivosPorEstado->pluck('total')],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId, 'carrera_id' => $carreraId],
        ]);
    }
}
