<?php

namespace App\Models\Estudiantes;

use Illuminate\Database\Eloquent\Model;

class JustificacionSolicitud extends Model
{
    protected $table = 'justificaciones_solicitudes';

    protected $fillable = [
        'falta_id',
        'motivo_estudiante',
        'archivo_adjunto',
        'estado',
        'comentario_docente',
    ];

    public function falta()
    {
        return $this->belongsTo(Falta::class, 'falta_id');
    }
}
