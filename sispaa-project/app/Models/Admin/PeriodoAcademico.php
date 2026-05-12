<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    protected $table = 'periodos_academicos';

    protected $fillable = [
        'carrera_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function asignacionesDocente()
    {
        return $this->hasMany(\App\Models\Docencia\AsignacionDocente::class);
    }

    public function silabos()
    {
        return $this->hasMany(\App\Models\Docencia\Silabo::class);
    }

    public function informesDocente()
    {
        return $this->hasMany(\App\Models\Docencia\InformeDocente::class);
    }

    public function matriculas()
    {
        return $this->hasMany(\App\Models\Estudiantes\Matricula::class);
    }

    public function faltas()
    {
        return $this->hasMany(\App\Models\Estudiantes\Falta::class);
    }

    public function investigaciones()
    {
        return $this->hasMany(\App\Models\Investigacion\Investigacion::class);
    }

    public function actividadesVinculacion()
    {
        return $this->hasMany(\App\Models\Vinculacion\ActividadVinculacion::class);
    }

    public function practicasLaboratorio()
    {
        return $this->hasMany(\App\Models\Laboratorio\PracticaLaboratorio::class);
    }
}
