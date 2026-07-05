<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\Materia;
use App\Models\Docencia\InformeDocente;
use App\Models\Estudiantes\Matricula;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminPortalController extends Controller
{
    /**
     * Dashboard / Métricas Globales
     */
    public function dashboard(): Response
    {
        // 1. Cumplimiento docente (informes subidos/aprobados de total esperados)
        $totalInformes = InformeDocente::count();
        $informesEntregados = InformeDocente::whereIn('estado', ['subido', 'aprobado'])->count();
        $cumplimientoDocente = $totalInformes > 0 ? round(($informesEntregados / $totalInformes) * 100) : 0;

        // 2. Deserción estudiantil
        $totalMatriculados = Matricula::count();
        $totalRetirados = Matricula::where('estado', 'retirado')->count();
        $desercionEstudiantil = $totalMatriculados > 0 ? round(($totalRetirados / $totalMatriculados) * 100, 2) : 0;

        // 3. Inventario (Simulado)
        $inventarioStats = [
            'total_equipos' => 320,
            'total_reactivos' => 145,
            'reactivos_bajo_stock' => 8
        ];

        // 4. Estadísticas generales
        $stats = [
            'cumplimiento_docente' => $cumplimientoDocente,
            'total_informes' => $totalInformes,
            'informes_entregados' => $informesEntregados,
            'desercion_estudiantil' => $desercionEstudiantil,
            'total_estudiantes' => User::role('estudiante')->count(),
            'total_docentes' => User::role('docente')->count(),
            'total_carreras' => Carrera::count(),
            'total_materias' => Materia::count(),
            'inventario' => $inventarioStats
        ];

        // Cumplimiento por carreras
        $carreras = Carrera::all();
        $cumplimientoPorCarrera = [];
        foreach ($carreras as $carrera) {
            $totalCarrera = InformeDocente::whereHas('materia', function($q) use ($carrera) {
                $q->where('carrera_id', $carrera.id);
            })->count();
            
            $entregadosCarrera = InformeDocente::whereIn('estado', ['subido', 'aprobado'])
                ->whereHas('materia', function($q) use ($carrera) {
                    $q->where('carrera_id', $carrera.id);
                })->count();

            $pct = $totalCarrera > 0 ? round(($entregadosCarrera / $totalCarrera) * 100) : 0;
            $cumplimientoPorCarrera[] = [
                'carrera' => $carrera->nombre,
                'codigo' => $carrera->codigo,
                'porcentaje' => $pct
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'cumplimientoPorCarrera' => $cumplimientoPorCarrera
        ]);
    }

    /**
     * Gestión de Usuarios
     */
    public function usuariosIndex(Request $request): Response
    {
        $q = $request->input('q');
        $role = $request->input('role');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min(100, $perPage) : 10;

        $query = User::with(['roles', 'carrera']);

        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('cedula', 'like', "%{$q}%");
            });
        }

        if ($role && $role !== 'all') {
            $query->role($role);
        }

        $usuarios = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $roles = Role::all();
        $carreras = Carrera::all();

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'carreras' => $carreras,
            'filters' => $request->only(['q', 'role', 'per_page'])
        ]);
    }

    public function usuariosStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'cedula' => 'nullable|string|max:10|unique:users',
            'telefono' => 'nullable|string|max:15',
            'carrera_id' => 'nullable|exists:carreras,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'carrera_id' => $request->carrera_id,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'Usuario creado correctamente.');
    }

    public function usuariosUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'cedula' => 'nullable|string|max:10|unique:users,cedula,' . $user->id,
            'telefono' => 'nullable|string|max:15',
            'carrera_id' => 'nullable|exists:carreras,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'carrera_id' => $request->carrera_id,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function usuariosToggleStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active
        ]);

        return redirect()->back()->with('success', 'Estado del usuario actualizado.');
    }

    /**
     * Malla Curricular
     */
    public function mallaIndex(Request $request): Response
    {
        $carreraId = $request->input('carrera_id');
        $q = $request->input('q');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min(100, $perPage) : 10;

        $carreras = Carrera::with('coordinador')->get();
        $coordinadores = User::role(['coordinador', 'docente'])->get();

        $query = Materia::with('carrera');
        if ($carreraId && $carreraId !== 'all') {
            $query->where('carrera_id', $carreraId);
        }

        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%");
            });
        }

        $materias = $query->orderBy('nivel')->orderBy('nombre')->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Carreras/Index', [
            'carreras' => $carreras,
            'coordinadores' => $coordinadores,
            'materias' => $materias,
            'filters' => $request->only(['carrera_id', 'q', 'per_page'])
        ]);
    }

    public function carreraStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:carreras',
            'coordinador_id' => 'nullable|exists:users,id',
        ]);

        Carrera::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'coordinador_id' => $request->coordinador_id,
            'activa' => true,
        ]);

        return redirect()->back()->with('success', 'Carrera creada correctamente.');
    }

    public function carreraUpdate(Request $request, Carrera $carrera)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:carreras,codigo,' . $carrera->id,
            'coordinador_id' => 'nullable|exists:users,id',
        ]);

        $carrera->update([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'coordinador_id' => $request->coordinador_id,
        ]);

        return redirect()->back()->with('success', 'Carrera actualizada correctamente.');
    }

    public function materiaStore(Request $request)
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:materias',
            'creditos' => 'required|integer|min:1',
            'nivel' => 'required|integer|min:1|max:10',
        ]);

        Materia::create([
            'carrera_id' => $request->carrera_id,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'creditos' => $request->creditos,
            'nivel' => $request->nivel,
            'activa' => true,
        ]);

        return redirect()->back()->with('success', 'Asignatura creada correctamente.');
    }

    public function materiaUpdate(Request $request, Materia $materia)
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:20|unique:materias,codigo,' . $materia->id,
            'creditos' => 'required|integer|min:1',
            'nivel' => 'required|integer|min:1|max:10',
        ]);

        $materia->update([
            'carrera_id' => $request->carrera_id,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'creditos' => $request->creditos,
            'nivel' => $request->nivel,
        ]);

        return redirect()->back()->with('success', 'Asignatura actualizada correctamente.');
    }

    public function materiaDestroy(Materia $materia)
    {
        $materia->delete();
        return redirect()->back()->with('success', 'Asignatura eliminada correctamente.');
    }

    /**
     * Fechas Límite y Convocatorias
     */
    public function fechasIndex(): Response
    {
        $periodos = PeriodoAcademico::with('carrera')->orderBy('id', 'desc')->get();
        $carreras = Carrera::all();

        return Inertia::render('Admin/PeriodosAcademicos', [
            'periodos' => $periodos,
            'carreras' => $carreras,
        ]);
    }

    public function periodoStore(Request $request)
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo' => 'required|in:semestral,anual',
        ]);

        // Si se crea como activo, desactivar los otros periodos de esa carrera
        PeriodoAcademico::create([
            'carrera_id' => $request->carrera_id,
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'tipo' => $request->tipo,
            'activo' => false,
        ]);

        return redirect()->back()->with('success', 'Periodo académico creado.');
    }

    public function periodoUpdate(Request $request, PeriodoAcademico $periodo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'required|boolean',
        ]);

        if ($request->activo) {
            // Desactivar otros para esta carrera
            PeriodoAcademico::where('carrera_id', $periodo->carrera_id)
                ->where('id', '!=', $periodo->id)
                ->update(['activo' => false]);
        }

        $periodo->update([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo,
        ]);

        return redirect()->back()->with('success', 'Periodo académico actualizado.');
    }

    public function periodoDeadlinesUpdate(Request $request, PeriodoAcademico $periodo)
    {
        $request->validate([
            'fecha_limite_silabo' => 'nullable|date',
            'fecha_limite_informe' => 'nullable|date',
        ]);

        $periodo->update([
            'fecha_limite_silabo' => $request->fecha_limite_silabo,
            'fecha_limite_informe' => $request->fecha_limite_informe,
        ]);

        return redirect()->back()->with('success', 'Fechas límites guardadas correctamente.');
    }

    /**
     * Informes de Asignatura (Paginado y Real)
     */
    public function informesAsignatura(Request $request): Response
    {
        $carreraId = $request->input('carrera_id');
        $estado = $request->input('estado');
        $perPage = (int) $request->input('per_page', 8);
        $perPage = $perPage > 0 ? min(100, $perPage) : 8;

        $query = InformeDocente::with(['docente', 'materia.carrera', 'periodo']);

        if ($carreraId && $carreraId !== 'all') {
            $query->whereHas('materia', function($q) use ($carreraId) {
                $q->where('carrera_id', $carreraId);
            });
        }

        if ($estado && $estado !== 'all') {
            // Mapear los estados del frontend ('Cumplido', 'Pendiente', 'Incumplido') a la DB
            // DB tiene ['pendiente', 'subido', 'aprobado']
            // Cumplido -> aprobado, Pendiente -> subido / pendiente
            if ($estado === 'Cumplido') {
                $query->where('estado', 'aprobado');
            } elseif ($estado === 'Pendiente') {
                $query->where('estado', 'subido');
            } elseif ($estado === 'Incumplido') {
                $query->where('estado', 'pendiente');
            }
        }

        $informesPaginated = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        $carreras = Carrera::all();

        return Inertia::render('Docencia/Informes', [
            'informesPaginated' => $informesPaginated,
            'carreras' => $carreras,
            'filters' => $request->only(['carrera_id', 'estado', 'per_page'])
        ]);
    }
}
