<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Laboratorio\Laboratorio;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Laboratorios (espacios físicos), separado de LaboratorioController
 * (que ahora solo sirve el dashboard general y el reporte por carrera).
 * Se llama EspacioLaboratorioController (no LaboratorioController) para no
 * chocar en el nombre con el controlador general del módulo.
 */
class EspacioLaboratorioController extends Controller
{
    use HasBreadcrumbs;

    public function index(): Response
    {
        $laboratorios = Laboratorio::with(['carrera', 'responsable'])
            ->withCount(['equipos', 'reactivos', 'practicas'])
            ->orderBy('nombre')
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'nombre' => $l->nombre,
                'ubicacion' => $l->ubicacion,
                'capacidad' => $l->capacidad,
                'estado' => $l->estado,
                'carrera' => $l->carrera?->nombre,
                'carrera_id' => $l->carrera_id,
                'responsable' => $l->responsable ? ['id' => $l->responsable->id, 'name' => $l->responsable->name] : null,
                'equipos_count' => $l->equipos_count,
                'reactivos_count' => $l->reactivos_count,
                'practicas_count' => $l->practicas_count,
            ]);

        return Inertia::render('Laboratorio/Laboratorios/Index', [
            'laboratorios' => $laboratorios,
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Laboratorios'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Laboratorio/Laboratorios/Create', [
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'responsables' => User::role('docente')->orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Laboratorios', 'Nuevo Laboratorio', route('laboratorio.laboratorios')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'carrera_id' => 'nullable|exists:carreras,id',
            'capacidad' => 'nullable|integer|min:1',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        Laboratorio::create([
            'nombre' => $request->nombre,
            'ubicacion' => $request->ubicacion,
            'carrera_id' => $request->carrera_id,
            'capacidad' => $request->capacidad,
            'responsable_id' => $request->responsable_id,
            'estado' => 'activo',
        ]);

        return redirect()->route('laboratorio.laboratorios')->with('success', 'Laboratorio registrado.');
    }

    public function show(Laboratorio $laboratorio): Response
    {
        $laboratorio->load(['carrera', 'responsable', 'creator', 'updater'])
            ->loadCount(['equipos', 'reactivos', 'practicas']);

        return Inertia::render('Laboratorio/Laboratorios/Show', [
            'laboratorio' => $laboratorio,
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Laboratorios', 'Ver Laboratorio', route('laboratorio.laboratorios'), $laboratorio->nombre),
        ]);
    }

    public function edit(Laboratorio $laboratorio): Response
    {
        return Inertia::render('Laboratorio/Laboratorios/Edit', [
            'laboratorio' => $laboratorio,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'responsables' => User::role('docente')->orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Laboratorios', 'Editar Laboratorio', route('laboratorio.laboratorios'), $laboratorio->nombre),
        ]);
    }

    public function update(Request $request, Laboratorio $laboratorio)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'ubicacion' => 'nullable|string|max:255',
                'carrera_id' => 'nullable|exists:carreras,id',
                'capacidad' => 'nullable|integer|min:1',
                'responsable_id' => 'nullable|exists:users,id',
            ]);
            $laboratorio->update($request->only(['nombre', 'ubicacion', 'carrera_id', 'capacidad', 'responsable_id']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:activo,inactivo']);
            $laboratorio->update(['estado' => $request->estado]);
        }

        return redirect()->route('laboratorio.laboratorios')->with('success', 'Laboratorio actualizado.');
    }

    public function destroy(Laboratorio $laboratorio)
    {
        $laboratorio->delete();

        return redirect()->route('laboratorio.laboratorios')->with('success', 'Laboratorio eliminado.');
    }
}
