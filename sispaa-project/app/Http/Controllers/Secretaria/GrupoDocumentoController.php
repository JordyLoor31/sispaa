<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Notificacion;
use App\Models\Documentos\DocumentoEstudiante;
use App\Models\Documentos\GrupoDocumento;
use App\Models\Documentos\RequisitoGrupo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Grupos de Documentos, separado de SecretariaController. Sigue el
 * patrón Index/Create/Show (sin Edit ni columns.ts): el grupo en sí no tiene
 * campos editables una vez creado (solo se activa/desactiva y se le agregan
 * requisitos), así que no hay un "Editar grupo" real que ofrecer. El
 * formulario de creación (con el array dinámico de requisitos) vive
 * directamente en Create.vue en vez de un GrupoDocumentoForm.vue compartido,
 * porque no hay una segunda pantalla que lo reutilice.
 */
class GrupoDocumentoController extends Controller
{
    use HasBreadcrumbs;

    /**
     * Extensiones aceptadas por el sistema al subir un documento del
     * expediente. Cada requisito puede restringir este conjunto (ej. solo
     * pdf); null significa "todas estas".
     */
    public const FORMATOS_PERMITIDOS = ['pdf', 'jpg', 'png', 'jpeg'];

    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));

        $query = GrupoDocumento::with(['requisitos', 'creadoPor'])->orderBy('created_at', 'desc');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'ilike', "%{$q}%")->orWhere('descripcion', 'ilike', "%{$q}%");
            });
        }

        return Inertia::render('Secretaria/GruposDocumentos/Index', [
            'grupos' => $query->get(),
            'filters' => ['q' => $q ?: null],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Grupos de Documentos'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secretaria/GruposDocumentos/Create', [
            'breadcrumbs' => $this->secretariaBreadcrumbs('Grupos de Documentos', 'Nuevo Grupo', route('secretaria.grupos-documentos.index')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'requisitos' => 'required|array|min:1',
            'requisitos.*' => 'required|string|max:255',
            'requisitos_formatos' => 'nullable|array',
            'requisitos_formatos.*' => 'array',
            'requisitos_formatos.*.*' => 'in:' . implode(',', self::FORMATOS_PERMITIDOS),
        ]);

        $grupo = GrupoDocumento::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'creado_por' => Auth::id(),
            'activo' => true,
        ]);

        foreach ($request->requisitos as $orden => $nombreRequisito) {
            $formatos = $request->input("requisitos_formatos.{$orden}");

            RequisitoGrupo::create([
                'grupo_id' => $grupo->id,
                'nombre' => $nombreRequisito,
                'formatos_permitidos' => !empty($formatos) ? array_values($formatos) : null,
                'orden' => $orden,
                'activo' => true,
            ]);
        }

        // Notificar a todos los estudiantes activos que hay un nuevo grupo de requisitos
        $estudiantes = User::role('estudiante')->where('is_active', true)->get(['id']);
        $ahora = now();
        $notificaciones = $estudiantes->map(fn ($estudiante) => [
            'user_id' => $estudiante->id,
            'titulo' => 'Nuevo grupo de documentos requeridos',
            'mensaje' => "Se habilitó el grupo \"{$grupo->nombre}\" con nuevos documentos que debes cargar. Revisa la sección Mis Documentos.",
            'leido' => false,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ])->all();

        if (!empty($notificaciones)) {
            Notificacion::insert($notificaciones);
        }

        return redirect()->route('secretaria.grupos-documentos.index')->with('success', 'Grupo creado y estudiantes notificados.');
    }

    public function show(GrupoDocumento $grupo): Response
    {
        $grupo->load(['requisitos', 'creadoPor', 'creator', 'updater']);

        return Inertia::render('Secretaria/GruposDocumentos/Show', [
            'grupo' => $grupo,
            'breadcrumbs' => $this->secretariaBreadcrumbs('Grupos de Documentos', 'Ver Grupo', route('secretaria.grupos-documentos.index'), $grupo->nombre),
        ]);
    }

    public function toggle(GrupoDocumento $grupo)
    {
        $grupo->update(['activo' => !$grupo->activo]);

        return redirect()->back()->with('success', $grupo->activo
            ? 'Grupo activado correctamente.'
            : 'Grupo desactivado correctamente.');
    }

    /**
     * Edita los datos del grupo (nombre y descripción).
     */
    public function update(Request $request, GrupoDocumento $grupo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $grupo->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->back()->with('success', 'Grupo actualizado correctamente.');
    }

    public function requisitoStore(Request $request, GrupoDocumento $grupo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'formatos' => 'nullable|array',
            'formatos.*' => 'in:' . implode(',', self::FORMATOS_PERMITIDOS),
        ]);

        $ordenMax = $grupo->requisitos()->max('orden') ?? -1;

        RequisitoGrupo::create([
            'grupo_id' => $grupo->id,
            'nombre' => $request->nombre,
            'formatos_permitidos' => !empty($request->formatos) ? array_values($request->formatos) : null,
            'orden' => $ordenMax + 1,
            'activo' => true,
        ]);

        return redirect()->back()->with('success', 'Requisito agregado al grupo.');
    }

    /**
     * Edita un requisito (nombre y/o formatos permitidos). Si se renombra el
     * requisito, los documentos ya cargados bajo él se actualizan para que el
     * tipo_documento denormalizado no quede desincronizado.
     */
    public function requisitoUpdate(Request $request, RequisitoGrupo $requisito)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'formatos' => 'nullable|array',
            'formatos.*' => 'in:' . implode(',', self::FORMATOS_PERMITIDOS),
        ]);

        $nombreAnterior = $requisito->nombre;

        $requisito->update([
            'nombre' => $request->nombre,
            'formatos_permitidos' => !empty($request->formatos) ? array_values($request->formatos) : null,
        ]);

        if ($nombreAnterior !== $request->nombre) {
            DocumentoEstudiante::where('requisito_id', $requisito->id)
                ->where('tipo_documento', $nombreAnterior)
                ->update(['tipo_documento' => $request->nombre]);
        }

        return redirect()->back()->with('success', 'Requisito actualizado correctamente.');
    }
}
