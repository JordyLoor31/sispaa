<?php

namespace App\Http\Controllers\Investigacion;

use App\Http\Controllers\Controller;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Investigacion\HitoInvestigacion;
use App\Models\Investigacion\Investigacion;
use App\Models\Investigacion\SeguimientoInvestigacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InvestigacionController extends Controller
{
    /**
     * Reglas de visibilidad (equivalentes a canEditProyecto del sistema origen,
     * extendidas por el campo coordinador_id que sí existe en este esquema):
     * - SystemAdministrador ve todos los proyectos.
     * - Cualquier otro usuario ve solo los proyectos donde es el docente dueño
     *   o el coordinador asignado como supervisor.
     */
    private function puedeVerTodo(): bool
    {
        return Auth::user()->hasRole('SystemAdministrador');
    }

    private function scopeVisible($query)
    {
        if ($this->puedeVerTodo()) {
            return $query;
        }

        $userId = Auth::id();

        return $query->where(function ($q) use ($userId) {
            $q->where('docente_id', $userId)->orWhere('coordinador_id', $userId);
        });
    }

    private function authorizeView(Investigacion $investigacion): void
    {
        $ok = $this->puedeVerTodo()
            || $investigacion->docente_id === Auth::id()
            || $investigacion->coordinador_id === Auth::id();

        if (!$ok) {
            abort(403);
        }
    }

    /**
     * Mis Proyectos de Investigación (docente) / Proyectos Supervisados (coordinador).
     */
    public function index(Request $request): Response
    {
        $query = Investigacion::with(['docente', 'coordinador', 'periodo', 'hitos']);
        $query = $this->scopeVisible($query);

        $estado = $request->input('estado', 'all');
        if ($estado !== 'all') {
            $query->where('estado', $estado);
        }

        $proyectos = $query->orderByDesc('created_at')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'objetivo' => $p->objetivo,
                'estado' => $p->estado,
                'docente' => ['id' => $p->docente->id, 'name' => $p->docente->name],
                'coordinador' => ['id' => $p->coordinador->id, 'name' => $p->coordinador->name],
                'periodo' => $p->periodo->nombre,
                'total_hitos' => $p->hitos->count(),
                'hitos_completados' => $p->hitos->where('porcentaje_avance', 100)->count(),
                'es_propio' => $p->docente_id === Auth::id(),
                'es_coordinador' => $p->coordinador_id === Auth::id(),
            ];
        });

        return Inertia::render('Investigacion/Index', [
            'proyectos' => $proyectos,
            'periodos' => PeriodoAcademico::where('activo', true)->get(['id', 'nombre']),
            'coordinadores' => User::role('coordinador')->get(['id', 'name']),
            'filters' => ['estado' => $estado],
        ]);
    }

    /**
     * Crear un proyecto propio (todo docente puede; un coordinador también,
     * porque un coordinador siempre es también docente).
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'objetivo' => 'nullable|string|max:2000',
            'periodo_id' => 'required|exists:periodos_academicos,id',
            'coordinador_id' => 'required|exists:users,id',
        ]);

        Investigacion::create([
            'docente_id' => Auth::id(),
            'coordinador_id' => $request->coordinador_id,
            'periodo_id' => $request->periodo_id,
            'titulo' => $request->titulo,
            'objetivo' => $request->objetivo,
            'estado' => 'en_curso',
        ]);

        return redirect()->back()->with('success', 'Proyecto de investigación creado.');
    }

    /**
     * El docente dueño edita título/objetivo; el docente dueño o el
     * coordinador asignado pueden cambiar el estado (en_curso/pausada/finalizada).
     */
    public function update(Request $request, Investigacion $investigacion)
    {
        $isOwner = $investigacion->docente_id === Auth::id();
        $isCoordinador = $investigacion->coordinador_id === Auth::id();

        if (!$isOwner && !$isCoordinador && !$this->puedeVerTodo()) {
            abort(403);
        }

        if (($isOwner || $this->puedeVerTodo()) && $request->filled('titulo')) {
            $request->validate([
                'titulo' => 'required|string|max:255',
                'objetivo' => 'nullable|string|max:2000',
            ]);
            $investigacion->update($request->only(['titulo', 'objetivo']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:en_curso,pausada,finalizada']);
            $investigacion->update(['estado' => $request->estado]);
        }

        return redirect()->back()->with('success', 'Proyecto actualizado.');
    }

    public function destroy(Investigacion $investigacion)
    {
        if ($investigacion->docente_id !== Auth::id() && !$this->puedeVerTodo()) {
            abort(403);
        }

        $investigacion->delete();

        return redirect()->back()->with('success', 'Proyecto eliminado.');
    }

    /**
     * Hitos y seguimiento de un proyecto puntual.
     */
    public function hitos(Investigacion $investigacion): Response
    {
        $this->authorizeView($investigacion);

        return Inertia::render('Investigacion/Hitos', [
            'proyecto' => [
                'id' => $investigacion->id,
                'titulo' => $investigacion->titulo,
                'estado' => $investigacion->estado,
            ],
            'hitos' => $investigacion->hitos()->orderBy('fecha_planificada')->get(),
            'seguimiento' => $investigacion->seguimientos()->orderBy('orden')->get(),
            'esDueno' => $investigacion->docente_id === Auth::id() || $this->puedeVerTodo(),
            'esCoordinador' => $investigacion->coordinador_id === Auth::id() || $this->puedeVerTodo(),
        ]);
    }

    public function storeHito(Request $request, Investigacion $investigacion)
    {
        if ($investigacion->docente_id !== Auth::id() && !$this->puedeVerTodo()) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fecha_planificada' => 'nullable|date',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
        ]);

        $investigacion->hitos()->create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fecha_planificada' => $request->fecha_planificada,
            'fecha_cumplida' => $request->porcentaje_avance == 100 ? now() : null,
            'porcentaje_avance' => $request->porcentaje_avance,
        ]);

        return redirect()->back()->with('success', 'Hito agregado.');
    }

    public function updateHito(Request $request, HitoInvestigacion $hito)
    {
        $investigacion = $hito->investigacion;

        if ($investigacion->docente_id !== Auth::id() && !$this->puedeVerTodo()) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fecha_planificada' => 'nullable|date',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
        ]);

        $hito->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fecha_planificada' => $request->fecha_planificada,
            'fecha_cumplida' => $request->porcentaje_avance == 100 ? ($hito->fecha_cumplida ?? now()) : null,
            'porcentaje_avance' => $request->porcentaje_avance,
        ]);

        return redirect()->back()->with('success', 'Hito actualizado.');
    }

    /**
     * Seguimiento tipo pregunta/respuesta: el coordinador asignado plantea la
     * pregunta, el docente dueño del proyecto la responde.
     */
    public function storeSeguimiento(Request $request, Investigacion $investigacion)
    {
        if ($investigacion->coordinador_id !== Auth::id() && !$this->puedeVerTodo()) {
            abort(403, 'Solo el coordinador asignado puede agregar preguntas de seguimiento.');
        }

        $request->validate(['pregunta' => 'required|string|max:500']);

        $orden = ($investigacion->seguimientos()->max('orden') ?? 0) + 1;

        $investigacion->seguimientos()->create([
            'pregunta' => $request->pregunta,
            'orden' => $orden,
        ]);

        return redirect()->back()->with('success', 'Pregunta de seguimiento agregada.');
    }

    public function responderSeguimiento(Request $request, SeguimientoInvestigacion $seguimiento)
    {
        $investigacion = $seguimiento->investigacion;

        if ($investigacion->docente_id !== Auth::id() && !$this->puedeVerTodo()) {
            abort(403, 'Solo el docente dueño del proyecto puede responder.');
        }

        $request->validate(['respuesta' => 'required|string|max:2000']);

        $seguimiento->update(['respuesta' => $request->respuesta]);

        return redirect()->back()->with('success', 'Respuesta guardada.');
    }
}
