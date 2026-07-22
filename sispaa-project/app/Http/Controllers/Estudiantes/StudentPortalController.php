<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Models\Documentos\DocumentoEstudiante;
use App\Models\Documentos\PlantillaDocumento;
use App\Models\Documentos\RequisitoGrupo;
use App\Models\Estudiantes\Falta;
use App\Models\Estudiantes\JustificacionSolicitud;
use App\Models\Titulacion\Titulacion;
use App\Models\Admin\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StudentPortalController extends Controller
{
    /**
     * Requisitos de documento activos, tomados del catálogo dinámico
     * (Grupos de Documentos / Requisitos) que gestiona Secretaría, en vez de
     * un arreglo fijo en código. Devuelve los nombres en el orden definido
     * por cada grupo/requisito.
     */
    private function requisitosActivos()
    {
        return RequisitoGrupo::where('activo', true)
            ->whereHas('grupo', fn ($q) => $q->where('activo', true))
            ->orderBy('grupo_id')
            ->orderBy('orden')
            ->pluck('nombre');
    }

    /**
     * Dashboard Principal del Estudiante
     */
    public function dashboard(): Response
    {
        $user = Auth::user();

        // 1. Promedio y Semestre (Simulado en base al ID para consistencia)
        $promedio = 7.5 + (($user->id % 25) / 10);
        $promedio = min(10.0, max(7.0, round($promedio, 2)));
        $semestre = (($user->id % 8) + 1) . 'º Semestre';

        // 2. Faltas reales
        $totalFaltas = Falta::where('estudiante_id', $user->id)->count();
        $faltasSinJustificar = Falta::where('estudiante_id', $user->id)
            ->where('justificada', false)
            ->whereDoesntHave('justificacion')
            ->count();

        // 3. Expediente de documentos (catálogo dinámico gestionado por Secretaría)
        $tiposRequeridos = $this->requisitosActivos();

        $documentosSubidos = DocumentoEstudiante::where('estudiante_id', $user->id)
            ->whereIn('tipo_documento', $tiposRequeridos)
            ->get()
            ->keyBy('tipo_documento');

        $aprobadosCount = 0;
        foreach ($tiposRequeridos as $tipo) {
            if (isset($documentosSubidos[$tipo]) && $documentosSubidos[$tipo]->estado === 'aprobado') {
                $aprobadosCount++;
            }
        }
        $avanceSGA = $tiposRequeridos->count() > 0 ? round(($aprobadosCount / $tiposRequeridos->count()) * 100) : 0;

        // 4. Actividades recientes/KPIs
        $kpis = [
            'promedio' => $promedio,
            'semestre' => $semestre,
            'total_faltas' => $totalFaltas,
            'faltas_sin_justificar' => $faltasSinJustificar,
            'avance_sga' => $avanceSGA
        ];

        // Faltas recientes sin justificar para alertar en el dashboard
        $faltasRecientes = Falta::with('materia')
            ->where('estudiante_id', $user->id)
            ->where('justificada', false)
            ->whereDoesntHave('justificacion')
            ->orderBy('fecha', 'desc')
            ->take(3)
            ->get();

        return Inertia::render('Estudiantes/StudentDashboard', [
            'kpis' => $kpis,
            'faltasRecientes' => $faltasRecientes
        ]);
    }

    /**
     * Mis Documentos (Expediente SGA)
     */
    public function documentos(): Response
    {
        $user = Auth::user();
        $tiposRequeridos = $this->requisitosActivos();

        $documentosDb = DocumentoEstudiante::where('estudiante_id', $user->id)
            ->whereIn('tipo_documento', $tiposRequeridos)
            ->get()
            ->keyBy('tipo_documento');

        $expediente = [];
        foreach ($tiposRequeridos as $tipo) {
            if (isset($documentosDb[$tipo])) {
                $doc = $documentosDb[$tipo];
                // archivo_url es JSON: {"path":"...","name":"...","size":...}
                $publicUrl = $doc->archivo_publico_url;
                $expediente[] = [
                    'tipo' => $tipo,
                    'subido' => true,
                    'archivo_url' => $publicUrl,
                    'estado' => $doc->estado,
                    'observacion' => $doc->observacion,
                    'updated_at' => $doc->updated_at->diffForHumans(),
                ];
            } else {
                $expediente[] = [
                    'tipo' => $tipo,
                    'subido' => false,
                    'archivo_url' => null,
                    'estado' => 'no_subido',
                    'observacion' => null,
                    'updated_at' => null,
                ];
            }
        }

        return Inertia::render('Estudiantes/StudentDocumentos', [
            'expediente' => $expediente
        ]);
    }

    /**
     * Plantillas de Documentos publicadas por Secretaría: solo lectura +
     * descarga (la descarga en sí vive en la ruta compartida
     * plantillas.descargar, ver Secretaria\PlantillaDocumentoController::descargar()).
     */
    public function plantillas(): Response
    {
        $plantillas = PlantillaDocumento::orderBy('nombre_doc')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre_doc' => $p->nombre_doc,
                'descargar_url' => route('plantillas.descargar', $p->id),
            ]);

        return Inertia::render('Estudiantes/StudentPlantillas', [
            'plantillas' => $plantillas,
        ]);
    }

    /**
     * Cargar o reemplazar archivos del expediente SGA
     */
    public function uploadDocumento(Request $request)
    {
        $request->validate([
            // El tipo debe existir en el catálogo dinámico de requisitos
            // activos; antes se aceptaba texto libre, lo que permitía crear
            // filas de documento con tipos arbitrarios.
            'tipo_documento' => ['required', 'string', \Illuminate\Validation\Rule::in($this->requisitosActivos()->all())],
            'archivo' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120', // Max 5MB
        ], [
            'tipo_documento.in' => 'El tipo de documento no corresponde a un requisito válido.',
        ]);

        // Resolver a qué requisito del catálogo dinámico corresponde este tipo,
        // para dejar el documento enlazado a su grupo/requisito (además del
        // campo de texto tipo_documento, que se conserva por compatibilidad).
        $requisito = RequisitoGrupo::where('nombre', $request->tipo_documento)
            ->where('activo', true)
            ->first();

        $user = Auth::user();
        $file = $request->file('archivo');

        // Guardar archivo en storage/public/documentos/{year}/{userId}/
        // conservando la extensión real (pdf/jpg/png/jpeg, ya validada arriba);
        // antes se forzaba '.pdf' y una imagen quedaba con extensión y mime
        // incorrectos.
        // Disco 'local' (storage/app/private): NO expuesto por el servidor web.
        // El archivo se sirve solo vía route('documentos.archivo') con control
        // de acceso. Antes iba al disco 'public', accesible sin autenticación.
        $year = now()->format('Y');
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path = $file->storeAs(
            "documentos/{$year}/{$user->id}",
            \Illuminate\Support\Str::uuid() . '.' . $extension,
            'local'
        );

        // Metadatos en JSON para no guardar binarios en DB
        $archivoJson = [
            'path'          => $path,
            'name'          => $file->getClientOriginalName(),
            'size'          => $file->getSize(),
            'mime'          => $file->getMimeType(),
            'uploaded_at'   => now()->toISOString(),
        ];

        // Buscar si ya existe
        $documento = DocumentoEstudiante::where('estudiante_id', $user->id)
            ->where('tipo_documento', $request->tipo_documento)
            ->first();

        if ($documento) {
            // Eliminar archivo anterior del disco privado
            $oldData = $documento->archivo_url; // ya es array por el cast
            if (isset($oldData['path'])) {
                Storage::disk('local')->delete($oldData['path']);
            }

            $documento->update([
                'archivo_url'  => $archivoJson,
                'estado'       => 'pendiente',
                'observacion'  => null,
                'reviewed_at'  => null,
                'grupo_id'     => $requisito?->grupo_id,
                'requisito_id' => $requisito?->id,
            ]);
        } else {
            DocumentoEstudiante::create([
                'estudiante_id'  => $user->id,
                'tipo_documento' => $request->tipo_documento,
                'archivo_url'    => $archivoJson,
                'estado'         => 'pendiente',
                'observacion'    => null,
                'grupo_id'       => $requisito?->grupo_id,
                'requisito_id'   => $requisito?->id,
            ]);
        }

        // Crear notificación del sistema
        Notificacion::create([
            'user_id' => $user->id,
            'titulo' => 'Documento cargado con éxito',
            'mensaje' => "Has cargado/actualizado tu documento: {$request->tipo_documento}. Queda pendiente de aprobación.",
            'leido' => false
        ]);

        return redirect()->back()->with('success', 'Documento subido correctamente.');
    }

    /**
     * Historial de Justificaciones
     */
    public function justificaciones(): Response
    {
        $user = Auth::user();

        // Obtener solicitudes enviadas
        $solicitudes = JustificacionSolicitud::with(['falta.materia'])
            ->whereHas('falta', function($q) use ($user) {
                $q->where('estudiante_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener faltas que se pueden justificar (justificada = false y sin solicitud)
        $faltasPorJustificar = Falta::with('materia')
            ->where('estudiante_id', $user->id)
            ->where('justificada', false)
            ->whereDoesntHave('justificacion')
            ->orderBy('fecha', 'desc')
            ->get();

        return Inertia::render('Estudiantes/Justificaciones', [
            'solicitudes' => $solicitudes,
            'faltasPorJustificar' => $faltasPorJustificar
        ]);
    }

    /**
     * Crear solicitud de justificación
     */
    public function storeJustificacion(Request $request)
    {
        $request->validate([
            'falta_id' => 'required|exists:faltas,id',
            'motivo_estudiante' => 'required|string|min:10',
            'archivo' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        $user = Auth::user();
        
        // Validar que la falta pertenezca al estudiante y no esté justificada
        $falta = Falta::where('id', $request->falta_id)
            ->where('estudiante_id', $user->id)
            ->firstOrFail();

        if ($falta->justificada || JustificacionSolicitud::where('falta_id', $falta->id)->exists()) {
            return redirect()->back()->withErrors(['falta_id' => 'Esta falta ya tiene una solicitud de justificación activa o ya está justificada.']);
        }

        // Disco 'local' (privado): el adjunto suele ser un certificado médico.
        // Se guarda la ruta relativa y se sirve solo vía
        // route('justificaciones.archivo') con control de acceso.
        $archivoPath = null;
        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('justificaciones', 'local');
        }

        JustificacionSolicitud::create([
            'falta_id'           => $falta->id,
            'motivo_estudiante'  => $request->motivo_estudiante,
            'archivo_adjunto'    => $archivoPath,
            'estado'             => 'pendiente',
            'comentario_revisor' => null,
        ]);

        // Crear notificación
        Notificacion::create([
            'user_id' => $user->id,
            'titulo' => 'Solicitud de justificación enviada',
            'mensaje' => 'Se ha enviado tu solicitud de justificación correctamente. Será revisada por tu docente.',
            'leido' => false
        ]);

        return redirect()->back()->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Mi Titulación
     */
    public function titulacion(): Response
    {
        $user = Auth::user();

        $titulacion = Titulacion::with('tutor')
            ->where('estudiante_id', $user->id)
            ->first();

        $etapas = [
            ['id' => 1, 'nombre' => 'Aprobación de Tema', 'completado' => false, 'descripcion' => 'Registro y aprobación formal del tema de titulación.'],
            ['id' => 2, 'nombre' => 'Desarrollo de Propuesta (Plan de Tesis)', 'completado' => false, 'descripcion' => 'Elaboración del marco teórico y diseño metodológico.'],
            ['id' => 3, 'nombre' => 'Revisión y Corrección de Borrador', 'completado' => false, 'descripcion' => 'Revisión técnica de capítulos por parte del tutor asignado.'],
            ['id' => 4, 'nombre' => 'Pre-defensa de Grado', 'completado' => false, 'descripcion' => 'Sustentación preliminar ante tribunal de pares.'],
            ['id' => 5, 'nombre' => 'Defensa de Grado (Sustentación Pública)', 'completado' => false, 'descripcion' => 'Presentación final y graduación oficial.'],
        ];

        $progreso = 0;

        if ($titulacion) {
            if ($titulacion->estado === 'en_proceso') {
                $etapas[0]['completado'] = true;
                $etapas[1]['completado'] = true;
                $progreso = 40;
            } elseif ($titulacion->estado === 'defendido') {
                $etapas[0]['completado'] = true;
                $etapas[1]['completado'] = true;
                $etapas[2]['completado'] = true;
                $etapas[3]['completado'] = true;
                $progreso = 80;
            } elseif ($titulacion->estado === 'graduado') {
                $etapas[0]['completado'] = true;
                $etapas[1]['completado'] = true;
                $etapas[2]['completado'] = true;
                $etapas[3]['completado'] = true;
                $etapas[4]['completado'] = true;
                $progreso = 100;
            }
        }

        return Inertia::render('Estudiantes/StudentTitulacion', [
            'titulacion' => $titulacion,
            'etapas' => $etapas,
            'progreso' => $progreso
        ]);
    }

    /**
     * Mis Asistencias
     */
    public function asistencias(): Response
    {
        $user = Auth::user();

        $faltas = Falta::with(['materia.carrera', 'justificacion'])
            ->where('estudiante_id', $user->id)
            ->orderBy('fecha', 'desc')
            ->get();

        return Inertia::render('Estudiantes/StudentAsistencias', [
            'faltas' => $faltas
        ]);
    }

    /**
     * Mi Perfil
     */
    public function perfil(): Response
    {
        $user = Auth::user();
        $carrera = DB::table('carreras')->where('id', $user->carrera_id)->first();

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'cedula' => $user->cedula ?? 'No registrada',
            'telefono' => $user->telefono ?? 'No registrado',
            'carrera' => $carrera ? $carrera->nombre : 'Sin carrera asignada',
            'carrera_codigo' => $carrera ? $carrera->codigo : null
        ];

        return Inertia::render('Estudiantes/Perfil', [
            'user_data' => $userData
        ]);
    }

    /**
     * Notificaciones
     */
    public function notificaciones(): Response
    {
        $user = Auth::user();

        $notificaciones = Notificacion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Estudiantes/StudentNotificaciones', [
            'notificaciones' => $notificaciones
        ]);
    }

    /**
     * Marcar notificaciones como leídas
     */
    public function readNotificaciones(Request $request)
    {
        $user = Auth::user();

        Notificacion::where('user_id', $user->id)
            ->where('leido', false)
            ->update(['leido' => true]);

        return redirect()->back()->with('success', 'Notificaciones marcadas como leídas.');
    }

    /**
     * JSON liviano para la campanita del header (AppSidebarHeader.vue), misma
     * idea que NotificacionController::recientes() pero bajo el prefijo del
     * portal del estudiante.
     */
    public function recientesNotificaciones()
    {
        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'titulo', 'mensaje', 'leido', 'created_at']);

        return response()->json([
            'notificaciones' => $notificaciones,
            'unread_count' => $notificaciones->where('leido', false)->count(),
        ]);
    }
}
