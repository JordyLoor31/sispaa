<?php

namespace App\Models\Documentos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoEstudiante extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_estudiante';

    protected $fillable = [
        'estudiante_id',
        'secretaria_id',
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

    /**
     * URL pública del archivo almacenado en storage/public
     */
    public function getArchivoPublicUrlAttribute(): ?string
    {
        if (!$this->archivo_url) return null;
        $data = is_array($this->archivo_url) ? $this->archivo_url : json_decode($this->archivo_url, true);
        return isset($data['path']) ? '/storage/' . $data['path'] : null;
    }
}
