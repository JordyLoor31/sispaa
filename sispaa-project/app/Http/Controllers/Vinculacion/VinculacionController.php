<?php

namespace App\Http\Controllers\Vinculacion;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\Empresa;
use App\Models\Admin\PeriodoAcademico;
use App\Models\User;
use App\Models\Vinculacion\ActividadVinculacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VinculacionController extends Controller
{
    /**
     * Actividades de vinculación. La asignación de "líder" (docente_lider_id)
     * se resuelve directamente en el formulario de la actividad, sin una
     * pantalla separada (el sistema origen la tenía aparte, pero aquí no
     * aporta nada que el formulario no pueda resolver en un solo paso).
     */
    public function actividades(Request $request): Response
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
            'carrera' => $a->carrera->nombre,
            'periodo' => $a->periodo->nombre,
            'empresa' => $a->empresa?->nombre,
        ]);

        return Inertia::render('Vinculacion/Actividades', [
            'actividades' => $actividades,
            'docentes' => User::role('docente')->get(['id', 'name']),
            'carreras' => Carrera::get(['id', 'nombre']),
            'periodos' => PeriodoAcademico::where('activo', true)->get(['id', 'nombre']),
            'empresas' => Empresa::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['estado' => $estado],
            'stats' => [
                'pendientes' => ActividadVinculacion::where('estado', 'pendiente')->count(),
                'ejecutadas' => ActividadVinculacion::where('estado', 'ejecutado')->count(),
                'total' => ActividadVinculacion::count(),
            ],
        ]);
    }

    public function storeActividad(Request $request)
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

        return redirect()->back()->with('success', 'Actividad de vinculación registrada.');
    }

    public function updateActividad(Request $request, ActividadVinculacion $actividad)
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

        return redirect()->back()->with('success', 'Actividad actualizada.');
    }

    public function destroyActividad(ActividadVinculacion $actividad)
    {
        $actividad->delete();

        return redirect()->back()->with('success', 'Actividad eliminada.');
    }

    /**
     * Empresas beneficiadas: catálogo simple usado por las actividades.
     */
    public function empresas(): Response
    {
        return Inertia::render('Vinculacion/Empresas', [
            'empresas' => Empresa::withCount('actividadesVinculacion')->orderBy('nombre')->get()->map(fn ($e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'ruc' => $e->ruc,
                'sector' => $e->sector,
                'contacto' => $e->contacto,
                'actividades_count' => $e->actividades_vinculacion_count,
            ]),
        ]);
    }

    public function storeEmpresa(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:13|unique:empresas,ruc',
            'sector' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
        ]);

        Empresa::create($request->only(['nombre', 'ruc', 'sector', 'contacto']));

        return redirect()->back()->with('success', 'Empresa registrada.');
    }

    public function updateEmpresa(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:13|unique:empresas,ruc,' . $empresa->id,
            'sector' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
        ]);

        $empresa->update($request->only(['nombre', 'ruc', 'sector', 'contacto']));

        return redirect()->back()->with('success', 'Empresa actualizada.');
    }

    public function destroyEmpresa(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->back()->with('success', 'Empresa eliminada.');
    }
}
