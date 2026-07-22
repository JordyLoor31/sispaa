<?php

namespace App\Http\Controllers\Vinculacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\Empresa;
use App\Models\Admin\PeriodoAcademico;
use App\Models\User;
use App\Models\Vinculacion\ActividadVinculacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Actividades de Vinculación, separado de VinculacionController
 * (que antes mezclaba Actividades y Empresas en una sola clase). La
 * asignación de "líder" (docente_lider_id) se resuelve en el propio
 * formulario de la actividad, sin pantalla aparte.
 */
class ActividadVinculacionController extends Controller
{
    use HasBreadcrumbs;

    private function catalogos(): array
    {
        return [
            'docentes' => User::role('docente')->get(['id', 'name']),
            'carreras' => Carrera::get(['id', 'nombre']),
            'periodos' => PeriodoAcademico::where('estado', 'activo')->get(['id', 'nombre']),
            'empresas' => Empresa::orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    public function index(Request $request): Response
    {
        $estado = $request->input('estado', 'all');

        $query = ActividadVinculacion::with(['docenteLider', 'carrera', 'periodo', 'empresa']);
        if ($estado !== 'all') {
            $query->where('estado', $estado);
        }

        $actividades = $query->orderByDesc('created_at')->get()->map(fn ($a) => [
            'id' => $a->id,
            'nombre' => $a->nombre,
            'estado' => $a->estado,
            'fecha' => $a->fecha?->format('Y-m-d'),
            'docente_lider' => ['id' => $a->docenteLider->id, 'name' => $a->docenteLider->name],
            'carrera_id' => $a->carrera_id,
            'carrera' => $a->carrera->nombre,
            'periodo_id' => $a->periodo_id,
            'periodo' => $a->periodo->nombre,
            'empresa_id' => $a->empresa_id,
            'empresa' => $a->empresa?->nombre,
        ]);

        return Inertia::render('Vinculacion/Actividades/Index', [
            'actividades' => $actividades,
            'filters' => ['estado' => $estado],
            'stats' => [
                'pendientes' => ActividadVinculacion::where('estado', 'pendiente')->count(),
                'ejecutadas' => ActividadVinculacion::where('estado', 'ejecutado')->count(),
                'total' => ActividadVinculacion::count(),
            ],
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vinculacion/Actividades/Create', [
            ...$this->catalogos(),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades', 'Nueva Actividad', route('vinculacion.actividades')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'docente_lider_id' => 'required|exists:users,id',
            'carrera_id' => 'required|exists:carreras,id',
            'periodo_id' => 'required|exists:periodos_academicos,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'fecha' => 'nullable|date',
        ]);

        ActividadVinculacion::create([
            'nombre' => $request->nombre,
            'docente_lider_id' => $request->docente_lider_id,
            'carrera_id' => $request->carrera_id,
            'periodo_id' => $request->periodo_id,
            'empresa_id' => $request->empresa_id,
            'fecha' => $request->fecha,
            'estado' => 'pendiente',
        ]);

        return redirect()->route('vinculacion.actividades')->with('success', 'Actividad de vinculación registrada.');
    }

    public function show(ActividadVinculacion $actividad): Response
    {
        $actividad->load(['docenteLider', 'carrera', 'periodo', 'empresa', 'creator', 'updater']);

        return Inertia::render('Vinculacion/Actividades/Show', [
            'actividad' => [
                'id' => $actividad->id,
                'nombre' => $actividad->nombre,
                'estado' => $actividad->estado,
                'fecha' => $actividad->fecha?->format('Y-m-d'),
                'docente_lider' => $actividad->docenteLider
                    ? ['id' => $actividad->docenteLider->id, 'name' => $actividad->docenteLider->name]
                    : null,
                'carrera_id' => $actividad->carrera_id,
                'carrera' => $actividad->carrera?->nombre,
                'periodo_id' => $actividad->periodo_id,
                'periodo' => $actividad->periodo?->nombre,
                'empresa_id' => $actividad->empresa_id,
                'empresa' => $actividad->empresa?->nombre,
                'creator' => $actividad->creator ? ['id' => $actividad->creator->id, 'name' => $actividad->creator->name] : null,
                'updater' => $actividad->updater ? ['id' => $actividad->updater->id, 'name' => $actividad->updater->name] : null,
                'created_at' => $actividad->created_at,
            ],
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades', 'Ver Actividad', route('vinculacion.actividades'), $actividad->nombre),
        ]);
    }

    public function edit(ActividadVinculacion $actividad): Response
    {
        $actividad->load(['docenteLider', 'carrera', 'periodo', 'empresa']);

        return Inertia::render('Vinculacion/Actividades/Edit', [
            'actividad' => [
                'id' => $actividad->id,
                'nombre' => $actividad->nombre,
                'estado' => $actividad->estado,
                'fecha' => $actividad->fecha?->format('Y-m-d'),
                'docente_lider' => $actividad->docenteLider
                    ? ['id' => $actividad->docenteLider->id, 'name' => $actividad->docenteLider->name]
                    : null,
                'carrera_id' => $actividad->carrera_id,
                'carrera' => $actividad->carrera?->nombre,
                'periodo_id' => $actividad->periodo_id,
                'periodo' => $actividad->periodo?->nombre,
                'empresa_id' => $actividad->empresa_id,
                'empresa' => $actividad->empresa?->nombre,
            ],
            ...$this->catalogos(),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades', 'Editar Actividad', route('vinculacion.actividades'), $actividad->nombre),
        ]);
    }

    public function update(Request $request, ActividadVinculacion $actividad)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'docente_lider_id' => 'required|exists:users,id',
                'carrera_id' => 'required|exists:carreras,id',
                'periodo_id' => 'required|exists:periodos_academicos,id',
                'empresa_id' => 'nullable|exists:empresas,id',
                'fecha' => 'nullable|date',
            ]);
            $actividad->update($request->only([
                'nombre', 'docente_lider_id', 'carrera_id', 'periodo_id', 'empresa_id', 'fecha',
            ]));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:pendiente,ejecutado']);
            $actividad->update(['estado' => $request->estado]);
        }

        return redirect()->route('vinculacion.actividades')->with('success', 'Actividad actualizada.');
    }

    public function destroy(ActividadVinculacion $actividad)
    {
        $actividad->delete();

        return redirect()->route('vinculacion.actividades')->with('success', 'Actividad eliminada.');
    }
}
