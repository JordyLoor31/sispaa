<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Convocatoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Convocatorias, separado de SecretariaController. Sigue el patrón
 * Index/Create/Edit/Show + Form (ver resources/js/pages/Secretaria/Convocatorias).
 *
 * Nota: a diferencia de Carreras/Usuarios, la vista Index conserva su grid de
 * tarjetas (no una tabla) porque el contenido (título, módulo, rango de
 * fechas) se lee mejor así; por eso no hay columns.ts aquí.
 */
class ConvocatoriaController extends Controller
{
    use HasBreadcrumbs;

    private const MODULOS = ['Académico', 'Titulación', 'Vinculación', 'Investigación', 'Laboratorio'];

    public function index(Request $request): Response
    {
        $modulo = $request->input('modulo', 'all');
        $q = trim((string) $request->input('q', ''));

        $query = Convocatoria::with(['creadoPor', 'creator', 'updater'])->orderBy('fecha_fin', 'desc');

        if ($modulo && $modulo !== 'all') {
            $query->where('modulo', $modulo);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('titulo', 'ilike', "%{$q}%")->orWhere('descripcion', 'ilike', "%{$q}%");
            });
        }

        return Inertia::render('Secretaria/Convocatorias/Index', [
            'convocatorias' => $query->get(),
            'filters' => ['modulo' => $modulo, 'q' => $q ?: null],
            'modulos' => self::MODULOS,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Convocatorias'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secretaria/Convocatorias/Create', [
            'modulos' => self::MODULOS,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Convocatorias', 'Nueva Convocatoria', route('secretaria.convocatorias.index')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'modulo' => 'required|string|max:100',
            'tipo_documento' => 'nullable|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        Convocatoria::create([
            ...$validated,
            'creado_por' => Auth::id(),
            'activa' => true,
        ]);

        return redirect()->route('secretaria.convocatorias.index')->with('success', 'Convocatoria creada correctamente.');
    }

    public function show(Convocatoria $convocatoria): Response
    {
        $convocatoria->load(['creadoPor', 'creator', 'updater']);

        return Inertia::render('Secretaria/Convocatorias/Show', [
            'convocatoria' => $convocatoria,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Convocatorias', 'Ver Convocatoria', route('secretaria.convocatorias.index'), $convocatoria->titulo),
        ]);
    }

    public function edit(Convocatoria $convocatoria): Response
    {
        return Inertia::render('Secretaria/Convocatorias/Edit', [
            'convocatoria' => $convocatoria,
            'modulos' => self::MODULOS,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Convocatorias', 'Editar Convocatoria', route('secretaria.convocatorias.index'), $convocatoria->titulo),
        ]);
    }

    public function update(Request $request, Convocatoria $convocatoria)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'modulo' => 'required|string|max:100',
            'tipo_documento' => 'nullable|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'activa' => 'required|boolean',
        ]);

        $convocatoria->update($validated);

        return redirect()->route('secretaria.convocatorias.index')->with('success', 'Convocatoria actualizada correctamente.');
    }

    public function destroy(Convocatoria $convocatoria)
    {
        $convocatoria->delete();

        return redirect()->route('secretaria.convocatorias.index')->with('success', 'Convocatoria eliminada correctamente.');
    }
}
