<?php

namespace App\Http\Controllers\Investigacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Investigacion\HitoInvestigacion;
use App\Models\Investigacion\Investigacion;
use App\Models\Investigacion\SeguimientoInvestigacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Sigue el patrón Index/Create/Edit/Show adaptado: Show.vue (antes
 * Hitos.vue) hace de página de detalle del proyecto con sus hitos y
 * seguimiento anidados, ya que esos sub-recursos no tienen sentido como
 * pantallas propias fuera del contexto del proyecto padre.
 *
 * Equipo del proyecto (restructurado): docente (quien lo crea/gestiona
 * administrativamente), líder de proyecto (antes "coordinador supervisor"),
 * colíder (opcional) y miembros (varios, tabla pivote
 * investigacion_miembros). "Gestionar" (editar título/objetivo, estado,
 * hitos) lo pueden hacer docente dueño, líder o colíder. Eliminar el
 * proyecto queda restringido solo al docente dueño (acción destructiva).
 */
class InvestigacionController extends Controller
{
    use HasBreadcrumbs;

    private function puedeVerTodo(): bool
    {
        return Auth::user()->hasRole('SystemAdministrador');
    }

    /**
     * Es parte del equipo (para visibilidad): dueño, líder, colíder o miembro.
     */
    private function esDelEquipo(Investigacion $investigacion): bool
    {
        $userId = Auth::id();

        return $investigacion->docente_id === $userId
            || $investigacion->lider_id === $userId
            || $investigacion->colider_id === $userId
            || $investigacion->miembros()->where('users.id', $userId)->exists();
    }

    /**
     * Puede gestionar (editar título/objetivo/estado/hitos): dueño, líder o colíder.
     */
    private function puedeGestionar(Investigacion $investigacion): bool
    {
        $userId = Auth::id();

        return $this->puedeVerTodo()
            || $investigacion->docente_id === $userId
            || $investigacion->lider_id === $userId
            || $investigacion->colider_id === $userId;
    }

    private function scopeVisible($query)
    {
        if ($this->puedeVerTodo()) {
            return $query;
        }

        $userId = Auth::id();

        return $query->where(function ($q) use ($userId) {
            $q->where('docente_id', $userId)
                ->orWhere('lider_id', $userId)
                ->orWhere('colider_id', $userId)
                ->orWhereHas('miembros', fn ($mq) => $mq->where('users.id', $userId));
        });
    }

    private function authorizeView(Investigacion $investigacion): void
    {
        if (!$this->puedeVerTodo() && !$this->esDelEquipo($investigacion)) {
            abort(403);
        }
    }

    /**
     * Catálogo de usuarios elegibles para líder/colíder/miembros de un
     * proyecto de investigación: docentes y coordinadores (un coordinador
     * también es docente).
     */
    private function usuariosElegibles()
    {
        return User::role(['docente', 'coordinador'])->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Mis Proyectos de Investigación (dueño / líder / colíder / miembro).
     */
    public function index(Request $request): Response
    {
        $query = Investigacion::with(['docente', 'lider', 'colider', 'miembros', 'periodo', 'hitos']);
        $query = $this->scopeVisible($query);

        $estado = $request->input('estado', 'all');
        if ($estado !== 'all') {
            $query->where('estado', $estado);
        }

        $userId = Auth::id();

        $proyectos = $query->orderByDesc('created_at')->get()->map(function ($p) use ($userId) {
            return [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'objetivo' => $p->objetivo,
                'estado' => $p->estado,
                'docente' => ['id' => $p->docente->id, 'name' => $p->docente->name],
                'lider' => ['id' => $p->lider->id, 'name' => $p->lider->name],
                'colider' => $p->colider ? ['id' => $p->colider->id, 'name' => $p->colider->name] : null,
                'miembros' => $p->miembros->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->values(),
                'periodo' => $p->periodo->nombre,
                'total_hitos' => $p->hitos->count(),
                'hitos_completados' => $p->hitos->where('porcentaje_avance', 100)->count(),
                'es_propio' => $p->docente_id === $userId,
                'es_lider' => $p->lider_id === $userId,
                'es_colider' => $p->colider_id === $userId,
                'puede_gestionar' => $this->puedeGestionar($p),
            ];
        });

        return Inertia::render('Investigacion/Index', [
            'proyectos' => $proyectos,
            'periodos' => PeriodoAcademico::where('estado', 'activo')->get(['id', 'nombre']),
            'filters' => ['estado' => $estado],
            'breadcrumbs' => $this->investigacionBreadcrumbs('Mis Proyectos'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Investigacion/Create', [
            'periodos' => PeriodoAcademico::where('estado', 'activo')->get(['id', 'nombre']),
            'usuarios' => $this->usuariosElegibles(),
            'breadcrumbs' => $this->investigacionBreadcrumbs('Mis Proyectos', 'Nuevo Proyecto', route('investigacion.index')),
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
            'lider_id' => 'required|exists:users,id',
            'colider_id' => 'nullable|exists:users,id|different:lider_id',
            'miembros' => 'nullable|array',
            'miembros.*' => 'exists:users,id',
        ]);

        $investigacion = Investigacion::create([
            'docente_id' => Auth::id(),
            'lider_id' => $request->lider_id,
            'colider_id' => $request->colider_id,
            'periodo_id' => $request->periodo_id,
            'titulo' => $request->titulo,
            'objetivo' => $request->objetivo,
            'estado' => 'en_curso',
        ]);

        $miembros = collect($request->input('miembros', []))
            ->reject(fn ($id) => $id == $request->lider_id || $id == $request->colider_id)
            ->unique()
            ->values();

        if ($miembros->isNotEmpty()) {
            $investigacion->miembros()->sync($miembros);
        }

        return redirect()->route('investigacion.index')->with('success', 'Proyecto de investigación creado.');
    }

    public function edit(Investigacion $investigacion): Response
    {
        if (!$this->puedeGestionar($investigacion)) {
            abort(403);
        }

        return Inertia::render('Investigacion/Edit', [
            'proyecto' => [
                'id' => $investigacion->id,
                'titulo' => $investigacion->titulo,
                'objetivo' => $investigacion->objetivo,
                'lider_id' => $investigacion->lider_id,
                'colider_id' => $investigacion->colider_id,
                'miembros' => $investigacion->miembros()->pluck('users.id'),
            ],
            'usuarios' => $this->usuariosElegibles(),
            'breadcrumbs' => $this->investigacionBreadcrumbs('Mis Proyectos', 'Editar Proyecto', route('investigacion.index'), $investigacion->titulo),
        ]);
    }

    /**
     * El dueño/líder/colíder editan título/objetivo/equipo; los mismos
     * pueden cambiar el estado (en_curso/pausada/finalizada).
     */
    public function update(Request $request, Investigacion $investigacion)
    {
        if (!$this->puedeGestionar($investigacion)) {
            abort(403);
        }

        if ($request->filled('titulo')) {
            $request->validate([
                'titulo' => 'required|string|max:255',
                'objetivo' => 'nullable|string|max:2000',
                'lider_id' => 'required|exists:users,id',
                'colider_id' => 'nullable|exists:users,id|different:lider_id',
                'miembros' => 'nullable|array',
                'miembros.*' => 'exists:users,id',
            ]);

            $investigacion->update($request->only(['titulo', 'objetivo', 'lider_id', 'colider_id']));

            $miembros = collect($request->input('miembros', []))
                ->reject(fn ($id) => $id == $request->lider_id || $id == $request->colider_id)
                ->unique()
                ->values();

            $investigacion->miembros()->sync($miembros);
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
     * Show del proyecto: hitos, seguimiento e informes trimestrales (antes
     * método hitos()).
     */
    public function show(Investigacion $investigacion): Response
    {
        $this->authorizeView($investigacion);

        $esLider = $investigacion->lider_id === Auth::id() || $this->puedeVerTodo();

        return Inertia::render('Investigacion/Show', [
            'proyecto' => [
                'id' => $investigacion->id,
                'titulo' => $investigacion->titulo,
                'estado' => $investigacion->estado,
                'lider' => $investigacion->lider->only(['id', 'name']),
                'colider' => $investigacion->colider?->only(['id', 'name']),
                'miembros' => $investigacion->miembros->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->values(),
            ],
            'hitos' => $investigacion->hitos()->orderBy('fecha_planificada')->get(),
            'seguimiento' => $investigacion->seguimientos()->orderBy('orden')->get(),
            'informesTrimestrales' => $investigacion->informesTrimestrales()->orderByDesc('fecha_subida')->get()->map(fn ($i) => [
                'id' => $i->id,
                'nombre_original' => $i->nombre_original,
                'observaciones' => $i->observaciones,
                'fecha_subida' => $i->fecha_subida?->diffForHumans(),
                'ver_url' => route('investigacion.informes.ver', $i->id),
            ]),
            'puedeGestionar' => $this->puedeGestionar($investigacion),
            'esDueno' => $investigacion->docente_id === Auth::id() || $this->puedeVerTodo(),
            'esLider' => $esLider,
            'esColider' => $investigacion->colider_id === Auth::id() || $this->puedeVerTodo(),
            'breadcrumbs' => $this->investigacionBreadcrumbs('Mis Proyectos', 'Ver Proyecto', route('investigacion.index'), $investigacion->titulo),
        ]);
    }

    public function storeHito(Request $request, Investigacion $investigacion)
    {
        if (!$this->puedeGestionar($investigacion)) {
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

        if (!$this->puedeGestionar($investigacion)) {
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
     * Seguimiento tipo pregunta/respuesta: el líder o colíder del proyecto
     * plantea la pregunta, el docente dueño del proyecto la responde.
     */
    public function storeSeguimiento(Request $request, Investigacion $investigacion)
    {
        $userId = Auth::id();
        $puedePreguntar = $investigacion->lider_id === $userId || $investigacion->colider_id === $userId || $this->puedeVerTodo();

        if (!$puedePreguntar) {
            abort(403, 'Solo el líder o colíder del proyecto pueden agregar preguntas de seguimiento.');
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

    /**
     * Sube un informe trimestral de avance. Solo el líder de proyecto puede
     * subirlo (por el momento, según lo confirmado). Se conserva un
     * historial completo de informes, no se reemplaza el anterior.
     */
    public function storeInformeTrimestral(Request $request, Investigacion $investigacion)
    {
        $esLider = $investigacion->lider_id === Auth::id() || $this->puedeVerTodo();

        if (!$esLider) {
            abort(403, 'Solo el líder de proyecto puede subir el informe trimestral.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $archivo = $request->file('archivo');
        $path = $archivo->store('informes-trimestrales', 'public');

        $investigacion->informesTrimestrales()->create([
            'archivo_url' => '/storage/' . $path,
            'nombre_original' => $archivo->getClientOriginalName(),
            'observaciones' => $request->observaciones,
            'fecha_subida' => now(),
        ]);

        return redirect()->back()->with('success', 'Informe trimestral subido.');
    }

    /**
     * Sirve el archivo del informe trimestral por una ruta autenticada, sin
     * depender del symlink public/storage (mismo patrón que
     * SilaboController::ver()). Cualquier miembro del equipo del proyecto
     * puede verlo/descargarlo.
     */
    public function verInformeTrimestral(\App\Models\Investigacion\InformeTrimestralInvestigacion $informe)
    {
        $investigacion = $informe->investigacion;

        if (!$this->puedeVerTodo() && !$this->esDelEquipo($investigacion)) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $ruta = Str::after($informe->archivo_url, '/storage/');

        abort_unless(Storage::disk('public')->exists($ruta), 404, 'El archivo ya no está disponible.');

        return Storage::disk('public')->response($ruta, $informe->nombre_original);
    }
}
