<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\Notificacion;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\Matricula;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD parcial de Matrículas, separado de SecretariaController. Sigue el
 * patrón Index/Create (sin Edit/Show/Delete): secretaría registra
 * matrículas nuevas y cambia su estado (activo/retirado/egresado), pero no
 * hay edición de campos ni eliminación. El cambio de estado se mantiene
 * como una acción inline en Index.vue (similar al toggle de Carreras), no
 * en una página Show, porque es un cambio de un solo campo sobre una fila
 * ya visible en la tabla.
 */
class MatriculaController extends Controller
{
    use HasBreadcrumbs;

    public function index(Request $request): Response
    {
        $q = $request->input('q');
        $estado = $request->input('estado', 'all');
        $periodo = $request->input('periodo_id', 'all');
        $carrera = $request->input('carrera_id', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = Matricula::with(['estudiante', 'periodo', 'carrera']);

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
        if ($periodo && $periodo !== 'all') {
            $query->where('periodo_id', $periodo);
        }
        if ($carrera && $carrera !== 'all') {
            $query->where('carrera_id', $carrera);
        }

        $matriculas = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $periodos = PeriodoAcademico::orderBy('id', 'desc')->get(['id', 'nombre', 'activo']);
        $carreras = Carrera::where('activa', true)->get(['id', 'nombre', 'codigo']);

        return Inertia::render('Secretaria/Matriculas/Index', [
            'matriculas' => $matriculas,
            'periodos' => $periodos,
            'carreras' => $carreras,
            'filters' => $request->only(['q', 'estado', 'periodo_id', 'carrera_id', 'per_page']),
            'stats' => [
                'activos' => Matricula::where('estado', 'activo')->count(),
                'retirados' => Matricula::where('estado', 'retirado')->count(),
                'egresados' => Matricula::where('estado', 'egresado')->count(),
                'total' => Matricula::count(),
            ],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Matrículas'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secretaria/Matriculas/Create', [
            'periodos' => PeriodoAcademico::orderBy('id', 'desc')->get(['id', 'nombre', 'activo']),
            'carreras' => Carrera::where('activa', true)->get(['id', 'nombre', 'codigo']),
            'estudiantes' => User::role('estudiante')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'cedula', 'carrera_id']),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Matrículas', 'Nueva Matrícula', route('secretaria.matriculas.index')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'periodo_id' => 'required|exists:periodos_academicos,id',
            'carrera_id' => 'required|exists:carreras,id',
            'fecha_matricula' => 'required|date',
        ], [
            'estudiante_id.required' => 'Selecciona un estudiante.',
            'periodo_id.required' => 'Selecciona un período académico.',
            'carrera_id.required' => 'Selecciona una carrera.',
        ]);

        $existe = Matricula::where('estudiante_id', $request->estudiante_id)
            ->where('periodo_id', $request->periodo_id)
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors([
                'estudiante_id' => 'Este estudiante ya tiene una matrícula en el período seleccionado.',
            ]);
        }

        Matricula::create([
            'estudiante_id' => $request->estudiante_id,
            'periodo_id' => $request->periodo_id,
            'carrera_id' => $request->carrera_id,
            'estado' => 'activo',
            'fecha_matricula' => $request->fecha_matricula,
        ]);

        $periodo = PeriodoAcademico::find($request->periodo_id);
        $carrera = Carrera::find($request->carrera_id);

        Notificacion::create([
            'user_id' => $request->estudiante_id,
            'titulo' => 'Matrícula registrada exitosamente',
            'mensaje' => "Has sido matriculado en el período \"{$periodo->nombre}\" de la carrera \"{$carrera->nombre}\". Bienvenido/a.",
            'leido' => false,
        ]);

        return redirect()->route('secretaria.matriculas.index')->with('success', 'Matrícula registrada correctamente.');
    }

    public function updateEstado(Request $request, Matricula $matricula)
    {
        $request->validate([
            'estado' => 'required|in:activo,retirado,egresado',
        ]);

        $matricula->update(['estado' => $request->estado]);

        $mensajes = [
            'retirado' => 'Tu matrícula ha sido marcada como RETIRADA en el período actual. Para más información, contacta a Secretaría.',
            'egresado' => '¡Felicitaciones! Tu matrícula ha sido actualizada a estado EGRESADO. ¡Has completado tu ciclo académico!',
            'activo' => 'Tu matrícula ha sido reactivada y está en estado ACTIVO.',
        ];

        Notificacion::create([
            'user_id' => $matricula->estudiante_id,
            'titulo' => 'Actualización de estado de matrícula',
            'mensaje' => $mensajes[$request->estado] ?? 'El estado de tu matrícula ha sido actualizado.',
            'leido' => false,
        ]);

        return redirect()->back()->with('success', "Estado de matrícula actualizado a \"{$request->estado}\".");
    }
}
