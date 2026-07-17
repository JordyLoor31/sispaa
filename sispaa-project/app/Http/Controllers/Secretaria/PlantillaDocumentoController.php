<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Documentos\PlantillaDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Plantillas de Documentos: formatos institucionales (solicitudes,
 * certificados, etc.) que Secretaría sube para que cualquier estudiante los
 * descargue desde su portal (menú "Plantillas", ver
 * StudentPortalController::plantillas()).
 *
 * El archivo se sirve por ruta autenticada (descargar()), no por enlace
 * directo a /storage/..., por la misma razón documentada en
 * Docencia\SilaboController::ver() (symlink roto / 403 típico en XAMPP).
 */
class PlantillaDocumentoController extends Controller
{
    use HasBreadcrumbs;

    private function transform(PlantillaDocumento $p): array
    {
        return [
            'id' => $p->id,
            'nombre_doc' => $p->nombre_doc,
            'ver_url' => route('plantillas.descargar', $p->id),
            'creado_por' => $p->creator?->name,
            'created_at' => $p->created_at?->format('d/m/Y H:i'),
            'actualizado_por' => $p->updater?->name,
            'updated_at' => $p->updated_at?->format('d/m/Y H:i'),
        ];
    }

    public function index(Request $request): Response
    {
        $q = $request->input('q');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = PlantillaDocumento::with(['creator', 'updater']);

        if ($q) {
            $query->where('nombre_doc', 'ilike', "%{$q}%");
        }

        $plantillas = $query->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($p) => $this->transform($p));

        return Inertia::render('Secretaria/Plantillas/Index', [
            'plantillas' => $plantillas,
            'filters' => $request->only(['q', 'per_page']),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Plantillas de Documentos'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secretaria/Plantillas/Create', [
            'breadcrumbs' => $this->secretariaBreadcrumbs('Plantillas de Documentos', 'Nueva Plantilla', route('secretaria.plantillas.index')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_doc' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $path = $request->file('archivo')->store('plantillas', 'public');

        PlantillaDocumento::create([
            'nombre_doc' => $request->nombre_doc,
            'url_doc' => '/storage/' . $path,
        ]);

        return redirect()->route('secretaria.plantillas.index')->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(PlantillaDocumento $plantilla): Response
    {
        return Inertia::render('Secretaria/Plantillas/Edit', [
            'plantilla' => $this->transform($plantilla),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Plantillas de Documentos', 'Editar Plantilla', route('secretaria.plantillas.index'), $plantilla->nombre_doc),
        ]);
    }

    public function update(Request $request, PlantillaDocumento $plantilla)
    {
        $request->validate([
            'nombre_doc' => 'required|string|max:255',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $data = ['nombre_doc' => $request->nombre_doc];

        if ($request->hasFile('archivo')) {
            $data['url_doc'] = '/storage/' . $request->file('archivo')->store('plantillas', 'public');
        }

        $plantilla->update($data);

        return redirect()->route('secretaria.plantillas.index')->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy(PlantillaDocumento $plantilla)
    {
        $plantilla->delete();

        return redirect()->route('secretaria.plantillas.index')->with('success', 'Plantilla eliminada correctamente.');
    }

    /**
     * Descarga el archivo de la plantilla. Cualquier usuario autenticado
     * puede acceder (Secretaría al administrarlas, cualquier estudiante al
     * descargarlas desde su portal): no es información sensible por
     * estudiante, es un formato institucional de uso general.
     */
    public function descargar(PlantillaDocumento $plantilla)
    {
        $ruta = Str::after($plantilla->url_doc, '/storage/');

        abort_unless($ruta && Storage::disk('public')->exists($ruta), 404, 'El archivo ya no está disponible.');

        $extension = pathinfo($ruta, PATHINFO_EXTENSION);
        $nombreDescarga = Str::slug($plantilla->nombre_doc) . ($extension ? '.' . $extension : '');

        return Storage::disk('public')->response($ruta, $nombreDescarga);
    }
}
