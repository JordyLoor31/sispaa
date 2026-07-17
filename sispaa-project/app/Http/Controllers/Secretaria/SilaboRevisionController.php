<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Notificacion;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\Silabo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cola de revisión de Sílabos, separada de SecretariaController. No es
 * CRUD: secretaría no crea sílabos, solo los aprueba o rechaza. Se llama
 * SilaboRevisionController (no SilaboController) para no chocar en el
 * nombre con App\Http\Controllers\Docencia\SilaboController (autoservicio
 * del docente), aunque viven en namespaces distintos.
 * Sigue un patrón adaptado Index + Show (la acción de aprobar/rechazar
 * vive en Show.vue, no en un modal dentro del Index).
 */
class SilaboRevisionController extends Controller
{
    use HasBreadcrumbs;

    private function transform(Silabo $s): array
    {
        return [
            'id' => $s->id,
            'estado' => $s->estado,
            'archivo_url' => $s->archivo_url,
            'observaciones' => $s->observaciones,
            'fecha_subida' => $s->fecha_subida?->format('d/m/Y H:i'),
            'docente' => [
                'id' => $s->docente->id,
                'name' => $s->docente->name,
                'email' => $s->docente->email,
            ],
            'materia' => $s->materia->nombre,
            'carrera' => $s->materia->carrera?->nombre,
            'periodo' => $s->periodo->nombre,
        ];
    }

    public function index(Request $request): Response
    {
        $q = $request->input('q');
        $estado = $request->input('estado', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = Silabo::with(['docente', 'materia.carrera', 'periodo'])
            ->whereNull('deleted_at');

        if ($q) {
            $query->whereHas('docente', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        }

        $silabos = $query
            ->orderByRaw("CASE estado WHEN 'subido' THEN 0 WHEN 'pendiente' THEN 1 ELSE 2 END")
            ->orderBy('fecha_subida', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($s) => $this->transform($s));

        return Inertia::render('Secretaria/Silabos/Index', [
            'silabos' => $silabos,
            'filters' => $request->only(['q', 'estado', 'per_page']),
            'stats' => [
                'pendientes' => Silabo::whereIn('estado', ['pendiente', 'subido'])->count(),
                'aprobados' => Silabo::where('estado', 'aprobado')->count(),
                'total' => Silabo::count(),
            ],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Revisión de Sílabos'),
        ]);
    }

    public function show(Silabo $silabo): Response
    {
        $silabo->load(['docente', 'materia.carrera', 'periodo']);

        return Inertia::render('Secretaria/Silabos/Show', [
            'silabo' => $this->transform($silabo),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Revisión de Sílabos', 'Ver Sílabo', route('secretaria.silabos.index'), $silabo->materia->nombre),
        ]);
    }

    public function review(Request $request, Silabo $silabo)
    {
        $request->validate([
            'accion' => 'required|in:aprobar,rechazar',
            'observaciones' => 'nullable|string|max:500',
        ]);

        if ($request->accion === 'rechazar') {
            $request->validate([
                'observaciones' => 'required|string|min:5|max:500',
            ], [
                'observaciones.required' => 'Debes indicar el motivo del rechazo.',
            ]);
        }

        $nuevoEstado = $request->accion === 'aprobar' ? 'aprobado' : 'pendiente';

        $silabo->update([
            'estado' => $nuevoEstado,
            'observaciones' => $request->observaciones,
        ]);

        $silabo->load('materia');

        // El sílabo es compartido por materia+período: se notifica a todos
        // los docentes asignados a esa materia en ese período (todos los
        // paralelos), no solo a quien lo subió.
        $docentesANotificar = AsignacionDocente::where('materia_id', $silabo->materia_id)
            ->where('periodo_id', $silabo->periodo_id)
            ->pluck('docente_id')
            ->unique();

        foreach ($docentesANotificar as $docenteId) {
            Notificacion::create([
                'user_id' => $docenteId,
                'titulo' => $nuevoEstado === 'aprobado'
                    ? "Sílabo aprobado: {$silabo->materia->nombre}"
                    : "Sílabo rechazado: {$silabo->materia->nombre}",
                'mensaje' => $nuevoEstado === 'aprobado'
                    ? "Tu sílabo de \"{$silabo->materia->nombre}\" ha sido aprobado por Secretaría."
                    : "Tu sílabo de \"{$silabo->materia->nombre}\" fue rechazado. Motivo: {$request->observaciones}. Vuelve a subirlo corregido.",
                'leido' => false,
            ]);
        }

        return redirect()->route('secretaria.silabos.index')->with('success',
            $nuevoEstado === 'aprobado'
                ? 'Sílabo aprobado correctamente.'
                : 'Sílabo rechazado. Se ha notificado al docente.'
        );
    }
}
