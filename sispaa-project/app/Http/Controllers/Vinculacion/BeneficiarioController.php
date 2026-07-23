<?php

namespace App\Http\Controllers\Vinculacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Vinculacion\Beneficiario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Beneficiarios (antes "Empresas beneficiadas"). Catálogo usado por las
 * Actividades de Vinculación.
 *
 * tipo: persona_natural | persona_juridica | comunidad_organizacion.
 * El RUC es obligatorio solo cuando tipo = persona_juridica.
 */
class BeneficiarioController extends Controller
{
    use HasBreadcrumbs;

    public function index(): Response
    {
        return Inertia::render('Vinculacion/Beneficiarios/Index', [
            'beneficiarios' => Beneficiario::withCount('actividadesVinculacion')->orderBy('nombre')->get()->map(fn ($b) => [
                'id' => $b->id,
                'tipo' => $b->tipo,
                'nombre' => $b->nombre,
                'ruc' => $b->ruc,
                'cedula' => $b->cedula,
                'sector' => $b->sector,
                'contacto' => $b->contacto,
                'actividades_count' => $b->actividades_vinculacion_count,
            ]),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Beneficiarios'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vinculacion/Beneficiarios/Create', [
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Beneficiarios', 'Nuevo Beneficiario', route('vinculacion.beneficiarios')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        Beneficiario::create($data);

        return redirect()->route('vinculacion.beneficiarios')->with('success', 'Beneficiario registrado.');
    }

    public function show(Beneficiario $beneficiario): Response
    {
        $beneficiario->loadCount('actividadesVinculacion')->load(['creator', 'updater']);

        return Inertia::render('Vinculacion/Beneficiarios/Show', [
            'beneficiario' => [
                'id' => $beneficiario->id,
                'tipo' => $beneficiario->tipo,
                'nombre' => $beneficiario->nombre,
                'ruc' => $beneficiario->ruc,
                'cedula' => $beneficiario->cedula,
                'sector' => $beneficiario->sector,
                'contacto' => $beneficiario->contacto,
                'actividades_count' => $beneficiario->actividades_vinculacion_count,
                'creator' => $beneficiario->creator ? ['id' => $beneficiario->creator->id, 'name' => $beneficiario->creator->name] : null,
                'updater' => $beneficiario->updater ? ['id' => $beneficiario->updater->id, 'name' => $beneficiario->updater->name] : null,
                'created_at' => $beneficiario->created_at,
            ],
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Beneficiarios', 'Ver Beneficiario', route('vinculacion.beneficiarios'), $beneficiario->nombre),
        ]);
    }

    public function edit(Beneficiario $beneficiario): Response
    {
        return Inertia::render('Vinculacion/Beneficiarios/Edit', [
            'beneficiario' => $beneficiario->only(['id', 'tipo', 'nombre', 'ruc', 'cedula', 'sector', 'contacto']),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Beneficiarios', 'Editar Beneficiario', route('vinculacion.beneficiarios'), $beneficiario->nombre),
        ]);
    }

    public function update(Request $request, Beneficiario $beneficiario)
    {
        $data = $this->validar($request, $beneficiario->id);

        $beneficiario->update($data);

        return redirect()->route('vinculacion.beneficiarios')->with('success', 'Beneficiario actualizado.');
    }

    public function destroy(Beneficiario $beneficiario)
    {
        $beneficiario->delete();

        return redirect()->route('vinculacion.beneficiarios')->with('success', 'Beneficiario eliminado.');
    }

    /**
     * Validación con RUC obligatorio solo para persona jurídica y cédula
     * opcional para persona natural.
     */
    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $rucRule = Rule::unique('beneficiarios', 'ruc');
        if ($ignoreId) {
            $rucRule->ignore($ignoreId);
        }

        return $request->validate([
            'tipo' => 'required|in:persona_natural,persona_juridica,comunidad_organizacion',
            'nombre' => 'required|string|max:255',
            'ruc' => [
                Rule::requiredIf(fn () => $request->input('tipo') === 'persona_juridica'),
                'nullable',
                'digits:13',
                $rucRule,
            ],
            'cedula' => 'nullable|digits:10',
            'sector' => 'nullable|string|max:255',
            'contacto' => 'nullable|digits:10',
        ]);
    }
}
