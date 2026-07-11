<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\Materia;
use App\Models\Estudiantes\Matricula;
use App\Models\Laboratorio\AsistenciaPractica;
use App\Models\Laboratorio\Equipo;
use App\Models\Laboratorio\Laboratorio;
use App\Models\Laboratorio\PracticaLaboratorio;
use App\Models\Laboratorio\Reactivo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LaboratorioController extends Controller
{
    private function puedeVerTodo(): bool
    {
        return Auth::user()->hasRole('SystemAdministrador');
    }

    private function authorizePractica(PracticaLaboratorio $practica): void
    {
        if ($practica->docente_id !== Auth::id() && !$this->puedeVerTodo()) {
            abort(403);
        }
    }

    /**
     * Dashboard general del módulo (KPIs reales, sin datos simulados).
     */
    public function index(): Response
    {
        $ultimasPracticas = PracticaLaboratorio::with(['materia.carrera', 'docente', 'laboratorio'])
            ->orderByDesc('fecha')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'tema' => $p->tema,
                'materia' => $p->materia->nombre,
                'carrera' => $p->materia->carrera->nombre,
                'docente' => $p->docente->name,
                'laboratorio' => $p->laboratorio?->nombre,
                'numero_estudiantes' => $p->numero_estudiantes,
                'fecha' => $p->fecha?->format('Y-m-d'),
            ]);

        $laboratoriosMasUsados = Laboratorio::withCount('practicas')
            ->orderByDesc('practicas_count')
            ->limit(5)
            ->get(['id', 'nombre'])
            ->map(fn ($l) => ['id' => $l->id, 'nombre' => $l->nombre, 'usos' => $l->practicas_count]);

        return Inertia::render('Laboratorio/Index', [
            'stats' => [
                'total_practicas' => PracticaLaboratorio::count(),
                'laboratorios_activos' => Laboratorio::where('estado', 'activo')->count(),
                'total_equipos' => Equipo::count(),
                'total_reactivos' => Reactivo::count(),
                'estudiantes_atendidos' => (int) PracticaLaboratorio::sum('numero_estudiantes'),
            ],
            'ultimasPracticas' => $ultimasPracticas,
            'laboratoriosMasUsados' => $laboratoriosMasUsados,
        ]);
    }

    /**
     * Listado y CRUD de prácticas. Cualquier docente ve todas las prácticas
     * (recurso institucional compartido, útil para no chocar horarios de
     * laboratorio), pero solo puede editar/eliminar las suyas.
     */
    public function practicas(Request $request): Response
    {
        $periodoId = $request->input('periodo_id', 'all');

        $query = PracticaLaboratorio::with(['materia.carrera', 'docente', 'periodo', 'laboratorio', 'equipos', 'reactivos']);
        if ($periodoId !== 'all') {
            $query->where('periodo_id', $periodoId);
        }

        $practicas = $query->orderByDesc('fecha')->get()->map(fn ($p) => [
            'id' => $p->id,
            'numero_practica' => $p->numero_practica,
            'tema' => $p->tema,
            'subtema' => $p->subtema,
            'logro_aprendizaje' => $p->logro_aprendizaje,
            'semestre' => $p->semestre,
            'numero_estudiantes' => $p->numero_estudiantes,
            'horario' => $p->horario,
            'objetivo' => $p->objetivo,
            'metodologia' => $p->metodologia,
            'resultados' => $p->resultados,
            'conclusiones' => $p->conclusiones,
            'fecha' => $p->fecha?->format('Y-m-d'),
            'materia' => ['id' => $p->materia->id, 'nombre' => $p->materia->nombre],
            'carrera' => $p->materia->carrera->nombre,
            'docente' => ['id' => $p->docente->id, 'name' => $p->docente->name],
            'laboratorio' => $p->laboratorio ? ['id' => $p->laboratorio->id, 'nombre' => $p->laboratorio->nombre] : null,
            'periodo' => $p->periodo->nombre,
            'equipos' => $p->equipos->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre, 'cantidad_usada' => $e->pivot->cantidad_usada]),
            'reactivos' => $p->reactivos->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->nombre, 'cantidad_usada' => $r->pivot->cantidad_usada]),
            'es_propio' => $p->docente_id === Auth::id() || $this->puedeVerTodo(),
        ]);

        return Inertia::render('Laboratorio/Practicas', [
            'practicas' => $practicas,
            'materias' => Materia::where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'periodos' => PeriodoAcademico::where('activo', true)->get(['id', 'nombre']),
            'equiposCatalogo' => Equipo::orderBy('nombre')->get(['id', 'nombre']),
            'reactivosCatalogo' => Reactivo::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['periodo_id' => $periodoId],
        ]);
    }

    private function reglasPractica(): array
    {
        return [
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos_academicos,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'numero_practica' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'tema' => 'required|string|max:255',
            'subtema' => 'nullable|string|max:255',
            'logro_aprendizaje' => 'nullable|string|max:255',
            'semestre' => 'nullable|string|max:50',
            'numero_estudiantes' => 'nullable|integer|min:0',
            'horario' => 'nullable|string|max:100',
            'objetivo' => 'nullable|string|max:2000',
            'metodologia' => 'nullable|string|max:2000',
            'resultados' => 'nullable|string|max:2000',
            'conclusiones' => 'nullable|string|max:2000',
            'equipos' => 'nullable|array',
            'equipos.*.id' => 'required_with:equipos|exists:equipos,id',
            'equipos.*.cantidad_usada' => 'nullable|integer|min:1',
            'reactivos' => 'nullable|array',
            'reactivos.*.id' => 'required_with:reactivos|exists:reactivos,id',
            'reactivos.*.cantidad_usada' => 'nullable|integer|min:1',
        ];
    }

    private function syncEquiposReactivos(PracticaLaboratorio $practica, array $data): void
    {
        $equipos = collect($data['equipos'] ?? [])
            ->mapWithKeys(fn ($e) => [$e['id'] => ['cantidad_usada' => $e['cantidad_usada'] ?? 1]]);
        $practica->equipos()->sync($equipos);

        $reactivos = collect($data['reactivos'] ?? [])
            ->mapWithKeys(fn ($r) => [$r['id'] => ['cantidad_usada' => $r['cantidad_usada'] ?? 1]]);
        $practica->reactivos()->sync($reactivos);
    }

    public function storePractica(Request $request)
    {
        $validated = $request->validate($this->reglasPractica());

        $practica = PracticaLaboratorio::create(array_merge(
            collect($validated)->except(['equipos', 'reactivos'])->all(),
            ['docente_id' => Auth::id()]
        ));

        $this->syncEquiposReactivos($practica, $validated);

        return redirect()->back()->with('success', 'Práctica de laboratorio registrada.');
    }

    public function updatePractica(Request $request, PracticaLaboratorio $practica)
    {
        $this->authorizePractica($practica);

        $validated = $request->validate($this->reglasPractica());

        $practica->update(collect($validated)->except(['equipos', 'reactivos'])->all());
        $this->syncEquiposReactivos($practica, $validated);

        return redirect()->back()->with('success', 'Práctica actualizada.');
    }

    public function destroyPractica(PracticaLaboratorio $practica)
    {
        $this->authorizePractica($practica);

        $practica->delete();

        return redirect()->back()->with('success', 'Práctica eliminada.');
    }

    /**
     * Asistencia de una práctica puntual: roster de estudiantes matriculados
     * en la misma carrera/período de la materia de la práctica.
     */
    public function asistencia(PracticaLaboratorio $practica): Response
    {
        $this->authorizePractica($practica);

        $practica->load('materia.carrera');

        $estudianteIds = Matricula::where('carrera_id', $practica->materia->carrera_id)
            ->where('periodo_id', $practica->periodo_id)
            ->pluck('estudiante_id');

        $asistenciasExistentes = $practica->asistencias()->pluck('asistio', 'estudiante_id');

        $roster = User::whereIn('id', $estudianteIds)->orderBy('name')->get(['id', 'name'])->map(fn ($e) => [
            'id' => $e->id,
            'name' => $e->name,
            'asistio' => (bool) ($asistenciasExistentes[$e->id] ?? false),
        ]);

        return Inertia::render('Laboratorio/Asistencia', [
            'practica' => [
                'id' => $practica->id,
                'tema' => $practica->tema,
                'fecha' => $practica->fecha?->format('Y-m-d'),
                'materia' => $practica->materia->nombre,
                'carrera' => $practica->materia->carrera->nombre,
            ],
            'roster' => $roster,
        ]);
    }

    public function storeAsistencia(Request $request, PracticaLaboratorio $practica)
    {
        $this->authorizePractica($practica);

        $request->validate([
            'asistencias' => 'required|array',
            'asistencias.*.estudiante_id' => 'required|exists:users,id',
            'asistencias.*.asistio' => 'required|boolean',
        ]);

        foreach ($request->asistencias as $item) {
            AsistenciaPractica::updateOrCreate(
                ['practica_id' => $practica->id, 'estudiante_id' => $item['estudiante_id']],
                ['asistio' => $item['asistio']]
            );
        }

        return redirect()->back()->with('success', 'Asistencia guardada.');
    }

    /**
     * CRUD de laboratorios (espacios físicos). Antes era la vista estática
     * "Ubicaciones"; ahora administra el catálogo real usado por prácticas,
     * equipos y reactivos.
     */
    public function laboratorios(): Response
    {
        $laboratorios = Laboratorio::with(['carrera', 'responsable'])
            ->withCount(['equipos', 'reactivos', 'practicas'])
            ->orderBy('nombre')
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'nombre' => $l->nombre,
                'ubicacion' => $l->ubicacion,
                'capacidad' => $l->capacidad,
                'estado' => $l->estado,
                'carrera' => $l->carrera?->nombre,
                'carrera_id' => $l->carrera_id,
                'responsable' => $l->responsable ? ['id' => $l->responsable->id, 'name' => $l->responsable->name] : null,
                'equipos_count' => $l->equipos_count,
                'reactivos_count' => $l->reactivos_count,
                'practicas_count' => $l->practicas_count,
            ]);

        return Inertia::render('Laboratorio/Laboratorios', [
            'laboratorios' => $laboratorios,
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'responsables' => User::role('docente')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeLaboratorio(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'carrera_id' => 'nullable|exists:carreras,id',
            'capacidad' => 'nullable|integer|min:1',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        Laboratorio::create([
            'nombre' => $request->nombre,
            'ubicacion' => $request->ubicacion,
            'carrera_id' => $request->carrera_id,
            'capacidad' => $request->capacidad,
            'responsable_id' => $request->responsable_id,
            'estado' => 'activo',
        ]);

        return redirect()->back()->with('success', 'Laboratorio registrado.');
    }

    public function updateLaboratorio(Request $request, Laboratorio $laboratorio)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'ubicacion' => 'nullable|string|max:255',
                'carrera_id' => 'nullable|exists:carreras,id',
                'capacidad' => 'nullable|integer|min:1',
                'responsable_id' => 'nullable|exists:users,id',
            ]);
            $laboratorio->update($request->only(['nombre', 'ubicacion', 'carrera_id', 'capacidad', 'responsable_id']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:activo,inactivo']);
            $laboratorio->update(['estado' => $request->estado]);
        }

        return redirect()->back()->with('success', 'Laboratorio actualizado.');
    }

    public function destroyLaboratorio(Laboratorio $laboratorio)
    {
        $laboratorio->delete();

        return redirect()->back()->with('success', 'Laboratorio eliminado.');
    }

    /**
     * Inventario de equipos.
     */
    public function equipos(Request $request): Response
    {
        $laboratorioId = $request->input('laboratorio_id', 'all');

        $query = Equipo::with('laboratorio');
        if ($laboratorioId !== 'all') {
            $query->where('laboratorio_id', $laboratorioId);
        }

        return Inertia::render('Laboratorio/Equipos', [
            'equipos' => $query->orderBy('nombre')->get()->map(fn ($e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'codigo' => $e->codigo,
                'cantidad' => $e->cantidad,
                'estado' => $e->estado,
                'laboratorio' => $e->laboratorio->nombre,
                'laboratorio_id' => $e->laboratorio_id,
            ]),
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['laboratorio_id' => $laboratorioId],
        ]);
    }

    public function storeEquipo(Request $request)
    {
        $request->validate([
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:30|unique:equipos,codigo',
            'cantidad' => 'required|integer|min:1',
        ]);

        Equipo::create([
            'laboratorio_id' => $request->laboratorio_id,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'cantidad' => $request->cantidad,
            'estado' => 'operativo',
        ]);

        return redirect()->back()->with('success', 'Equipo registrado.');
    }

    public function updateEquipo(Request $request, Equipo $equipo)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'laboratorio_id' => 'required|exists:laboratorios,id',
                'nombre' => 'required|string|max:255',
                'codigo' => 'required|string|max:30|unique:equipos,codigo,' . $equipo->id,
                'cantidad' => 'required|integer|min:1',
            ]);
            $equipo->update($request->only(['laboratorio_id', 'nombre', 'codigo', 'cantidad']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:operativo,mantenimiento,dañado']);
            $equipo->update(['estado' => $request->estado]);
        }

        return redirect()->back()->with('success', 'Equipo actualizado.');
    }

    public function destroyEquipo(Equipo $equipo)
    {
        $equipo->delete();

        return redirect()->back()->with('success', 'Equipo eliminado.');
    }

    /**
     * Inventario de reactivos.
     */
    public function reactivos(Request $request): Response
    {
        $laboratorioId = $request->input('laboratorio_id', 'all');

        $query = Reactivo::with('laboratorio');
        if ($laboratorioId !== 'all') {
            $query->where('laboratorio_id', $laboratorioId);
        }

        return Inertia::render('Laboratorio/Reactivos', [
            'reactivos' => $query->orderBy('nombre')->get()->map(fn ($r) => [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'formula' => $r->formula,
                'cantidad' => $r->cantidad,
                'unidad' => $r->unidad,
                'estado' => $r->estado,
                'laboratorio' => $r->laboratorio->nombre,
                'laboratorio_id' => $r->laboratorio_id,
            ]),
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => ['laboratorio_id' => $laboratorioId],
        ]);
    }

    public function storeReactivo(Request $request)
    {
        $request->validate([
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'nombre' => 'required|string|max:255',
            'formula' => 'nullable|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'unidad' => 'nullable|string|max:30',
        ]);

        Reactivo::create([
            'laboratorio_id' => $request->laboratorio_id,
            'nombre' => $request->nombre,
            'formula' => $request->formula,
            'cantidad' => $request->cantidad,
            'unidad' => $request->unidad,
            'estado' => 'disponible',
        ]);

        return redirect()->back()->with('success', 'Reactivo registrado.');
    }

    public function updateReactivo(Request $request, Reactivo $reactivo)
    {
        if ($request->filled('nombre')) {
            $request->validate([
                'laboratorio_id' => 'required|exists:laboratorios,id',
                'nombre' => 'required|string|max:255',
                'formula' => 'nullable|string|max:255',
                'cantidad' => 'required|integer|min:0',
                'unidad' => 'nullable|string|max:30',
            ]);
            $reactivo->update($request->only(['laboratorio_id', 'nombre', 'formula', 'cantidad', 'unidad']));
        }

        if ($request->filled('estado')) {
            $request->validate(['estado' => 'required|in:disponible,agotado,vencido']);
            $reactivo->update(['estado' => $request->estado]);
        }

        return redirect()->back()->with('success', 'Reactivo actualizado.');
    }

    public function destroyReactivo(Reactivo $reactivo)
    {
        $reactivo->delete();

        return redirect()->back()->with('success', 'Reactivo eliminado.');
    }

    /**
     * Estadísticas por carrera (prácticas, estudiantes atendidos y
     * laboratorios propios de cada carrera).
     */
    public function porCarrera(): Response
    {
        $carreras = Carrera::orderBy('nombre')->get(['id', 'nombre'])->map(function ($c) {
            $practicasQuery = PracticaLaboratorio::whereHas('materia', fn ($q) => $q->where('carrera_id', $c->id));

            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'practicas' => $practicasQuery->count(),
                'estudiantes' => (int) $practicasQuery->sum('numero_estudiantes'),
                'laboratorios' => Laboratorio::where('carrera_id', $c->id)->count(),
            ];
        });

        return Inertia::render('Laboratorio/PorCarrera', [
            'carreras' => $carreras,
        ]);
    }
}
