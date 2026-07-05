<?php

namespace App\Models\Estudiantes;

use Illuminate\Database\Eloquent\Model;

class Falta extends Model
{
    protected $table = 'faltas';

    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'periodo_id',
        'fecha',
        'justificada',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'justificada' => 'boolean',
    ];

    public function estudiante()
    {
        return $this->belongsTo(\App\Models\User::class, 'estudiante_id');
    }

    public function materia()
    {
        return $this->belongsTo(\App\Models\Docencia\Materia::class);
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    public function justificacion()
    {
        return $this->hasOne(JustificacionSolicitud::class, 'falta_id');
    }
}

