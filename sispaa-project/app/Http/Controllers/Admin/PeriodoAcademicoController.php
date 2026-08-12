<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\AsignacionDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        // Desactiva automáticamente los períodos activos cuya fecha_fin ya
        // venció, para que el listado refleje el estado real (mismo criterio
        // que usa el scheduler diario).
        PeriodoAcademico::finalizarVencidos();

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

        // Un periodo con fecha de fin ya vencida no puede activarse: el
        // scheduler/listado lo desactiva solo al instante, así que dejarlo
        // "activo" sería un cambio que no se guarda. Mejor avisar y pedir que
        // primero se extienda la fecha de fin.
        if ($validated['estado'] === PeriodoAcademico::ESTADO_ACTIVO
            && Carbon::parse($validated['fecha_fin'])->isPast()) {
            return back()->withErrors([
                'estado' => 'No se puede activar: la fecha de fin ('.Carbon::parse($validated['fecha_fin'])->format('d/m/Y').') ya venció. Extiende la fecha de fin para poder activar el periodo.',
            ])->withInput();
        }

        // Se copia desde el periodo anterior cronológico (sin importar si ya
        // quedó finalizado solo por vencimiento): así el nuevo periodo arranca
        // con las mismas materias asignadas a los docentes.
        $anterior = PeriodoAcademico::where('id', '!=', $periodo->id)
            ->orderByDesc('fecha_inicio')
            ->first();

        $this->aplicarEstado($periodo, $validated['estado']);

        $periodo->update($validated);

        $mensaje = 'Periodo académico actualizado correctamente.';
        if ($validated['estado'] === PeriodoAcademico::ESTADO_ACTIVO && $anterior) {
            $mensaje = $this->mensajeConCopiadas($mensaje, $this->copiarAsignaciones($anterior, $periodo));
        }

        return redirect()->route('admin.periodos.index')->with('success', $mensaje);
    }

    /**
     * Acciones rápidas (desde el listado o la vista de detalle) para mover
     * el periodo a Activo o a Finalizado sin pasar por el formulario.
     */
    public function activate(PeriodoAcademico $periodo)
    {
        if ($periodo->fecha_fin->isPast()) {
            return back()->withErrors([
                'estado' => 'No se puede activar: la fecha de fin ('.$periodo->fecha_fin->format('d/m/Y').') ya venció. Extiende la fecha de fin para poder activar el periodo.',
            ]);
        }

        // Mismo criterio que update(): el anterior se busca por cronología,
        // no por estado (puede que ya se haya finalizado solo al vencer).
        $anterior = PeriodoAcademico::where('id', '!=', $periodo->id)
            ->orderByDesc('fecha_inicio')
            ->first();

        $this->aplicarEstado($periodo, PeriodoAcademico::ESTADO_ACTIVO);
        $periodo->update(['estado' => PeriodoAcademico::ESTADO_ACTIVO]);

        $mensaje = 'Periodo activado correctamente.';
        if ($anterior) {
            $mensaje = $this->mensajeConCopiadas($mensaje, $this->copiarAsignaciones($anterior, $periodo));
        }

        return back()->with('success', $mensaje);
    }

    public function finalize(PeriodoAcademico $periodo)
    {
        $periodo->update(['estado' => PeriodoAcademico::ESTADO_FINALIZADO]);

        return back()->with('success', 'Periodo desactivado correctamente.');
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

    /**
     * Copia las asignaciones de docentes del periodo anterior al nuevo, para
     * que la carrera quede vinculada al nuevo periodo sin reingresar todo a
     * mano. Devuelve cuántas asignaciones se copiaron.
     */
    private function copiarAsignaciones(PeriodoAcademico $anterior, PeriodoAcademico $nuevo): int
    {
        return AsignacionDocente::copiarDesdePeriodo($anterior->id, $nuevo->id);
    }
    /**
     * Añade al mensaje de éxito cuántas asignaciones se replicaron, si hubo.
     */
    private function mensajeConCopiadas(string $mensaje, int $copiadas): string
    {
        if ($copiadas > 0) {
            $mensaje .= " Se copiaron {$copiadas} asignaciones de docentes del periodo anterior.";
        }

        return $mensaje;
    }
}
