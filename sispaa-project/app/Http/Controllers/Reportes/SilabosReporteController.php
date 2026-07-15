<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\Silabo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SilabosReporteController extends Controller
{
    /**
     * Reporte estadístico de Sílabos: por estado, por carrera y por
     * período académico. Agregado en BD vía joins con materias/carreras.
     */
    public function index(Request $request): Response
    {
        $periodoId = $request->input('periodo_id', 'all');
        $carreraId = $request->input('carrera_id', 'all');

        $base = Silabo::query()->join('materias', 'materias.id', '=', 'silabos.materia_id');
        if ($periodoId !== 'all') {
            $base->where('silabos.periodo_id', $periodoId);
        }
        if ($carreraId !== 'all') {
            $base->where('materias.carrera_id', $carreraId);
        }

        $porEstado = (clone $base)
            ->selectRaw('silabos.estado as label, count(*) as total')
            ->groupBy('silabos.estado')
            ->get();

        $porCarrera = (clone $base)
            ->join('carreras', 'carreras.id', '=', 'materias.carrera_id')
            ->selectRaw('carreras.nombre as label, carreras.color as color, count(*) as total')
            ->groupBy('carreras.nombre', 'carreras.color')
            ->orderByDesc('total')
            ->get();

        $porPeriodo = (clone $base)
            ->join('periodos_academicos', 'periodos_academicos.id', '=', 'silabos.periodo_id')
            ->selectRaw('periodos_academicos.nombre as label, count(*) as total')
            ->groupBy('periodos_academicos.nombre', 'periodos_academicos.fecha_inicio')
            ->orderBy('periodos_academicos.fecha_inicio')
            ->get();

        $total = (clone $base)->count();
        $aprobados = (clone $base)->where('silabos.estado', 'aprobado')->count();

        return Inertia::render('Reportes/Silabos', [
            'kpis' => [
                'total' => $total,
                'aprobados' => $aprobados,
                'porcentaje_aprobados' => $total > 0 ? round(($aprobados / $total) * 100) : 0,
            ],
            'charts' => [
                'porEstado' => ['labels' => $porEstado->pluck('label'), 'series' => $porEstado->pluck('total')],
                'porCarrera' => ['labels' => $porCarrera->pluck('label'), 'series' => $porCarrera->pluck('total'), 'colors' => $porCarrera->pluck('color')],
                'porPeriodo' => ['labels' => $porPeriodo->pluck('label'), 'series' => $porPeriodo->pluck('total')],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId, 'carrera_id' => $carreraId],
        ]);
    }
}
