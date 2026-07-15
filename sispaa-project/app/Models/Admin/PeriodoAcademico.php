<?php

namespace App\Models\Admin;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    use HasAuditFields;

    protected $table = 'periodos_academicos';

    /** Ciclo de vida de un período académico: se planifica, se activa y finalmente se finaliza. */
    public const ESTADO_PLANIFICADO = 'planificado';
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_FINALIZADO = 'finalizado';
    public const ESTADOS = [self::ESTADO_PLANIFICADO, self::ESTADO_ACTIVO, self::ESTADO_FINALIZADO];

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'tipo',
        'estado',
        'fecha_limite_silabo',
        'fecha_limite_informe',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_limite_silabo' => 'date',
        'fecha_limite_informe' => 'date',
    ];

    public function scopeActivo($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    /**
     * Un periodo académico es una entidad global compartida por todas las
     * carreras (ej. "2026-1"), no un registro por carrera. El alcance por
     * carrera se resuelve en cada tabla dependiente que ya tiene su propio
     * carrera_id (matriculas, actividades_vinculacion) o vía materia->carrera
     * (silabos, informes_docente, asignaciones_docente, practicas_laboratorio).
     */
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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
