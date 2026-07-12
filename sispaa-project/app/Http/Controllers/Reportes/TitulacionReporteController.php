<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Titulacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TitulacionReporteController extends Controller
{
    /**
     * Reporte estadístico de Titulación: por estado, por tutor (top 10) y
     * por año de inicio del proceso. No se filtra por carrera/período
     * porque el estudiante puede tener varias matrículas en el tiempo y
     * ese join duplicaría filas; se prioriza un conteo exacto.
     */
    public function index(Request $request): Response
    {
        $estado = $request->input('estado', 'all');

        $base = Titulacion::query();
        if ($estado !== 'all') {
            $base->where('estado', $estado);
        }

        $porEstado = (clone $base)
            ->selectRaw('estado as label, count(*) as total')
            ->groupBy('estado')
            ->get();

        $porTutor = (clone $base)
            ->join('users', 'users.id', '=', 'titulaciones.tutor_id')
            ->selectRaw('users.name as label, count(*) as total')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $porAnio = (clone $base)
            ->whereNotNull('fecha_inicio')
            ->selectRaw('EXTRACT(YEAR FROM fecha_inicio) as label, count(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->get()
            ->map(fn ($row) => ['label' => (string) (int) $row->label, 'total' => $row->total]);

        $total = (clone $base)->count();
        $graduados = (clone $base)->where('estado', 'graduado')->count();

        return Inertia::render('Reportes/Titulacion', [
            'kpis' => [
                'total' => $total,
                'graduados' => $graduados,
                'porcentaje_graduados' => $total > 0 ? round(($graduados / $total) * 100) : 0,
            ],
            'charts' => [
                'porEstado' => ['labels' => $porEstado->pluck('label'), 'series' => $porEstado->pluck('total')],
                'porTutor' => ['labels' => $porTutor->pluck('label'), 'series' => $porTutor->pluck('total')],
                'porAnio' => ['labels' => $porAnio->pluck('label'), 'series' => $porAnio->pluck('total')],
            ],
            'filters' => ['estado' => $estado],
        ]);
    }
}
