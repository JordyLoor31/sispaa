<?php

namespace App\Models\Docencia;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materias';

    protected $fillable = [
        'carrera_id',
        'nombre',
        'codigo',
        'creditos',
        'nivel',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function carrera()
    {
        return $this->belongsTo(\App\Models\Admin\Carrera::class);
    }

    public function asignacionesDocente()
    {
        return $this->hasMany(AsignacionDocente::class);
    }

    public function silabos()
    {
        return $this->hasMany(Silabo::class);
    }

    public function informesDocente()
    {
        return $this->hasMany(InformeDocente::class);
    }

    public function faltas()
    {
        return $this->hasMany(\App\Models\Estudiantes\Falta::class);
    }

    public function practicasLaboratorio()
    {
        return $this->hasMany(\App\Models\Laboratorio\PracticaLaboratorio::class);
    }
}
