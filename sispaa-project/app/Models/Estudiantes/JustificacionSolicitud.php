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
        'comentario_revisor',
    ];

    public function falta()
    {
        return $this->belongsTo(Falta::class, 'falta_id');
    }

    /**
     * Acceso directo al estudiante a través de la falta
     */
    public function estudiante()
    {
        return $this->hasOneThrough(
            \App\Models\User::class,
            Falta::class,
            'id',          // FK en faltas -> justificacion
            'id',          // PK en users
            'falta_id',    // FK local en justificaciones
            'estudiante_id' // FK en faltas -> users
        );
    }
}
