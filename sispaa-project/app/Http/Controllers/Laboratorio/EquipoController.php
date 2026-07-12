<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Laboratorio\Equipo;
use App\Models\Laboratorio\Laboratorio;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipoController extends Controller
{
    use HasBreadcrumbs;

    public function index(Request $request): Response
    {
        $laboratorioId = $request->input('laboratorio_id', 'all');

        $query = Equipo::with('laboratorio');
        if ($laboratorioId !== 'all') {
            $query->where('laboratorio_id', $laboratorioId);
        }

        return Inertia::render('Laboratorio/Equipos/Index', [
            'equipos' => $query->orderBy('nombre')->get()->map(fn ($e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'codigo' => $e->codigo,
                'cantidad' => $e->cantidad,
                'estado' => $e->estado,
                'laboratorio' => $e->laboratorio->nombre,
                'laboratorio_id' => $e->laboratorio_id,
            ]),
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['laboratorio_id' => $laboratorioId],
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Equipos'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Laboratorio/Equipos/Create', [
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Equipos', 'Nuevo Equipo', route('laboratorio.equipos')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:30|unique:equipos,codigo',
            'cantidad' => 'required|integer|min:1',
        ]);

        Equipo::create([
            'laboratorio_id' => $request->laboratorio_id,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'cantidad' => $request->cantidad,
            'estado' => 'operativo',
        ]);

        return redirect()->route('laboratorio.equipos')->with('success', 'Equipo registrado.');
    }

    public function show(Equipo $equipo): Response
    {
        $equipo->load(['laboratorio', 'creator', 'updater']);

        return Inertia::render('Laboratorio/Equipos/Show', [
            'equipo' => $equipo,
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Equipos', 'Ver Equipo', route('laboratorio.equipos'), $equipo->nombre),
        ]);
    }

    public function edit(Equipo $equipo): Response
    {
        return Inertia::render('Laboratorio/Equipos/Edit', [
            'equipo' => $equipo,
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Equipos', 'Editar Equipo', route('laboratorio.equipos'), $equipo->nombre),
        ]);
    }

    public function update(Request $request, Equipo $equipo)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'laboratorio_id' => 'required|exists:laboratorios,id',
                'nombre' => 'required|string|max:255',
                'codigo' => 'required|string|max:30|unique:equipos,codigo,' . $equipo->id,
                'cantidad' => 'required|integer|min:1',
            ]);
            $equipo->update($request->only(['laboratorio_id', 'nombre', 'codigo', 'cantidad']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:operativo,mantenimiento,dañado']);
            $equipo->update(['estado' => $request->estado]);
        }

        return redirect()->route('laboratorio.equipos')->with('success', 'Equipo actualizado.');
    }

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();

        return redirect()->route('laboratorio.equipos')->with('success', 'Equipo eliminado.');
    }
}
