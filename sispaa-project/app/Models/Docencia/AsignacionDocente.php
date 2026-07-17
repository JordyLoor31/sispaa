<?php

namespace App\Models\Docencia;

use Illuminate\Database\Eloquent\Model;

class AsignacionDocente extends Model
{
    protected $table = 'asignaciones_docente';

    protected $fillable = [
        'docente_id',
        'materia_id',
        'periodo_id',
        'tipo_rol',
        'grupo',
    ];

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }
}
