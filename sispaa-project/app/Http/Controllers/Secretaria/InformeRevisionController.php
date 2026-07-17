<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\Notificacion;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\InformeDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cola de revisión de Informes de Asignatura, análoga a
 * SilaboRevisionController pero para InformeDocente (tipo='asignatura').
 * Reemplaza la vista ad-hoc que vivía en
 * AdminPortalController::informesAsignatura() + Docencia/Informes.vue: ahora
 * sigue el mismo patrón Index + Show (con revisión) que Sílabos y
 * Justificaciones.
 */
class InformeRevisionController extends Controller
{
    use HasBreadcrumbs;

    private function transform(InformeDocente $i): array
    {
        return [
            'id' => $i->id,
            'estado' => $i->estado,
            'archivo_url' => $i->archivo_url,
            'observaciones' => $i->observaciones,
            'fecha_subida' => $i->fecha_subida?->format('d/m/Y H:i'),
            'docente' => [
                'id' => $i->docente->id,
                'name' => $i->docente->name,
                'email' => $i->docente->email,
            ],
            'materia' => $i->materia->nombre,
            'carrera' => $i->materia->carrera?->nombre,
            'periodo' => $i->periodo->nombre,
            // URL de visualización servida por Laravel, no depende del
            // symlink public/storage (ver SilaboController::ver() en el
            // namespace Docencia para el detalle del porqué).
            'ver_url' => $i->archivo_url ? route('secretaria.informes.ver', $i->id) : null,
        ];
    }

    public function index(Request $request): Response
    {
        $q = $request->input('q');
        $carreraId = $request->input('carrera_id', 'all');
        $estado = $request->input('estado', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 10)));

        $query = InformeDocente::with(['docente', 'materia.carrera', 'periodo'])
            ->where('tipo', 'asignatura');

        if ($q) {
            $query->whereHas('docente', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($carreraId && $carreraId !== 'all') {
            $query->whereHas('materia', fn ($m) => $m->where('carrera_id', $carreraId));
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        }

        $informes = $query
            ->orderByRaw("CASE estado WHEN 'subido' THEN 0 WHEN 'pendiente' THEN 1 ELSE 2 END")
            ->orderBy('fecha_subida', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($i) => $this->transform($i));

        // El resumen/gráfico se calcula sobre TODOS los informes que
        // coinciden con el filtro de carrera (no solo la página actual),
        // para que el porcentaje mostrado sea real.
        $statsQuery = InformeDocente::where('tipo', 'asignatura');
        if ($carreraId && $carreraId !== 'all') {
            $statsQuery->whereHas('materia', fn ($m) => $m->where('carrera_id', $carreraId));
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'aprobados' => (clone $statsQuery)->where('estado', 'aprobado')->count(),
            'subidos' => (clone $statsQuery)->where('estado', 'subido')->count(),
            'pendientes' => (clone $statsQuery)->where('estado', 'pendiente')->count(),
        ];

        return Inertia::render('Secretaria/Informes/Index', [
            'informes' => $informes,
            'filters' => $request->only(['q', 'carrera_id', 'estado', 'per_page']),
            'stats' => $stats,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Informes de Asignatura'),
        ]);
    }

    public function show(InformeDocente $informe): Response
    {
        abort_unless($informe->tipo === 'asignatura', 404);

        $informe->load(['docente', 'materia.carrera', 'periodo']);

        return Inertia::render('Secretaria/Informes/Show', [
            'informe' => $this->transform($informe),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Informes de Asignatura', 'Ver Informe', route('secretaria.informes.index'), $informe->materia->nombre),
        ]);
    }

    public function review(Request $request, InformeDocente $informe)
    {
        abort_unless($informe->tipo === 'asignatura', 404);

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

        $informe->update([
            'estado' => $nuevoEstado,
            'observaciones' => $request->observaciones,
        ]);

        $informe->load('materia');

        // El informe se comparte por materia+período: se notifica a todos
        // los docentes asignados a esa materia en ese período (todos los
        // paralelos), igual que en SilaboRevisionController::review().
        $docentesANotificar = AsignacionDocente::where('materia_id', $informe->materia_id)
            ->where('periodo_id', $informe->periodo_id)
            ->pluck('docente_id')
            ->unique();

        foreach ($docentesANotificar as $docenteId) {
            Notificacion::create([
                'user_id' => $docenteId,
                'titulo' => $nuevoEstado === 'aprobado'
                    ? "Informe aprobado: {$informe->materia->nombre}"
                    : "Informe rechazado: {$informe->materia->nombre}",
                'mensaje' => $nuevoEstado === 'aprobado'
                    ? "Tu informe de asignatura de \"{$informe->materia->nombre}\" ha sido aprobado por Secretaría."
                    : "Tu informe de asignatura de \"{$informe->materia->nombre}\" fue rechazado. Motivo: {$request->observaciones}. Vuelve a subirlo corregido.",
                'leido' => false,
            ]);
        }

        return redirect()->route('secretaria.informes.index')->with('success',
            $nuevoEstado === 'aprobado'
                ? 'Informe aprobado correctamente.'
                : 'Informe rechazado. Se ha notificado a los docentes.'
        );
    }

    /**
     * Sirve el archivo del informe vía Laravel. Ver el comentario en
     * SilaboController::ver() (namespace Docencia) para el detalle de por
     * qué no se usa el enlace directo /storage/... (403 típico en XAMPP por
     * symlink). El acceso ya está restringido a Secretaría/SystemAdministrador
     * por el middleware del grupo de rutas.
     */
    public function ver(InformeDocente $informe)
    {
        abort_unless($informe->tipo === 'asignatura', 404);

        $ruta = $informe->archivo_url ? Str::after($informe->archivo_url, '/storage/') : null;

        abort_unless($ruta && Storage::disk('public')->exists($ruta), 404, 'El archivo ya no está disponible.');

        return Storage::disk('public')->response($ruta);
    }
}
