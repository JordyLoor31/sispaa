<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Carreras, separado de AdminPortalController (que antes mezclaba
 * Carreras y Materias en un solo método 'malla*'). Sigue el patrón
 * Index/Create/Edit/Show + Form + columns.ts (ver resources/js/pages/Admin/Carreras).
 */
class CarreraController extends Controller
{
    use HasBreadcrumbs;

    public function index(Request $request): Response
    {
        $query = Carrera::with('coordinador')->withCount(['materias', 'usuarios']);

        if ($q = $request->input('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%");
            });
        }

        $carreras = $query->orderBy('nombre')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Carreras/Index', [
            'carreras' => $carreras,
            'filters' => $request->only(['q', 'per_page']),
            'breadcrumbs' => $this->adminBreadcrumbs('Carreras'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Carreras/Create', [
            'coordinadores' => User::role(['coordinador', 'docente'])->select('id', 'name')->orderBy('name')->get(),
            'breadcrumbs' => $this->adminBreadcrumbs('Carreras', 'Nueva Carrera', route('admin.carreras.index')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:carreras',
            'coordinador_id' => 'nullable|exists:users,id',
        ]);

        Carrera::create([...$validated, 'activa' => true]);

        return redirect()->route('admin.carreras.index')->with('success', 'Carrera creada correctamente.');
    }

    public function show(Carrera $carrera): Response
    {
        $carrera->load(['coordinador', 'creator', 'updater'])
            ->loadCount(['materias', 'usuarios']);

        return Inertia::render('Admin/Carreras/Show', [
            'carrera' => $carrera,
            'breadcrumbs' => $this->adminBreadcrumbs('Carreras', 'Ver Carrera', route('admin.carreras.index'), $carrera->nombre),
        ]);
    }

    public function edit(Carrera $carrera): Response
    {
        return Inertia::render('Admin/Carreras/Edit', [
            'carrera' => $carrera,
            'coordinadores' => User::role(['coordinador', 'docente'])->select('id', 'name')->orderBy('name')->get(),
            'breadcrumbs' => $this->adminBreadcrumbs('Carreras', 'Editar Carrera', route('admin.carreras.index'), $carrera->nombre),
        ]);
    }

    public function update(Request $request, Carrera $carrera)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:carreras,codigo,' . $carrera->id,
            'coordinador_id' => 'nullable|exists:users,id',
        ]);

        $carrera->update($validated);

        return redirect()->route('admin.carreras.index')->with('success', 'Carrera actualizada correctamente.');
    }

    public function toggleStatus(Carrera $carrera)
    {
        $carrera->update(['activa' => !$carrera->activa]);

        return back()->with('success', 'Estado de la carrera actualizado.');
    }
}
