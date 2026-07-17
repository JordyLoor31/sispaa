<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Titulacion\Titulacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TitulacionController extends Controller
{
    use HasBreadcrumbs;

    /**
     * true si el usuario autenticado ve TODOS los procesos de titulación
     * (coordinador, secretaría, SystemAdministrador) en vez de solo los
     * propios. Controla el ALCANCE de lectura (qué filas devuelve la
     * consulta), no si puede editarlas.
     */
    private function veTodosLosProcesos(): bool
    {
        return Auth::user()->hasAnyRole(['coordinador', 'secretaria', 'SystemAdministrador']);
    }

    /**
     * true si el usuario autenticado puede crear/editar/eliminar procesos
     * de titulación (coordinador, SystemAdministrador). Secretaría y
     * docente solo tienen acceso de lectura: las rutas de escritura ya
     * están restringidas a nivel de middleware, pero esto también se usa
     * para ocultar los controles de edición en el frontend.
     */
    private function puedeGestionar(): bool
    {
        return Auth::user()->hasAnyRole(['coordinador', 'SystemAdministrador']);
    }

    /**
     * Panel único de Titulación para el coordinador: consolida lo que en el
     * sistema origen eran 3 vistas separadas (temas en desarrollo, estudiantes
     * en proceso, estudiantes titulados) en una sola pantalla con filtro por
     * estado, ya que las 3 comparten exactamente el mismo modelo de datos.
     */
    public function index(Request $request): Response
    {
        $estado = $request->input('estado', 'all');
        $veTodos = $this->veTodosLosProcesos();

        $query = Titulacion::with(['estudiante', 'tutor']);
        if (!$veTodos) {
            $query->where('tutor_id', Auth::id());
        }
        if ($estado !== 'all') {
            $query->where('estado', $estado);
        }

        $titulaciones = $query->orderByDesc('created_at')->get()->map(fn ($t) => [
            'id' => $t->id,
            'tema' => $t->tema,
            'estado' => $t->estado,
            'fecha_inicio' => $t->fecha_inicio?->format('Y-m-d'),
            'fecha_graduacion' => $t->fecha_graduacion?->format('Y-m-d'),
            'estudiante' => ['id' => $t->estudiante->id, 'name' => $t->estudiante->name],
            'tutor' => ['id' => $t->tutor->id, 'name' => $t->tutor->name],
        ]);

        // Los contadores del header también se acotan al alcance del
        // usuario: un docente no debe ver el total institucional.
        $statsQuery = fn () => Titulacion::when(!$veTodos, fn ($q) => $q->where('tutor_id', Auth::id()));

        return Inertia::render('Titulacion/Index', [
            'titulaciones' => $titulaciones,
            'filters' => ['estado' => $estado],
            'stats' => [
                'en_proceso' => $statsQuery()->where('estado', 'en_proceso')->count(),
                'defendido' => $statsQuery()->where('estado', 'defendido')->count(),
                'graduado' => $statsQuery()->where('estado', 'graduado')->count(),
                'total' => $statsQuery()->count(),
            ],
            'puedeGestionar' => $this->puedeGestionar(),
            'breadcrumbs' => $this->titulacionBreadcrumbs('Procesos de Titulación'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Titulacion/Create', [
            'estudiantes' => User::role('estudiante')->get(['id', 'name']),
            'tutores' => User::role('docente')->get(['id', 'name']),
            'breadcrumbs' => $this->titulacionBreadcrumbs('Procesos de Titulación', 'Registrar Tema', route('titulacion.index')),
        ]);
    }

    /**
     * Registrar el tema y tutor de titulación de un estudiante.
     */
    public function store(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'tutor_id' => 'required|exists:users,id',
            'tema' => 'required|string|max:255',
            'fecha_inicio' => 'nullable|date',
        ]);

        Titulacion::create([
            'estudiante_id' => $request->estudiante_id,
            'tutor_id' => $request->tutor_id,
            'tema' => $request->tema,
            'fecha_inicio' => $request->fecha_inicio,
            'estado' => 'en_proceso',
        ]);

        return redirect()->route('titulacion.index')->with('success', 'Proceso de titulación registrado.');
    }

    public function show(Titulacion $titulacion): Response
    {
        abort_unless(
            $this->veTodosLosProcesos() || $titulacion->tutor_id === Auth::id(),
            403,
            'No tienes permiso para ver este proceso de titulación.'
        );

        $titulacion->load(['estudiante', 'tutor', 'creator', 'updater']);

        return Inertia::render('Titulacion/Show', [
            'titulacion' => $titulacion,
            'puedeGestionar' => $this->puedeGestionar(),
            'breadcrumbs' => $this->titulacionBreadcrumbs('Procesos de Titulación', 'Ver Proceso', route('titulacion.index'), $titulacion->estudiante->name),
        ]);
    }

    public function edit(Titulacion $titulacion): Response
    {
        return Inertia::render('Titulacion/Edit', [
            'titulacion' => $titulacion->load(['estudiante', 'tutor']),
            'tutores' => User::role('docente')->get(['id', 'name']),
            'breadcrumbs' => $this->titulacionBreadcrumbs('Procesos de Titulación', 'Editar Proceso', route('titulacion.index'), $titulacion->estudiante->name),
        ]);
    }

    /**
     * Editar tema/tutor/fecha de inicio, o avanzar el estado
     * (en_proceso -> defendido -> graduado). Al marcar "graduado" se
     * sella la fecha_graduacion automáticamente si no viene indicada.
     */
    public function update(Request $request, Titulacion $titulacion)
    {
        if ($request->filled('tema') || $request->filled('tutor_id')) {
            $request->validate([
                'tema' => 'required|string|max:255',
                'tutor_id' => 'required|exists:users,id',
                'fecha_inicio' => 'nullable|date',
            ]);
            $titulacion->update($request->only(['tema', 'tutor_id', 'fecha_inicio']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:en_proceso,defendido,graduado']);

            $data = ['estado' => $request->estado];
            if ($request->estado === 'graduado') {
                $data['fecha_graduacion'] = $request->input('fecha_graduacion', now()->toDateString());
            }
            $titulacion->update($data);
        }

        return redirect()->back()->with('success', 'Proceso de titulación actualizado.');
    }

    public function destroy(Titulacion $titulacion)
    {
        $titulacion->delete();

        return redirect()->route('titulacion.index')->with('success', 'Proceso de titulación eliminado.');
    }
}
