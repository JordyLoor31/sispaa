<?php

namespace App\Models\Estudiantes;

use App\Models\Admin\Carrera;
use App\Models\Admin\Facultad;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Perfil académico/residencial del estudiante (1:1 con users). Paso 1 y 2
 * del wizard "Completar mi perfil" (Datos Académicos, Residencia).
 */
class PerfilEstudiante extends Model
{
    protected $table = 'perfiles_estudiantes';

    protected $fillable = [
        'user_id',
        'tipo_alumno',
        'nivel',
        'sede',
        'facultad_id',
        'carrera_id',
        'itinerario',
        'pensum',
        'anio_ingreso',
        'graduado_pregrado',
        'fecha_graduacion',
        'colegio',
        'anio_graduacion_colegio',
        'provincia_colegio',
        'universidad_procedencia',
        'provincia_universidad',
        'residente',
        'direccion',
        'provincia_residencia',
        'canton_residencia',
        'telefono_casa',
    ];

    protected $casts = [
        'graduado_pregrado' => 'boolean',
        'residente' => 'boolean',
        'fecha_graduacion' => 'date',
        'anio_ingreso' => 'integer',
        'anio_graduacion_colegio' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    /* ---------------------------------------------------------------
     | Scopes
     |----------------------------------------------------------------*/

    public function scopeGraduados($query)
    {
        return $query->where('graduado_pregrado', true);
    }

    public function scopeNoGraduados($query)
    {
        return $query->where('graduado_pregrado', false);
    }

    public function scopeResidentes($query)
    {
        return $query->where('residente', true);
    }

    public function scopePorCarrera($query, int $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    public function scopePorFacultad($query, int $facultadId)
    {
        return $query->where('facultad_id', $facultadId);
    }

    public function scopePorNivel($query, string $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopePorSede($query, string $sede)
    {
        return $query->where('sede', $sede);
    }
}
