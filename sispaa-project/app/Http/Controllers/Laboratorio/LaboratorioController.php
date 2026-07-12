<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Laboratorio\Equipo;
use App\Models\Laboratorio\Laboratorio;
use App\Models\Laboratorio\PracticaLaboratorio;
use App\Models\Laboratorio\Reactivo;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard general del módulo y reporte por carrera. El CRUD de
 * Prácticas/Laboratorios/Equipos/Reactivos vive en sus propios
 * controladores (PracticaLaboratorioController, EspacioLaboratorioController,
 * EquipoController, ReactivoController).
 */
class LaboratorioController extends Controller
{
    use HasBreadcrumbs;

    public function index(): Response
    {
        $ultimasPracticas = PracticaLaboratorio::with(['materia.carrera', 'docente', 'laboratorio'])
            ->orderByDesc('fecha')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'tema' => $p->tema,
                'materia' => $p->materia->nombre,
                'carrera' => $p->materia->carrera->nombre,
                'docente' => $p->docente->name,
                'laboratorio' => $p->laboratorio?->nombre,
                'numero_estudiantes' => $p->numero_estudiantes,
                'fecha' => $p->fecha?->format('Y-m-d'),
            ]);

        $laboratoriosMasUsados = Laboratorio::withCount('practicas')
            ->orderByDesc('practicas_count')
            ->limit(5)
            ->get(['id', 'nombre'])
            ->map(fn ($l) => ['id' => $l->id, 'nombre' => $l->nombre, 'usos' => $l->practicas_count]);

        return Inertia::render('Laboratorio/Index', [
            'stats' => [
                'total_practicas' => PracticaLaboratorio::count(),
                'laboratorios_activos' => Laboratorio::where('estado', 'activo')->count(),
                'total_equipos' => Equipo::count(),
                'total_reactivos' => Reactivo::count(),
                'estudiantes_atendidos' => (int) PracticaLaboratorio::sum('numero_estudiantes'),
            ],
            'ultimasPracticas' => $ultimasPracticas,
            'laboratoriosMasUsados' => $laboratoriosMasUsados,
        ]);
    }

    /**
     * Estadísticas por carrera (prácticas, estudiantes atendidos y
     * laboratorios propios de cada carrera).
     */
    public function porCarrera(): Response
    {
        $carreras = Carrera::orderBy('nombre')->get(['id', 'nombre'])->map(function ($c) {
            $practicasQuery = PracticaLaboratorio::whereHas('materia', fn ($q) => $q->where('carrera_id', $c->id));

            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'practicas' => $practicasQuery->count(),
                'estudiantes' => (int) $practicasQuery->sum('numero_estudiantes'),
                'laboratorios' => Laboratorio::where('carrera_id', $c->id)->count(),
            ];
        });

        return Inertia::render('Laboratorio/PorCarrera', [
            'carreras' => $carreras,
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Reporte por Carrera'),
        ]);
    }
}
