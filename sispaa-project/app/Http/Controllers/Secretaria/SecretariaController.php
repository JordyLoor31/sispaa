<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Admin\Convocatoria;
use App\Models\Admin\Notificacion;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Admin\Carrera;
use App\Models\Documentos\DocumentoEstudiante;
use App\Models\Documentos\GrupoDocumento;
use App\Models\Documentos\RequisitoGrupo;
use App\Models\Estudiantes\JustificacionSolicitud;
use App\Models\Estudiantes\Matricula;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class SecretariaController extends Controller
{
    // ─────────────────────────────────────────────
    // 1. EXPEDIENTE SGA
    // ─────────────────────────────────────────────

    public function expedienteIndex(Request $request): Response
    {
        $q       = $request->input('q');
        $estado  = $request->input('estado', 'all');
        $tipo    = $request->input('tipo', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = DocumentoEstudiante::with(['estudiante.carrera', 'secretaria'])
            ->whereNull('deleted_at');

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

        if ($tipo && $tipo !== 'all') {
            $query->where('tipo_documento', $tipo);
        }

        $documentos = $query->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 WHEN 'rechazado' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($doc) {
                // Exponer URL pública sin exponer ruta interna completa
                $archivoData = $doc->archivo_url;
                return [
                    'id'             => $doc->id,
                    'tipo_documento' => $doc->tipo_documento,
                    'estado'         => $doc->estado,
                    'observacion'    => $doc->observacion,
                    'reviewed_at'    => $doc->reviewed_at?->diffForHumans(),
                    'created_at'     => $doc->created_at->format('d/m/Y H:i'),
                    'archivo_url'    => $doc->archivo_publico_url,
                    'archivo_meta'   => $archivoData ? [
                        'name' => $archivoData['name'] ?? 'documento.pdf',
                        'size' => isset($archivoData['size']) ? round($archivoData['size'] / 1024, 1) . ' KB' : null,
                    ] : null,
                    'estudiante' => [
                        'id'     => $doc->estudiante->id,
                        'name'   => $doc->estudiante->name,
                        'email'  => $doc->estudiante->email,
                        'cedula' => $doc->estudiante->cedula,
                        'carrera' => $doc->estudiante->carrera?->nombre,
                    ],
                    'revisado_por' => $doc->secretaria?->name,
                ];
            });

        $tiposDocumento = DocumentoEstudiante::distinct()
            ->pluck('tipo_documento');

        return Inertia::render('Secretaria/Expedientes', [
            'documentos'     => $documentos,
            'tiposDocumento' => $tiposDocumento,
            'filters'        => $request->only(['q', 'estado', 'tipo', 'per_page']),
            'stats'          => [
                'pendientes'  => DocumentoEstudiante::where('estado', 'pendiente')->count(),
                'aprobados'   => DocumentoEstudiante::where('estado', 'aprobado')->count(),
                'rechazados'  => DocumentoEstudiante::where('estado', 'rechazado')->count(),
                'total'       => DocumentoEstudiante::count(),
            ],
        ]);
    }

    public function expedienteReview(Request $request, DocumentoEstudiante $documento)
    {
        $request->validate([
            'accion'     => 'required|in:aprobar,rechazar',
            'observacion' => 'nullable|string|max:500',
        ]);

        if ($request->accion === 'rechazar') {
            $request->validate([
                'observacion' => 'required|string|min:5|max:500',
            ], [
                'observacion.required' => 'Debes indicar el motivo del rechazo.',
            ]);
        }

        $nuevoEstado = $request->accion === 'aprobar' ? 'aprobado' : 'rechazado';

        $documento->update([
            'estado'       => $nuevoEstado,
            'observacion'  => $request->observacion,
            'secretaria_id' => Auth::id(),
            'reviewed_at'  => now(),
        ]);

        // Notificar al estudiante
        $textoAccion = $nuevoEstado === 'aprobado' ? 'aprobado' : 'rechazado';
        $mensaje = $nuevoEstado === 'aprobado'
            ? "Tu documento \"{$documento->tipo_documento}\" ha sido aprobado por Secretaría. ¡Todo en orden!"
            : "Tu documento \"{$documento->tipo_documento}\" ha sido rechazado. Motivo: {$request->observacion}. Por favor vuelve a subir el documento corregido.";

        Notificacion::create([
            'user_id' => $documento->estudiante_id,
            'titulo'  => "Documento {$textoAccion}: {$documento->tipo_documento}",
            'mensaje' => $mensaje,
            'leido'   => false,
        ]);

        return redirect()->back()->with('success',
            $nuevoEstado === 'aprobado'
                ? 'Documento aprobado correctamente.'
                : 'Documento rechazado. Se ha notificado al estudiante.'
        );
    }

    // ─────────────────────────────────────────────
    // 2. JUSTIFICACIONES
    // ─────────────────────────────────────────────

    public function justificacionesIndex(Request $request): Response
    {
        $q      = $request->input('q');
        $estado = $request->input('estado', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = JustificacionSolicitud::with([
            'falta.estudiante.carrera',
            'falta.materia',
        ]);

        if ($q) {
            $query->whereHas('falta.estudiante', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('cedula', 'like', "%{$q}%");
            });
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        }

        $justificaciones = $query
            ->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($j) {
                return [
                    'id'                 => $j->id,
                    'estado'             => $j->estado,
                    'motivo_estudiante'  => $j->motivo_estudiante,
                    'comentario_revisor' => $j->comentario_revisor,
                    'archivo_adjunto'    => $j->archivo_adjunto,
                    'created_at'         => $j->created_at->format('d/m/Y H:i'),
                    'falta' => [
                        'id'     => $j->falta->id,
                        'fecha'  => $j->falta->fecha->format('d/m/Y'),
                        'materia' => $j->falta->materia?->nombre,
                    ],
                    'estudiante' => [
                        'id'     => $j->falta->estudiante->id,
                        'name'   => $j->falta->estudiante->name,
                        'email'  => $j->falta->estudiante->email,
                        'cedula' => $j->falta->estudiante->cedula,
                        'carrera' => $j->falta->estudiante->carrera?->nombre,
                    ],
                ];
            });

        return Inertia::render('Secretaria/Justificaciones', [
            'justificaciones' => $justificaciones,
            'filters'         => $request->only(['q', 'estado', 'per_page']),
            'stats'           => [
                'pendientes' => JustificacionSolicitud::where('estado', 'pendiente')->count(),
                'aprobadas'  => JustificacionSolicitud::where('estado', 'aprobada')->count(),
                'rechazadas' => JustificacionSolicitud::where('estado', 'rechazada')->count(),
            ],
        ]);
    }

    public function justificacionReview(Request $request, JustificacionSolicitud $justificacion)
    {
        $request->validate([
            'accion'             => 'required|in:aprobar,rechazar',
            'comentario_revisor' => 'nullable|string|max:500',
        ]);

        if ($request->accion === 'rechazar') {
            $request->validate([
                'comentario_revisor' => 'required|string|min:5',
            ], [
                'comentario_revisor.required' => 'Debes indicar el motivo del rechazo.',
            ]);
        }

        $nuevoEstado = $request->accion === 'aprobar' ? 'aprobada' : 'rechazada';

        $justificacion->update([
            'estado'             => $nuevoEstado,
            'comentario_revisor' => $request->comentario_revisor,
        ]);

        // Si se aprueba la justificación, marcar la falta como justificada
        if ($nuevoEstado === 'aprobada') {
            $justificacion->falta->update(['justificada' => true]);
        }

        // Notificar al estudiante
        $estudianteId = $justificacion->falta->estudiante_id;
        $materia      = $justificacion->falta->materia?->nombre ?? 'asignatura';
        $fecha        = $justificacion->falta->fecha->format('d/m/Y');

        $titulo  = $nuevoEstado === 'aprobada'
            ? "Justificación aprobada — {$materia}"
            : "Justificación rechazada — {$materia}";

        $mensaje = $nuevoEstado === 'aprobada'
            ? "Tu solicitud de justificación para la falta del {$fecha} en {$materia} ha sido APROBADA por Secretaría."
            : "Tu solicitud de justificación para la falta del {$fecha} en {$materia} ha sido RECHAZADA. Motivo: {$request->comentario_revisor}";

        Notificacion::create([
            'user_id' => $estudianteId,
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'leido'   => false,
        ]);

        return redirect()->back()->with('success',
            $nuevoEstado === 'aprobada'
                ? 'Justificación aprobada. La falta ha sido marcada como justificada.'
                : 'Justificación rechazada. Se ha notificado al estudiante.'
        );
    }

    // ─────────────────────────────────────────────
    // 3. MATRÍCULAS
    // ─────────────────────────────────────────────

    public function matriculasIndex(Request $request): Response
    {
        $q        = $request->input('q');
        $estado   = $request->input('estado', 'all');
        $periodo  = $request->input('periodo_id', 'all');
        $carrera  = $request->input('carrera_id', 'all');
        $perPage  = max(1, min(100, (int) $request->input('per_page', 15)));

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

        // Catálogos para el formulario de nueva matrícula
        $periodos  = PeriodoAcademico::orderBy('id', 'desc')->get(['id', 'nombre', 'activo']);
        $carreras  = Carrera::where('activa', true)->get(['id', 'nombre', 'codigo']);
        $estudiantes = User::role('estudiante')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'cedula', 'carrera_id']);

        return Inertia::render('Secretaria/Matriculas', [
            'matriculas'   => $matriculas,
            'periodos'     => $periodos,
            'carreras'     => $carreras,
            'estudiantes'  => $estudiantes,
            'filters'      => $request->only(['q', 'estado', 'periodo_id', 'carrera_id', 'per_page']),
            'stats'        => [
                'activos'   => Matricula::where('estado', 'activo')->count(),
                'retirados' => Matricula::where('estado', 'retirado')->count(),
                'egresados' => Matricula::where('estado', 'egresado')->count(),
                'total'     => Matricula::count(),
            ],
        ]);
    }

    public function matriculaStore(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'periodo_id'    => 'required|exists:periodos_academicos,id',
            'carrera_id'    => 'required|exists:carreras,id',
            'fecha_matricula' => 'required|date',
        ], [
            'estudiante_id.required' => 'Selecciona un estudiante.',
            'periodo_id.required'    => 'Selecciona un período académico.',
            'carrera_id.required'    => 'Selecciona una carrera.',
        ]);

        // Verificar matrícula duplicada en el mismo período
        $existe = Matricula::where('estudiante_id', $request->estudiante_id)
            ->where('periodo_id', $request->periodo_id)
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors([
                'estudiante_id' => 'Este estudiante ya tiene una matrícula en el período seleccionado.',
            ]);
        }

        $matricula = Matricula::create([
            'estudiante_id'   => $request->estudiante_id,
            'periodo_id'      => $request->periodo_id,
            'carrera_id'      => $request->carrera_id,
            'estado'          => 'activo',
            'fecha_matricula' => $request->fecha_matricula,
        ]);

        // Notificar al estudiante
        $periodo = PeriodoAcademico::find($request->periodo_id);
        $carrera = Carrera::find($request->carrera_id);

        Notificacion::create([
            'user_id' => $request->estudiante_id,
            'titulo'  => 'Matrícula registrada exitosamente',
            'mensaje' => "Has sido matriculado en el período \"{$periodo->nombre}\" de la carrera \"{$carrera->nombre}\". Bienvenido/a.",
            'leido'   => false,
        ]);

        return redirect()->back()->with('success', 'Matrícula registrada correctamente.');
    }

    public function matriculaUpdateEstado(Request $request, Matricula $matricula)
    {
        $request->validate([
            'estado' => 'required|in:activo,retirado,egresado',
        ]);

        $estadoAnterior = $matricula->estado;
        $matricula->update(['estado' => $request->estado]);

        // Notificar cambio de estado al estudiante
        $mensajes = [
            'retirado' => 'Tu matrícula ha sido marcada como RETIRADA en el período actual. Para más información, contacta a Secretaría.',
            'egresado' => '¡Felicitaciones! Tu matrícula ha sido actualizada a estado EGRESADO. ¡Has completado tu ciclo académico!',
            'activo'   => 'Tu matrícula ha sido reactivada y está en estado ACTIVO.',
        ];

        Notificacion::create([
            'user_id' => $matricula->estudiante_id,
            'titulo'  => 'Actualización de estado de matrícula',
            'mensaje' => $mensajes[$request->estado] ?? 'El estado de tu matrícula ha sido actualizado.',
            'leido'   => false,
        ]);

        return redirect()->back()->with('success', "Estado de matrícula actualizado a \"{$request->estado}\".");
    }

    // ─────────────────────────────────────────────
    // 4. CONVOCATORIAS (fechas límite por módulo)
    // ─────────────────────────────────────────────

    public function convocatoriasIndex(Request $request): Response
    {
        $modulo = $request->input('modulo', 'all');

        $query = Convocatoria::with('creadoPor')->orderBy('fecha_fin', 'desc');

        if ($modulo && $modulo !== 'all') {
            $query->where('modulo', $modulo);
        }

        return Inertia::render('Secretaria/Convocatorias', [
            'convocatorias' => $query->get(),
            'filters' => $request->only(['modulo']),
            'modulos' => ['Académico', 'Titulación', 'Vinculación', 'Investigación', 'Laboratorio'],
        ]);
    }

    public function convocatoriaStore(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'modulo' => 'required|string|max:100',
            'tipo_documento' => 'nullable|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        Convocatoria::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'modulo' => $request->modulo,
            'tipo_documento' => $request->tipo_documento,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'creado_por' => Auth::id(),
            'activa' => true,
        ]);

        return redirect()->back()->with('success', 'Convocatoria creada correctamente.');
    }

    public function convocatoriaUpdate(Request $request, Convocatoria $convocatoria)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'modulo' => 'required|string|max:100',
            'tipo_documento' => 'nullable|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'activa' => 'required|boolean',
        ]);

        $convocatoria->update($request->only([
            'titulo', 'descripcion', 'modulo', 'tipo_documento', 'fecha_inicio', 'fecha_fin', 'activa',
        ]));

        return redirect()->back()->with('success', 'Convocatoria actualizada correctamente.');
    }

    public function convocatoriaDestroy(Convocatoria $convocatoria)
    {
        $convocatoria->delete();

        return redirect()->back()->with('success', 'Convocatoria eliminada correctamente.');
    }

    // ─────────────────────────────────────────────
    // 5. GRUPOS DE DOCUMENTOS (catálogo dinámico de requisitos)
    // ─────────────────────────────────────────────

    public function gruposDocumentosIndex(): Response
    {
        $grupos = GrupoDocumento::with(['requisitos', 'creadoPor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Secretaria/GruposDocumentos', [
            'grupos' => $grupos,
        ]);
    }

    public function grupoDocumentoStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'requisitos' => 'required|array|min:1',
            'requisitos.*' => 'required|string|max:255',
        ]);

        $grupo = GrupoDocumento::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'creado_por' => Auth::id(),
            'activo' => true,
        ]);

        foreach ($request->requisitos as $orden => $nombreRequisito) {
            RequisitoGrupo::create([
                'grupo_id' => $grupo->id,
                'nombre' => $nombreRequisito,
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

        return redirect()->back()->with('success', 'Grupo de documentos creado y estudiantes notificados.');
    }

    public function requisitoStore(Request $request, GrupoDocumento $grupo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $ordenMax = $grupo->requisitos()->max('orden') ?? -1;

        RequisitoGrupo::create([
            'grupo_id' => $grupo->id,
            'nombre' => $request->nombre,
            'orden' => $ordenMax + 1,
            'activo' => true,
        ]);

        return redirect()->back()->with('success', 'Requisito agregado al grupo.');
    }

    public function grupoDocumentoToggle(GrupoDocumento $grupo)
    {
        $grupo->update(['activo' => !$grupo->activo]);

        return redirect()->back()->with('success', $grupo->activo
            ? 'Grupo activado correctamente.'
            : 'Grupo desactivado correctamente.');
    }

    // ─────────────────────────────────────────────
    // 6. NOTIFICACIONES MASIVAS
    // ─────────────────────────────────────────────

    public function notificacionesMasivasIndex(): Response
    {
        // Historial agrupado por título+mensaje+minuto de envío (igual al criterio
        // usado en el sistema Next.js origen para reconstruir "envíos masivos"
        // a partir de filas individuales de la tabla de notificaciones).
        $historial = Notificacion::selectRaw("titulo, mensaje, date_trunc('minute', created_at) as enviado_en, count(*) as total_destinatarios")
            ->groupBy('titulo', 'mensaje', 'enviado_en')
            ->orderByDesc('enviado_en')
            ->limit(50)
            ->get();

        return Inertia::render('Secretaria/NotificacionesMasivas', [
            'historial' => $historial,
            'roles' => Role::pluck('name'),
        ]);
    }

    public function notificacionesMasivasStore(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string|max:1000',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|string|exists:roles,name',
        ]);

        $destinatarios = User::role($request->roles)
            ->where('id', '!=', Auth::id())
            ->get(['id']);

        $ahora = now();
        $notificaciones = $destinatarios->map(fn ($user) => [
            'user_id' => $user->id,
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'leido' => false,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ])->all();

        if (!empty($notificaciones)) {
            Notificacion::insert($notificaciones);
        }

        return redirect()->back()->with('success', 'Notificación enviada a ' . count($notificaciones) . ' usuario(s).');
    }
}
