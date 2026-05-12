<?php

namespace App\Models\Laboratorio;

use Illuminate\Database\Eloquent\Model;

class PracticaLaboratorio extends Model
{
    protected $table = 'practicas_laboratorio';

    protected $fillable = [
        'materia_id',
        'docente_id',
        'periodo_id',
        'ubicacion',
        'numero_practica',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function materia()
    {
        return $this->belongsTo(\App\Models\Docencia\Materia::class);
    }

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }
}
