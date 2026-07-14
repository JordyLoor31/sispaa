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
     * URL pública del archivo almacenado en storage/public.
     *
     * IMPORTANTE: el nombre del accessor debe coincidir exactamente con
     * "archivo_publico_url" (con "o"), que es como lo consumen
     * ExpedienteController y StudentPortalController. Antes se llamaba
     * getArchivoPublicUrlAttribute() (inglés, sin "o"), lo que generaba un
     * accessor distinto (archivo_public_url) que nunca se resolvía: el
     * enlace para ver el documento subido nunca aparecía en pantalla.
     */
    public function getArchivoPublicoUrlAttribute(): ?string
    {
        if (!$this->archivo_url) return null;
        $data = is_array($this->archivo_url) ? $this->archivo_url : json_decode($this->archivo_url, true);
        return isset($data['path']) ? '/storage/' . $data['path'] : null;
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
