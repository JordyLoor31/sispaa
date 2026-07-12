<?php

namespace App\Http\Controllers\Vinculacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Empresas Beneficiadas, separado de VinculacionController.
 * Catálogo simple usado por las Actividades de Vinculación.
 */
class EmpresaController extends Controller
{
    use HasBreadcrumbs;

    public function index(): Response
    {
        return Inertia::render('Vinculacion/Empresas/Index', [
            'empresas' => Empresa::withCount('actividadesVinculacion')->orderBy('nombre')->get()->map(fn ($e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'ruc' => $e->ruc,
                'sector' => $e->sector,
                'contacto' => $e->contacto,
                'actividades_count' => $e->actividades_vinculacion_count,
            ]),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Empresas Beneficiadas'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vinculacion/Empresas/Create', [
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Empresas Beneficiadas', 'Nueva Empresa', route('vinculacion.empresas')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:13|unique:empresas,ruc',
            'sector' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
        ]);

        Empresa::create($request->only(['nombre', 'ruc', 'sector', 'contacto']));

        return redirect()->route('vinculacion.empresas')->with('success', 'Empresa registrada.');
    }

    public function show(Empresa $empresa): Response
    {
        $empresa->loadCount('actividadesVinculacion')->load(['creator', 'updater']);

        return Inertia::render('Vinculacion/Empresas/Show', [
            'empresa' => $empresa,
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Empresas Beneficiadas', 'Ver Empresa', route('vinculacion.empresas'), $empresa->nombre),
        ]);
    }

    public function edit(Empresa $empresa): Response
    {
        return Inertia::render('Vinculacion/Empresas/Edit', [
            'empresa' => $empresa,
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Empresas Beneficiadas', 'Editar Empresa', route('vinculacion.empresas'), $empresa->nombre),
        ]);
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:13|unique:empresas,ruc,' . $empresa->id,
            'sector' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
        ]);

        $empresa->update($request->only(['nombre', 'ruc', 'sector', 'contacto']));

        return redirect()->route('vinculacion.empresas')->with('success', 'Empresa actualizada.');
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->route('vinculacion.empresas')->with('success', 'Empresa eliminada.');
    }
}
