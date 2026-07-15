<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\Matricula;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Periodos Académicos (Gestión de Periodos), exclusivo de
 * SystemAdministrador. Reemplaza la pantalla anterior "Fechas y
 * Convocatorias" (antes en AdminPortalController::fechasIndex/periodoStore/
 * periodoUpdate/periodoDeadlinesUpdate): aquí se unifica la creación/edición
 * del periodo junto con sus fechas límite de sílabos/informes en un solo
 * recurso, siguiendo el patrón Index/Create/Edit/Show + Form + columns.ts
 * (ver CarreraController).
 */
class PeriodoAcademicoController extends Controller
{
    use HasBreadcrumbs;

    public function index(Request $request): Response
    {
        $query = PeriodoAcademico::query();

        if ($q = $request->input('q')) {
            $query->where('nombre', 'like', "%{$q}%");
        }

        $periodos = $query->orderByDesc('fecha_inicio')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Periodos/Index', [
            'periodos' => $periodos,
            'filters' => $request->only(['q', 'per_page']),
            'breadcrumbs' => $this->adminBreadcrumbs('Gestión de Periodos'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Periodos/Create', [
            'breadcrumbs' => $this->adminBreadcrumbs('Gestión de Periodos', 'Nuevo Periodo', route('admin.periodos.index')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:periodos_academicos,nombre',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo' => 'required|in:semestral,anual',
            'fecha_limite_silabo' => 'nullable|date',
            'fecha_limite_informe' => 'nullable|date',
        ]);

        // Un periodo nuevo siempre nace en estado "planificado"; se activa
        // explícitamente después desde el listado o la vista de detalle.
        PeriodoAcademico::create([
            ...$validated,
            'estado' => PeriodoAcademico::ESTADO_PLANIFICADO,
        ]);

        return redirect()->route('admin.periodos.index')->with('success', 'Periodo académico creado correctamente.');
    }

    public function show(PeriodoAcademico $periodo): Response
    {
        $periodo->load(['creator', 'updater']);

        return Inertia::render('Admin/Periodos/Show', [
            'periodo' => $periodo,
            'totalMatriculados' => Matricula::where('periodo_id', $periodo->id)->count(),
            'breadcrumbs' => $this->adminBreadcrumbs('Gestión de Periodos', 'Ver Periodo', route('admin.periodos.index'), $periodo->nombre),
        ]);
    }

    public function edit(PeriodoAcademico $periodo): Response
    {
        return Inertia::render('Admin/Periodos/Edit', [
            'periodo' => $periodo,
            'breadcrumbs' => $this->adminBreadcrumbs('Gestión de Periodos', 'Editar Periodo', route('admin.periodos.index'), $periodo->nombre),
        ]);
    }

    public function update(Request $request, PeriodoAcademico $periodo)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('periodos_academicos', 'nombre')->ignore($periodo->id)],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => ['required', Rule::in(PeriodoAcademico::ESTADOS)],
            'fecha_limite_silabo' => 'nullable|date',
            'fecha_limite_informe' => 'nullable|date',
        ]);

        $this->aplicarEstado($periodo, $validated['estado']);

        $periodo->update($validated);

        return redirect()->route('admin.periodos.index')->with('success', 'Periodo académico actualizado correctamente.');
    }

    /**
     * Acciones rápidas (desde el listado o la vista de detalle) para mover
     * el periodo a Activo o a Finalizado sin pasar por el formulario.
     */
    public function activate(PeriodoAcademico $periodo)
    {
        $this->aplicarEstado($periodo, PeriodoAcademico::ESTADO_ACTIVO);
        $periodo->update(['estado' => PeriodoAcademico::ESTADO_ACTIVO]);

        return back()->with('success', 'Periodo activado correctamente.');
    }

    public function finalize(PeriodoAcademico $periodo)
    {
        $periodo->update(['estado' => PeriodoAcademico::ESTADO_FINALIZADO]);

        return back()->with('success', 'Periodo finalizado correctamente.');
    }

    /**
     * Solo un periodo puede estar activo a la vez a nivel institucional: al
     * activar uno, cualquier otro que estuviera activo pasa a finalizado
     * (activar uno nuevo implica que el anterior concluyó).
     */
    private function aplicarEstado(PeriodoAcademico $periodo, string $nuevoEstado): void
    {
        if ($nuevoEstado === PeriodoAcademico::ESTADO_ACTIVO) {
            PeriodoAcademico::where('id', '!=', $periodo->id)
                ->where('estado', PeriodoAcademico::ESTADO_ACTIVO)
                ->update(['estado' => PeriodoAcademico::ESTADO_FINALIZADO]);
        }
    }
}
