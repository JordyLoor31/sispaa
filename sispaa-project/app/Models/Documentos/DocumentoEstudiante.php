<?php

namespace App\Models\Documentos;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoEstudiante extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'documentos_estudiante';

    protected $fillable = [
        'estudiante_id',
        'secretaria_id',
        'grupo_id',
        'requisito_id',
        'convocatoria_id',
        'tipo_documento',
        'archivo_url',
        'estado',
        'observacion',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        // archivo_url se guarda como JSON: {"path":"...","name":"...","size":...}
        'archivo_url' => 'array',
    ];

    public function estudiante()
    {
        return $this->belongsTo(\App\Models\User::class, 'estudiante_id');
    }

    public function secretaria()
    {
        return $this->belongsTo(\App\Models\User::class, 'secretaria_id');
    }

    public function grupo()
    {
        return $this->belongsTo(GrupoDocumento::class, 'grupo_id');
    }

    public function requisito()
    {
        return $this->belongsTo(RequisitoGrupo::class, 'requisito_id');
    }

    public function convocatoria()
    {
        return $this->belongsTo(\App\Models\Admin\Convocatoria::class, 'convocatoria_id');
    }

    /**
     * URL para ver el archivo del documento. Apunta a la ruta autenticada
     * (documentos.archivo) que lo sirve desde el disco privado con control
     * de acceso, NO a /storage/... (que sería público). El nombre del
     * accessor se mantiene como "archivo_publico_url" (con "o") porque así lo
     * consumen ExpedienteController y StudentPortalController.
     */
    public function getArchivoPublicoUrlAttribute(): ?string
    {
        $data = is_array($this->archivo_url) ? $this->archivo_url : json_decode($this->archivo_url, true);

        return isset($data['path']) ? route('documentos.archivo', $this->id) : null;
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
