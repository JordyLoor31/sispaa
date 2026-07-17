<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Asignación de Docentes (tabla asignaciones_docente): vincula un
 * docente a una materia/período/grupo. Antes de este controlador no existía
 * ninguna pantalla para esto -- la tabla solo se llenaba manualmente en la
 * base de datos o vía el seeder de desarrollo (AsignacionesInformesSeeder).
 *
 * Esta asignación es la que alimenta "Mis Sílabos", "Mis Informes de
 * Asignatura", "Mis Estudiantes" y el alcance de Titulación del propio
 * docente: sin una fila aquí, esas pantallas le aparecen vacías al docente
 * aunque tenga el rol correcto.
 */
class AsignacionDocenteController extends Controller
{
    use HasBreadcrumbs;

    public const TIPOS_ROL = ['Principal', 'Auxiliar'];

    private function docentesDisponibles()
    {
        return User::role('docente')->orderBy('name')->get(['id', 'name', 'email', 'cedula']);
    }

    private function materiasDisponibles()
    {
        return Materia::with('carrera:id,nombre,codigo')
            ->where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'carrera_id', 'nombre', 'codigo', 'nivel']);
    }

    private function periodosDisponibles()
    {
        return PeriodoAcademico::orderByDesc('id')->get(['id', 'nombre', 'estado']);
    }

    public function index(Request $request): Response
    {
        $docenteId = $request->input('docente_id', 'all');
        $materiaId = $request->input('materia_id', 'all');
        $periodoId = $request->input('periodo_id', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = AsignacionDocente::with(['docente:id,name,email', 'materia:id,nombre,codigo,carrera_id', 'materia.carrera:id,nombre,codigo', 'periodo:id,nombre,estado']);

        if ($docenteId !== 'all') {
            $query->where('docente_id', $docenteId);
        }
        if ($materiaId !== 'all') {
            $query->where('materia_id', $materiaId);
        }
        if ($periodoId !== 'all') {
            $query->where('periodo_id', $periodoId);
        }

        $asignaciones = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        return Inertia::render('Secretaria/AsignacionesDocente/Index', [
            'asignaciones' => $asignaciones,
            'docentes' => $this->docentesDisponibles(),
            'materias' => $this->materiasDisponibles(),
            'periodos' => $this->periodosDisponibles(),
            'filters' => ['docente_id' => $docenteId, 'materia_id' => $materiaId, 'periodo_id' => $periodoId],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Asignación de Docentes'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secretaria/AsignacionesDocente/Create', [
            'docentes' => $this->docentesDisponibles(),
            'materias' => $this->materiasDisponibles(),
            'periodos' => $this->periodosDisponibles(),
            'tiposRol' => self::TIPOS_ROL,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Asignación de Docentes', 'Nueva Asignación', route('secretaria.asignaciones-docente.index')),
        ]);
    }

    private function reglas(): array
    {
        return [
            'docente_id' => [
                'required',
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) {
                    if (!User::find($value)?->hasRole('docente')) {
                        $fail('El usuario seleccionado no tiene el rol de docente.');
                    }
                },
            ],
            'materia_id' => ['required', Rule::exists('materias', 'id')],
            'periodo_id' => ['required', Rule::exists('periodos_academicos', 'id')],
            'tipo_rol' => ['required', Rule::in(self::TIPOS_ROL)],
            'grupo' => ['nullable', 'string', 'max:5'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->reglas());

        $duplicada = AsignacionDocente::where('docente_id', $validated['docente_id'])
            ->where('materia_id', $validated['materia_id'])
            ->where('periodo_id', $validated['periodo_id'])
            ->where('grupo', $validated['grupo'] ?? null)
            ->exists();

        if ($duplicada) {
            return redirect()->back()->withInput()->withErrors([
                'materia_id' => 'Este docente ya tiene esta materia asignada en el mismo período y grupo.',
            ]);
        }

        AsignacionDocente::create($validated);

        return redirect()->route('secretaria.asignaciones-docente.index')->with('success', 'Asignación registrada correctamente.');
    }

    public function edit(AsignacionDocente $asignacion): Response
    {
        return Inertia::render('Secretaria/AsignacionesDocente/Edit', [
            'asignacion' => $asignacion->load(['docente:id,name,email', 'materia:id,nombre,codigo,carrera_id', 'periodo:id,nombre,estado']),
            'docentes' => $this->docentesDisponibles(),
            'materias' => $this->materiasDisponibles(),
            'periodos' => $this->periodosDisponibles(),
            'tiposRol' => self::TIPOS_ROL,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Asignación de Docentes', 'Editar Asignación', route('secretaria.asignaciones-docente.index'), $asignacion->docente->name),
        ]);
    }

    public function update(Request $request, AsignacionDocente $asignacion)
    {
        $validated = $request->validate($this->reglas());

        $duplicada = AsignacionDocente::where('docente_id', $validated['docente_id'])
            ->where('materia_id', $validated['materia_id'])
            ->where('periodo_id', $validated['periodo_id'])
            ->where('grupo', $validated['grupo'] ?? null)
            ->where('id', '!=', $asignacion->id)
            ->exists();

        if ($duplicada) {
            return redirect()->back()->withInput()->withErrors([
                'materia_id' => 'Este docente ya tiene esta materia asignada en el mismo período y grupo.',
            ]);
        }

        $asignacion->update($validated);

        return redirect()->route('secretaria.asignaciones-docente.index')->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy(AsignacionDocente $asignacion)
    {
        $asignacion->delete();

        return redirect()->route('secretaria.asignaciones-docente.index')->with('success', 'Asignación eliminada correctamente.');
    }
}
