<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Notificacion;
use App\Models\Documentos\DocumentoEstudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cola de revisión de Expediente SGA, separada de SecretariaController.
 * No es CRUD: secretaría no crea ni edita documentos, solo los aprueba o
 * rechaza. Sigue un patrón adaptado Index + Show (la acción de
 * aprobar/rechazar vive en Show.vue, no en un modal dentro del Index).
 */
class ExpedienteController extends Controller
{
    use HasBreadcrumbs;

    private function transform(DocumentoEstudiante $doc): array
    {
        $archivoData = $doc->archivo_url;

        return [
            'id' => $doc->id,
            'tipo_documento' => $doc->tipo_documento,
            'estado' => $doc->estado,
            'observacion' => $doc->observacion,
            'reviewed_at' => $doc->reviewed_at?->diffForHumans(),
            'reviewed_at_raw' => $doc->reviewed_at?->format('d/m/Y H:i'),
            'created_at' => $doc->created_at->format('d/m/Y H:i'),
            'archivo_url' => $doc->archivo_publico_url,
            'archivo_meta' => $archivoData ? [
                'name' => $archivoData['name'] ?? 'documento.pdf',
                'size' => isset($archivoData['size']) ? round($archivoData['size'] / 1024, 1) . ' KB' : null,
            ] : null,
            'estudiante' => [
                'id' => $doc->estudiante->id,
                'name' => $doc->estudiante->name,
                'email' => $doc->estudiante->email,
                'cedula' => $doc->estudiante->cedula,
                'carrera' => $doc->estudiante->carrera?->nombre,
            ],
            'revisado_por' => $doc->secretaria?->name,
        ];
    }

    public function index(Request $request): Response
    {
        $q = $request->input('q');
        $estado = $request->input('estado', 'all');
        $tipo = $request->input('tipo', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = DocumentoEstudiante::with(['estudiante.carrera', 'secretaria'])
            ->whereNull('deleted_at');

        if ($q) {
            $query->whereHas('estudiante', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('cedula', 'like', "%{$q}%");
            });
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        }

        if ($tipo && $tipo !== 'all') {
            $query->where('tipo_documento', $tipo);
        }

        $documentos = $query->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 WHEN 'rechazado' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($doc) => $this->transform($doc));

        $tiposDocumento = DocumentoEstudiante::distinct()->pluck('tipo_documento');

        return Inertia::render('Secretaria/Expedientes/Index', [
            'documentos' => $documentos,
            'tiposDocumento' => $tiposDocumento,
            'filters' => $request->only(['q', 'estado', 'tipo', 'per_page']),
            'stats' => [
                'pendientes' => DocumentoEstudiante::where('estado', 'pendiente')->count(),
                'aprobados' => DocumentoEstudiante::where('estado', 'aprobado')->count(),
                'rechazados' => DocumentoEstudiante::where('estado', 'rechazado')->count(),
                'total' => DocumentoEstudiante::count(),
            ],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Expediente SGA'),
        ]);
    }

    public function show(DocumentoEstudiante $documento): Response
    {
        $documento->load(['estudiante.carrera', 'secretaria']);

        return Inertia::render('Secretaria/Expedientes/Show', [
            'documento' => $this->transform($documento),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Expediente SGA', 'Ver Documento', route('secretaria.expediente.index'), $documento->tipo_documento),
        ]);
    }

    public function review(Request $request, DocumentoEstudiante $documento)
    {
        $request->validate([
            'accion' => 'required|in:aprobar,rechazar',
            'observacion' => 'nullable|string|max:500',
        ]);

        if ($request->accion === 'rechazar') {
            $request->validate([
                'observacion' => 'required|string|min:5|max:500',
            ], [
                'observacion.required' => 'Debes indicar el motivo del rechazo.',
            ]);
        }

        $nuevoEstado = $request->accion === 'aprobar' ? 'aprobado' : 'rechazado';

        $documento->update([
            'estado' => $nuevoEstado,
            'observacion' => $request->observacion,
            'secretaria_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $textoAccion = $nuevoEstado === 'aprobado' ? 'aprobado' : 'rechazado';
        $mensaje = $nuevoEstado === 'aprobado'
            ? "Tu documento \"{$documento->tipo_documento}\" ha sido aprobado por Secretaría. ¡Todo en orden!"
            : "Tu documento \"{$documento->tipo_documento}\" ha sido rechazado. Motivo: {$request->observacion}. Por favor vuelve a subir el documento corregido.";

        Notificacion::create([
            'user_id' => $documento->estudiante_id,
            'titulo' => "Documento {$textoAccion}: {$documento->tipo_documento}",
            'mensaje' => $mensaje,
            'leido' => false,
        ]);

        return redirect()->route('secretaria.expediente.index')->with('success',
            $nuevoEstado === 'aprobado'
                ? 'Documento aprobado correctamente.'
                : 'Documento rechazado. Se ha notificado al estudiante.'
        );
    }
}
