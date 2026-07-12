<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\InformeDocente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InformesReporteController extends Controller
{
    /**
     * Reporte estadístico de Informes de Asignatura: por estado, por
     * carrera y por período académico (mismo patrón que Sílabos).
     */
    public function index(Request $request): Response
    {
        $periodoId = $request->input('periodo_id', 'all');
        $carreraId = $request->input('carrera_id', 'all');

        $base = InformeDocente::query()->join('materias', 'materias.id', '=', 'informes_docente.materia_id');
        if ($periodoId !== 'all') {
            $base->where('informes_docente.periodo_id', $periodoId);
        }
        if ($carreraId !== 'all') {
            $base->where('materias.carrera_id', $carreraId);
        }

        $porEstado = (clone $base)
            ->selectRaw('informes_docente.estado as label, count(*) as total')
            ->groupBy('informes_docente.estado')
            ->get();

        $porCarrera = (clone $base)
            ->join('carreras', 'carreras.id', '=', 'materias.carrera_id')
            ->selectRaw('carreras.nombre as label, count(*) as total')
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        $porPeriodo = (clone $base)
            ->join('periodos_academicos', 'periodos_academicos.id', '=', 'informes_docente.periodo_id')
            ->selectRaw('periodos_academicos.nombre as label, count(*) as total')
            ->groupBy('periodos_academicos.nombre', 'periodos_academicos.fecha_inicio')
            ->orderBy('periodos_academicos.fecha_inicio')
            ->get();

        $total = (clone $base)->count();
        $aprobados = (clone $base)->where('informes_docente.estado', 'aprobado')->count();

        return Inertia::render('Reportes/Informes', [
            'kpis' => [
                'total' => $total,
                'aprobados' => $aprobados,
                'porcentaje_aprobados' => $total > 0 ? round(($aprobados / $total) * 100) : 0,
            ],
            'charts' => [
                'porEstado' => ['labels' => $porEstado->pluck('label'), 'series' => $porEstado->pluck('total')],
                'porCarrera' => ['labels' => $porCarrera->pluck('label'), 'series' => $porCarrera->pluck('total')],
                'porPeriodo' => ['labels' => $porPeriodo->pluck('label'), 'series' => $porPeriodo->pluck('total')],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId, 'carrera_id' => $carreraId],
        ]);
    }
}
