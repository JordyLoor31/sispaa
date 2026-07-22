<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use App\Models\Documentos\DocumentoEstudiante;
use App\Models\Estudiantes\JustificacionSolicitud;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Sirve archivos SENSIBLES por estudiante (expediente y adjuntos de
 * justificaciones) a través de Laravel, con control de acceso.
 *
 * Estos archivos contienen datos personales (cédulas, certificados médicos):
 * antes se guardaban en el disco 'public' y se enlazaban como /storage/...,
 * accesibles por cualquiera con la URL. Ahora viven en el disco privado
 * ('local' => storage/app/private) y solo se sirven desde aquí, verificando
 * que quien pide el archivo sea su dueño (el estudiante) o parte del staff
 * que lo revisa (Secretaría / SystemAdministrador). Mismo enfoque que
 * Docencia\SilaboController::ver().
 */
class DocumentoEstudianController extends Controller
{
    /** ¿El usuario autenticado puede ver un archivo de este estudiante? */
    private function puedeVer(int $estudianteId): bool
    {
        $user = Auth::user();

        return $user->id === $estudianteId
            || $user->hasAnyRole(['secretaria', 'SystemAdministrador']);
    }

    /** Documento de expediente SGA. */
    public function documento(DocumentoEstudiante $documento)
    {
        abort_unless($this->puedeVer($documento->estudiante_id), 403, 'No tienes permiso para ver este documento.');

        $data = $documento->archivo_url; // array {path,name,...} por el cast
        $path = is_array($data) ? ($data['path'] ?? null) : null;

        abort_unless($path && Storage::disk('local')->exists($path), 404, 'El archivo ya no está disponible.');

        return Storage::disk('local')->response($path);
    }

    /** Adjunto de una solicitud de justificación. */
    public function justificacion(JustificacionSolicitud $justificacion)
    {
        $estudianteId = $justificacion->falta->estudiante_id;

        abort_unless($this->puedeVer($estudianteId), 403, 'No tienes permiso para ver este documento.');

        // archivo_adjunto guarda la ruta relativa en el disco privado.
        $path = $justificacion->archivo_adjunto;

        abort_unless($path && Storage::disk('local')->exists($path), 404, 'El archivo ya no está disponible.');

        return Storage::disk('local')->response($path);
    }
}
