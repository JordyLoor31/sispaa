<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Laboratorio\Laboratorio;
use App\Models\Laboratorio\Reactivo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReactivoController extends Controller
{
    use HasBreadcrumbs;

    public function index(Request $request): Response
    {
        $laboratorioId = $request->input('laboratorio_id', 'all');

        $query = Reactivo::with('laboratorio');
        if ($laboratorioId !== 'all') {
            $query->where('laboratorio_id', $laboratorioId);
        }

        return Inertia::render('Laboratorio/Reactivos/Index', [
            'reactivos' => $query->orderBy('nombre')->get()->map(fn ($r) => [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'formula' => $r->formula,
                'cantidad' => $r->cantidad,
                'unidad' => $r->unidad,
                'estado' => $r->estado,
                'laboratorio' => $r->laboratorio->nombre,
                'laboratorio_id' => $r->laboratorio_id,
            ]),
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['laboratorio_id' => $laboratorioId],
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Reactivos'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Laboratorio/Reactivos/Create', [
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Reactivos', 'Nuevo Reactivo', route('laboratorio.reactivos')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'nombre' => 'required|string|max:255',
            'formula' => 'nullable|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'unidad' => 'nullable|string|max:30',
        ]);

        Reactivo::create([
            'laboratorio_id' => $request->laboratorio_id,
            'nombre' => $request->nombre,
            'formula' => $request->formula,
            'cantidad' => $request->cantidad,
            'unidad' => $request->unidad,
            'estado' => 'disponible',
        ]);

        return redirect()->route('laboratorio.reactivos')->with('success', 'Reactivo registrado.');
    }

    public function show(Reactivo $reactivo): Response
    {
        $reactivo->load(['laboratorio', 'creator', 'updater']);

        return Inertia::render('Laboratorio/Reactivos/Show', [
            'reactivo' => $reactivo,
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Reactivos', 'Ver Reactivo', route('laboratorio.reactivos'), $reactivo->nombre),
        ]);
    }

    public function edit(Reactivo $reactivo): Response
    {
        return Inertia::render('Laboratorio/Reactivos/Edit', [
            'reactivo' => $reactivo,
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Reactivos', 'Editar Reactivo', route('laboratorio.reactivos'), $reactivo->nombre),
        ]);
    }

    public function update(Request $request, Reactivo $reactivo)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'laboratorio_id' => 'required|exists:laboratorios,id',
                'nombre' => 'required|string|max:255',
                'formula' => 'nullable|string|max:255',
                'cantidad' => 'required|integer|min:0',
                'unidad' => 'nullable|string|max:30',
            ]);
            $reactivo->update($request->only(['laboratorio_id', 'nombre', 'formula', 'cantidad', 'unidad']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:disponible,agotado,vencido']);
            $reactivo->update(['estado' => $request->estado]);
        }

        return redirect()->route('laboratorio.reactivos')->with('success', 'Reactivo actualizado.');
    }

    public function destroy(Reactivo $reactivo)
    {
        $reactivo->delete();

        return redirect()->route('laboratorio.reactivos')->with('success', 'Reactivo eliminado.');
    }
}
