<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\Empresa;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Vinculacion\ActividadVinculacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VinculacionReporteController extends Controller
{
    /**
     * Reporte estadístico de Vinculación: actividades por estado/carrera y
     * empresas beneficiadas por sector.
     */
    public function index(Request $request): Response
    {
        $periodoId = $request->input('periodo_id', 'all');
        $carreraId = $request->input('carrera_id', 'all');

        $base = ActividadVinculacion::query();
        if ($periodoId !== 'all') {
            $base->where('periodo_id', $periodoId);
        }
        if ($carreraId !== 'all') {
            $base->where('carrera_id', $carreraId);
        }

        $porEstado = (clone $base)
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        $porCarrera = (clone $base)
            ->join('carreras', 'carreras.id', '=', 'actividades_vinculacion.carrera_id')
            ->selectRaw('carreras.nombre as label, carreras.color as color, count(*) as total')
            ->groupBy('carreras.nombre', 'carreras.color')
            ->orderByDesc('total')
            ->get();

        $empresasPorSector = Empresa::query()
            ->selectRaw("COALESCE(NULLIF(sector, ''), 'Sin sector') as label, count(*) as total")
            ->groupBy('sector')
            ->orderByDesc('total')
            ->get();

        $total = (clone $base)->count();
        $ejecutadas = (clone $base)->where('estado', 'ejecutado')->count();

        return Inertia::render('Reportes/Vinculacion', [
            'kpis' => [
                'total_actividades' => $total,
                'ejecutadas' => $ejecutadas,
                'porcentaje_ejecutadas' => $total > 0 ? round(($ejecutadas / $total) * 100) : 0,
                'total_empresas' => Empresa::count(),
            ],
            'charts' => [
                'porEstado' => ['labels' => $porEstado->pluck('label'), 'series' => $porEstado->pluck('total')],
                'porCarrera' => ['labels' => $porCarrera->pluck('label'), 'series' => $porCarrera->pluck('total'), 'colors' => $porCarrera->pluck('color')],
                'empresasPorSector' => ['labels' => $empresasPorSector->pluck('label'), 'series' => $empresasPorSector->pluck('total')],
            ],
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId, 'carrera_id' => $carreraId],
        ]);
    }
}
