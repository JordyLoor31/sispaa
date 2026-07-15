<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
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

/**
 * CRUD de prácticas de laboratorio (recurso institucional compartido:
 * cualquier docente ve todas las prácticas, pero solo edita/elimina las
 * suyas) y su sub-recurso de asistencia por práctica.
 */
class PracticaLaboratorioController extends Controller
{
    use HasBreadcrumbs;

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

    private function catalogos(): array
    {
        return [
            'materias' => Materia::where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
            'laboratorios' => Laboratorio::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'periodos' => PeriodoAcademico::where('estado', 'activo')->get(['id', 'nombre']),
            'equiposCatalogo' => Equipo::orderBy('nombre')->get(['id', 'nombre']),
            'reactivosCatalogo' => Reactivo::orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    public function index(Request $request): Response
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
            'periodo_id' => $p->periodo_id,
            'materia' => ['id' => $p->materia->id, 'nombre' => $p->materia->nombre],
            'carrera' => $p->materia->carrera->nombre,
            'docente' => ['id' => $p->docente->id, 'name' => $p->docente->name],
            'laboratorio' => $p->laboratorio ? ['id' => $p->laboratorio->id, 'nombre' => $p->laboratorio->nombre] : null,
            'periodo' => $p->periodo->nombre,
            'equipos' => $p->equipos->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre, 'cantidad_usada' => $e->pivot->cantidad_usada]),
            'reactivos' => $p->reactivos->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->nombre, 'cantidad_usada' => $r->pivot->cantidad_usada]),
            'es_propio' => $p->docente_id === Auth::id() || $this->puedeVerTodo(),
        ]);

        return Inertia::render('Laboratorio/Practicas/Index', array_merge([
            'practicas' => $practicas,
            'filters' => ['periodo_id' => $periodoId],
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Prácticas'),
        ], $this->catalogos()));
    }

    public function create(): Response
    {
        return Inertia::render('Laboratorio/Practicas/Create', array_merge(
            $this->catalogos(),
            ['breadcrumbs' => $this->laboratorioBreadcrumbs('Prácticas', 'Nueva Práctica', route('laboratorio.practicas'))]
        ));
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

    public function store(Request $request)
    {
        $validated = $request->validate($this->reglasPractica());

        $practica = PracticaLaboratorio::create(array_merge(
            collect($validated)->except(['equipos', 'reactivos'])->all(),
            ['docente_id' => Auth::id()]
        ));

        $this->syncEquiposReactivos($practica, $validated);

        return redirect()->route('laboratorio.practicas')->with('success', 'Práctica de laboratorio registrada.');
    }

    public function edit(PracticaLaboratorio $practica): Response
    {
        $this->authorizePractica($practica);

        $practica->load(['equipos', 'reactivos']);

        return Inertia::render('Laboratorio/Practicas/Edit', array_merge($this->catalogos(), [
            'practica' => [
                'id' => $practica->id,
                'materia_id' => $practica->materia_id,
                'periodo_id' => $practica->periodo_id,
                'laboratorio_id' => $practica->laboratorio_id,
                'numero_practica' => $practica->numero_practica,
                'fecha' => $practica->fecha?->format('Y-m-d'),
                'tema' => $practica->tema,
                'subtema' => $practica->subtema,
                'logro_aprendizaje' => $practica->logro_aprendizaje,
                'semestre' => $practica->semestre,
                'numero_estudiantes' => $practica->numero_estudiantes,
                'horario' => $practica->horario,
                'objetivo' => $practica->objetivo,
                'metodologia' => $practica->metodologia,
                'resultados' => $practica->resultados,
                'conclusiones' => $practica->conclusiones,
                'equipos' => $practica->equipos->map(fn ($e) => ['id' => $e->id, 'cantidad_usada' => $e->pivot->cantidad_usada]),
                'reactivos' => $practica->reactivos->map(fn ($r) => ['id' => $r->id, 'cantidad_usada' => $r->pivot->cantidad_usada]),
            ],
            'breadcrumbs' => $this->laboratorioBreadcrumbs('Prácticas', 'Editar Práctica', route('laboratorio.practicas'), $practica->tema),
        ]));
    }

    public function update(Request $request, PracticaLaboratorio $practica)
    {
        $this->authorizePractica($practica);

        $validated = $request->validate($this->reglasPractica());

        $practica->update(collect($validated)->except(['equipos', 'reactivos'])->all());
        $this->syncEquiposReactivos($practica, $validated);

        return redirect()->route('laboratorio.practicas')->with('success', 'Práctica actualizada.');
    }

    public function destroy(PracticaLaboratorio $practica)
    {
        $this->authorizePractica($practica);

        $practica->delete();

        return redirect()->route('laboratorio.practicas')->with('success', 'Práctica eliminada.');
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
}
