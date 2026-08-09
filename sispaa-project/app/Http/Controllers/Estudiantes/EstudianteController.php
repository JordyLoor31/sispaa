<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\Notificacion;
use App\Models\Documentos\DocumentoEstudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vistas de gestión/staff sobre estudiantes: listado institucional de los
 * estudiantes registrados en el sistema (las matrículas se retiraron del
 * sistema). El show de cada estudiante muestra su expediente de documentos
 * (antes ExpedienteController de Secretaría) y permite a la secretaría
 * aprobar/rechazar cada documento sin salir del módulo Estudiantes.
 */
class EstudianteController extends Controller
{
    use HasBreadcrumbs;

    private function transformDocumento(DocumentoEstudiante $doc): array
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
            'revisado_por' => $doc->secretaria?->name,
        ];
    }

    /**
     * Listado de estudiantes del sistema con búsqueda y filtro por carrera.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = $perPage > 0 ? min(100, $perPage) : 12;

        $carreraId = $request->input('carrera_id');
        $q = $request->input('q');

        $base = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('carreras', 'carreras.id', '=', 'users.carrera_id')
            ->leftJoin('documentos_estudiante', 'documentos_estudiante.estudiante_id', '=', 'users.id')
            ->where('roles.name', 'estudiante')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.cedula',
                'users.telefono',
                'users.carrera_id',
                'carreras.nombre as carrera_nombre',
                DB::raw('COUNT(DISTINCT documentos_estudiante.id) as documentos_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.cedula', 'users.telefono', 'users.carrera_id', 'carreras.nombre');

        if ($carreraId) {
            $base->where('users.carrera_id', $carreraId);
        }

        if ($q) {
            $base->where(function ($w) use ($q) {
                $w->where('users.name', 'ilike', "%{$q}%")
                  ->orWhere('users.email', 'ilike', "%{$q}%")
                  ->orWhere('users.cedula', 'ilike', "%{$q}%");
            });
        }

        $students = $base->orderBy('users.name')->paginate($perPage)->withQueryString();

        return Inertia::render('Estudiantes/Index', [
            'students' => $students,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['carrera_id' => $carreraId, 'q' => $q],
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Estudiantes'),
        ]);
    }

    /**
     * Show de un estudiante con su expediente de documentos (vista de la
     * secretaría). Aquí se aprueba/rechaza cada documento, como antes en
     * Expediente SGA.
     */
    public function show(User $estudiante): Response
    {
        abort_unless($estudiante->hasRole('estudiante'), 404);

        $estudiante->load('carrera');

        $documentos = DocumentoEstudiante::with('secretaria')
            ->where('estudiante_id', $estudiante->id)
            ->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 WHEN 'rechazado' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($doc) => $this->transformDocumento($doc));

        return Inertia::render('Estudiantes/Show', [
            'estudiante' => [
                'id' => $estudiante->id,
                'name' => $estudiante->name,
                'email' => $estudiante->email,
                'cedula' => $estudiante->cedula,
                'telefono' => $estudiante->telefono,
                'carrera' => $estudiante->carrera?->nombre,
            ],
            'documentos' => $documentos,
            'stats' => [
                'pendientes' => $documentos->where('estado', 'pendiente')->count(),
                'aprobados' => $documentos->where('estado', 'aprobado')->count(),
                'rechazados' => $documentos->where('estado', 'rechazado')->count(),
                'total' => $documentos->count(),
            ],
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Estudiantes', 'Expediente del Estudiante', route('estudiantes.index'), $estudiante->name),
        ]);
    }

    /**
     * Aprueba o rechaza un documento del expediente de un estudiante.
     */
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

        return redirect()->route('estudiantes.show', $documento->estudiante_id)->with('success',
            $nuevoEstado === 'aprobado'
                ? 'Documento aprobado correctamente.'
                : 'Documento rechazado. Se ha notificado al estudiante.'
        );
    }
}
