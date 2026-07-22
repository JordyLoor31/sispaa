<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Notificacion;
use App\Models\Estudiantes\JustificacionSolicitud;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cola de revisión de Justificaciones, separada de SecretariaController.
 * No es CRUD: secretaría no crea justificaciones, solo las aprueba o
 * rechaza. Sigue un patrón adaptado Index + Show (la acción de
 * aprobar/rechazar vive en Show.vue, no en un modal dentro del Index).
 */
class JustificacionController extends Controller
{
    use HasBreadcrumbs;

    private function transform(JustificacionSolicitud $j): array
    {
        return [
            'id' => $j->id,
            'estado' => $j->estado,
            'motivo_estudiante' => $j->motivo_estudiante,
            'comentario_revisor' => $j->comentario_revisor,
            'archivo_url' => $j->archivo_url,
            'created_at' => $j->created_at->format('d/m/Y H:i'),
            'falta' => [
                'id' => $j->falta->id,
                'fecha' => $j->falta->fecha->format('d/m/Y'),
                'materia' => $j->falta->materia?->nombre,
            ],
            'estudiante' => [
                'id' => $j->falta->estudiante->id,
                'name' => $j->falta->estudiante->name,
                'email' => $j->falta->estudiante->email,
                'cedula' => $j->falta->estudiante->cedula,
                'carrera' => $j->falta->estudiante->carrera?->nombre,
            ],
        ];
    }

    public function index(Request $request): Response
    {
        $q = $request->input('q');
        $estado = $request->input('estado', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = JustificacionSolicitud::with(['falta.estudiante.carrera', 'falta.materia']);

        if ($q) {
            $query->whereHas('falta.estudiante', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('cedula', 'like', "%{$q}%");
            });
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        }

        $justificaciones = $query
            ->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($j) => $this->transform($j));

        return Inertia::render('Secretaria/Justificaciones/Index', [
            'justificaciones' => $justificaciones,
            'filters' => $request->only(['q', 'estado', 'per_page']),
            'stats' => [
                'pendientes' => JustificacionSolicitud::where('estado', 'pendiente')->count(),
                'aprobadas' => JustificacionSolicitud::where('estado', 'aprobada')->count(),
                'rechazadas' => JustificacionSolicitud::where('estado', 'rechazada')->count(),
            ],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Justificaciones'),
        ]);
    }

    public function show(JustificacionSolicitud $justificacion): Response
    {
        $justificacion->load(['falta.estudiante.carrera', 'falta.materia']);

        return Inertia::render('Secretaria/Justificaciones/Show', [
            'justificacion' => $this->transform($justificacion),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Justificaciones', 'Ver Solicitud', route('secretaria.justificaciones.index'), $justificacion->falta->estudiante->name),
        ]);
    }

    public function review(Request $request, JustificacionSolicitud $justificacion)
    {
        $request->validate([
            'accion' => 'required|in:aprobar,rechazar',
            'comentario_revisor' => 'nullable|string|max:500',
        ]);

        if ($request->accion === 'rechazar') {
            $request->validate([
                'comentario_revisor' => 'required|string|min:5',
            ], [
                'comentario_revisor.required' => 'Debes indicar el motivo del rechazo.',
            ]);
        }

        $nuevoEstado = $request->accion === 'aprobar' ? 'aprobada' : 'rechazada';

        $justificacion->update([
            'estado' => $nuevoEstado,
            'comentario_revisor' => $request->comentario_revisor,
        ]);

        if ($nuevoEstado === 'aprobada') {
            $justificacion->falta->update(['justificada' => true]);
        }

        $estudianteId = $justificacion->falta->estudiante_id;
        $materia = $justificacion->falta->materia?->nombre ?? 'asignatura';
        $fecha = $justificacion->falta->fecha->format('d/m/Y');

        $titulo = $nuevoEstado === 'aprobada'
            ? "Justificación aprobada — {$materia}"
            : "Justificación rechazada — {$materia}";

        $mensaje = $nuevoEstado === 'aprobada'
            ? "Tu solicitud de justificación para la falta del {$fecha} en {$materia} ha sido APROBADA por Secretaría."
            : "Tu solicitud de justificación para la falta del {$fecha} en {$materia} ha sido RECHAZADA. Motivo: {$request->comentario_revisor}";

        Notificacion::create([
            'user_id' => $estudianteId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leido' => false,
        ]);

        return redirect()->route('secretaria.justificaciones.index')->with('success',
            $nuevoEstado === 'aprobada'
                ? 'Justificación aprobada. La falta ha sido marcada como justificada.'
                : 'Justificación rechazada. Se ha notificado al estudiante.'
        );
    }
}
